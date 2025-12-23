<?php
// Set CORS headers first
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            // Get all printed models from makerworld.com
            $query = "SELECT ol.id, ol.order_id, ol.link_url, ol.copies, ol.printed, ol.created_at,
                             o.user_id, o.user_name, o.status, o.created_at as order_created_at
                      FROM order_links ol
                      INNER JOIN orders o ON ol.order_id = o.id
                      WHERE ol.printed = 1 
                      AND ol.link_url LIKE '%makerworld.com%'
                      ORDER BY ol.created_at DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $links = $stmt->fetchAll();
            
            // Group by link_url to avoid duplicates and get unique models
            $uniqueModels = [];
            foreach ($links as $link) {
                $url = $link['link_url'];
                // Normalize URL (remove trailing slashes, fragments, etc.)
                $normalizedUrl = preg_replace('/[#?].*$/', '', rtrim($url, '/'));
                
                if (!isset($uniqueModels[$normalizedUrl])) {
                    $uniqueModels[$normalizedUrl] = [
                        'id' => $link['id'],
                        'url' => $normalizedUrl,
                        'copies' => intval($link['copies']),
                        'printed' => (bool)$link['printed'],
                        'created_at' => $link['created_at'],
                        'order_id' => $link['order_id'],
                        'user_id' => $link['user_id'],
                        'user_name' => $link['user_name'],
                        'order_status' => $link['status'],
                        'order_created_at' => $link['order_created_at']
                    ];
                } else {
                    // If same model printed multiple times, sum copies
                    $uniqueModels[$normalizedUrl]['copies'] += intval($link['copies']);
                }
            }
            
            sendJSON(array_values($uniqueModels));
            break;
            
        case 'POST':
            // Fetch model data from makerworld.com
            $data = getRequestBody();
            
            if (!isset($data['url']) || empty($data['url'])) {
                sendJSON(['error' => 'URL is required'], 400);
            }
            
            $url = $data['url'];
            
            // Validate it's a makerworld.com URL
            if (strpos($url, 'makerworld.com') === false) {
                sendJSON(['error' => 'URL must be from makerworld.com'], 400);
            }
            
            // Try regular HTML scraping first (faster and gets actual image URLs from HTML)
            $modelData = fetchMakerWorldData($url);
            
            // If no image found, try headless browser service (for JavaScript-rendered content)
            if ($modelData === null || empty($modelData['image'])) {
                $browserData = fetchMakerWorldWithBrowser($url);
                if ($browserData && !empty($browserData['image'])) {
                    // Merge browser data, prefer browser image if it's better
                    if (empty($modelData)) {
                        $modelData = $browserData;
                    } else {
                        $modelData['image'] = $browserData['image'];
                        if (!empty($browserData['screenshot'])) {
                            $modelData['screenshot'] = $browserData['screenshot'];
                        }
                    }
                }
            }
            
            if ($modelData === null || (empty($modelData['title']) && empty($modelData['image']) && empty($modelData['screenshot']))) {
                // Return a basic structure even if scraping failed
                $parsedUrl = parse_url($url);
                $pathParts = explode('/', trim($parsedUrl['path'] ?? '', '/'));
                $lastPart = end($pathParts);
                
                $modelData = [
                    'url' => $url,
                    'title' => $lastPart ? str_replace('-', ' ', $lastPart) : 'MakerWorld Model',
                    'description' => '',
                    'image' => '',
                    'author' => '',
                    'likes' => 0,
                    'downloads' => 0,
                    'views' => 0
                ];
            }
            
            sendJSON($modelData);
            break;
            
        default:
            sendJSON(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    sendJSON(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    sendJSON(['error' => 'Server error: ' . $e->getMessage()], 500);
}

/**
 * Fetch model data from makerworld.com
 * This function scrapes the HTML page to extract model information
 */
function fetchMakerWorldData($url) {
    try {
        // First, try to extract model ID from URL to use MakerWorld's pattern
        // MakerWorld URLs typically look like: https://makerworld.com/en/models/123456-model-name
        $modelId = extractMakerWorldModelId($url);
        
        // Try using a link preview service first (most reliable for images)
        $previewData = fetchLinkPreview($url);
        if ($previewData && !empty($previewData['image'])) {
            return array_merge([
                'url' => $url,
                'title' => $previewData['title'] ?? '',
                'description' => $previewData['description'] ?? '',
                'image' => $previewData['image'],
                'author' => '',
                'likes' => 0,
                'downloads' => 0,
                'views' => 0,
                'modelId' => $modelId
            ], $previewData);
        }
        
        // Fallback to direct HTML scraping
        // Initialize cURL with better headers to get Open Graph tags
        $ch = curl_init();
        
        // Set cURL options optimized for Open Graph extraction
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9',
                'Accept-Encoding: gzip, deflate, br',
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ],
            CURLOPT_ENCODING => '', // Automatically handle gzip/deflate
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($html === false || !empty($error) || $httpCode !== 200) {
            error_log("Failed to fetch makerworld.com: HTTP $httpCode, Error: $error");
            // Return basic data from URL if fetch fails
            return createBasicModelData($url, $modelId);
        }
        
        // Extract image from HTML first (most reliable for MakerWorld - gets actual swiper-lazy images)
        $htmlImageData = extractImageFromHTML($html, $url);
        
        // Extract Open Graph tags (for title, description, etc.)
        $modelData = extractOpenGraphData($html, $url);
        
        // Prefer HTML-extracted image over OG image (HTML has the actual model images)
        if (!empty($htmlImageData['image'])) {
            $modelData['image'] = $htmlImageData['image'];
            if (!empty($htmlImageData['imageUrls'])) {
                $modelData['imageUrls'] = $htmlImageData['imageUrls'];
            }
        }
        
        // If Open Graph data is good, use it
        if (!empty($modelData['title']) && !empty($modelData['image'])) {
            $modelData['modelId'] = $modelId;
            return $modelData;
        }
        
        // Try to extract JSON-LD structured data as fallback
        $jsonLdData = extractJsonLdData($html, $url);
        if (!empty($jsonLdData['title'])) {
            // Merge JSON-LD data with Open Graph data
            foreach ($jsonLdData as $key => $value) {
                if (!empty($value) && empty($modelData[$key])) {
                    $modelData[$key] = $value;
                }
            }
        }
        
        $modelData['modelId'] = $modelId;
        
        // If JSON-LD extraction failed, fall back to HTML parsing
        if (empty($modelData['title'])) {
            // Create DOMDocument
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            libxml_clear_errors();
            
            $xpath = new DOMXPath($dom);
            
            // Try to find JSON data in script tags
            $scripts = $xpath->query('//script[@type="application/json" or contains(@type, "json")]');
            if ($scripts && $scripts->length > 0) {
                foreach ($scripts as $script) {
                    $jsonText = trim($script->textContent);
                    if (!empty($jsonText)) {
                        $jsonData = json_decode($jsonText, true);
                        if ($jsonData) {
                            $modelData = extractFromJsonData($jsonData, $modelData);
                        }
                    }
                }
            }
            
            // Try to extract title (multiple possible selectors)
            $titleSelectors = [
                '//meta[@property="og:title"]/@content',
                '//meta[@name="twitter:title"]/@content',
                '//h1[contains(@class, "title")]',
                '//h1[1]',
                '//title'
            ];
            
            foreach ($titleSelectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes && $nodes->length > 0) {
                    $node = $nodes->item(0);
                    if ($node->nodeName === 'meta') {
                        $title = $node->getAttribute('content');
                    } else {
                        $title = trim($node->textContent);
                    }
                    if (!empty($title) && $title !== 'MakerWorld') {
                        $modelData['title'] = $title;
                        break;
                    }
                }
            }
            
            // Try to extract description
            $descSelectors = [
                '//meta[@property="og:description"]/@content',
                '//meta[@name="twitter:description"]/@content',
                '//meta[@name="description"]/@content',
                '//div[contains(@class, "description")]',
                '//p[contains(@class, "description")]'
            ];
            
            foreach ($descSelectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes && $nodes->length > 0) {
                    $node = $nodes->item(0);
                    if ($node->nodeName === 'meta') {
                        $modelData['description'] = $node->getAttribute('content');
                    } else {
                        $modelData['description'] = trim($node->textContent);
                    }
                    if (!empty($modelData['description'])) break;
                }
            }
        
            // Try to extract image (prioritize MakerWorld swiper-lazy images)
            $htmlImageData = extractImageFromHTML($html, $url);
            if (!empty($htmlImageData['image'])) {
                $modelData['image'] = $htmlImageData['image'];
                if (!empty($htmlImageData['imageUrls'])) {
                    $modelData['imageUrls'] = $htmlImageData['imageUrls'];
                }
            } else {
                // Fallback to other image selectors
                $imageSelectors = [
                    '//meta[@property="og:image"]/@content',
                    '//meta[@name="twitter:image"]/@content',
                    '//meta[@name="twitter:image:src"]/@content',
                    '//img[contains(@class, "model-image")]/@src',
                    '//img[contains(@class, "preview")]/@src',
                    '//img[contains(@class, "thumbnail")]/@src',
                    '//img[1]/@src'
                ];
                
                foreach ($imageSelectors as $selector) {
                    $nodes = $xpath->query($selector);
                    if ($nodes && $nodes->length > 0) {
                        $node = $nodes->item(0);
                        $imageUrl = $node->nodeName === 'meta' ? $node->getAttribute('content') : ($node->getAttribute('src') ?: $node->getAttribute('data-src') ?: $node->textContent);
                        if (!empty($imageUrl)) {
                            // Make absolute URL if relative
                            if (strpos($imageUrl, 'http') !== 0) {
                                $parsedUrl = parse_url($url);
                                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                                if (strpos($imageUrl, '/') === 0) {
                                    $imageUrl = $baseUrl . $imageUrl;
                                } else {
                                    $imageUrl = $baseUrl . '/' . $imageUrl;
                                }
                            }
                            $modelData['image'] = $imageUrl;
                            break;
                        }
                    }
                }
            }
        
            // Try to extract author
            $authorSelectors = [
                '//a[contains(@class, "author")]',
                '//span[contains(@class, "author")]',
                '//div[contains(@class, "author")]',
                '//meta[@property="article:author"]/@content'
            ];
            
            foreach ($authorSelectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes && $nodes->length > 0) {
                    $node = $nodes->item(0);
                    if ($node->nodeName === 'meta') {
                        $modelData['author'] = $node->getAttribute('content');
                    } else {
                        $modelData['author'] = trim($node->textContent);
                    }
                    if (!empty($modelData['author'])) break;
                }
            }
            
            // Try to extract stats (likes, downloads, views)
            // Look for numbers in the page that might be stats
            $allText = $dom->textContent;
            // Try to find patterns like "1.2k likes" or "500 downloads"
            if (preg_match('/(\d+(?:\.\d+)?[kKmM]?)\s*(?:likes?|👍)/i', $allText, $matches)) {
                $modelData['likes'] = parseNumber($matches[1]);
            }
            if (preg_match('/(\d+(?:\.\d+)?[kKmM]?)\s*(?:downloads?|⬇)/i', $allText, $matches)) {
                $modelData['downloads'] = parseNumber($matches[1]);
            }
            if (preg_match('/(\d+(?:\.\d+)?[kKmM]?)\s*(?:views?|👁)/i', $allText, $matches)) {
                $modelData['views'] = parseNumber($matches[1]);
            }
            
            // If title is still empty, use URL as fallback
            if (empty($modelData['title'])) {
                $parsedUrl = parse_url($url);
                $pathParts = explode('/', trim($parsedUrl['path'] ?? '', '/'));
                $lastPart = end($pathParts);
                if ($lastPart && $lastPart !== 'models' && $lastPart !== 'en') {
                    $modelData['title'] = str_replace('-', ' ', $lastPart);
                } else {
                    $modelData['title'] = 'MakerWorld Model';
                }
            }
        }
        
        return $modelData;
        
    } catch (Exception $e) {
        error_log("Error fetching makerworld.com data: " . $e->getMessage());
        $modelId = extractMakerWorldModelId($url);
        return createBasicModelData($url, $modelId);
    }
}

/**
 * Fetch MakerWorld data using headless browser service
 */
function fetchMakerWorldWithBrowser($url) {
    // Check if headless browser service is available
    $serviceUrl = getenv('MAKERWORLD_FETCHER_URL') ?: 'http://localhost:3002/fetch';
    
    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $serviceUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['url' => $url]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if ($data && !isset($data['error'])) {
                return $data;
            }
        }
        
        // Service not available or error - return null to fall back to regular scraping
        return null;
        
    } catch (Exception $e) {
        error_log("MakerWorld browser service error: " . $e->getMessage());
        return null;
    }
}

/**
 * Generate screenshot/preview using screenshot API service
 */
function generateScreenshotPreview($url) {
    // Option 1: Use ScreenshotAPI.net (requires API key, but has free tier)
    // $apiKey = getenv('SCREENSHOT_API_KEY');
    // if ($apiKey) {
    //     $screenshotUrl = "https://api.screenshotapi.net/?apiKey={$apiKey}&url=" . urlencode($url);
    //     return ['screenshotUrl' => $screenshotUrl];
    // }
    
    // Option 2: Use htmlcsstoimage.com (free tier available)
    // This requires API credentials
    
    // Option 3: Use a simple proxy approach - try to get the page and extract better image URLs
    // This is what we'll do - improve our existing scraping
    
    return null;
}

/**
 * Fetch link preview using free API services
 */
function fetchLinkPreview($url) {
    // Try multiple free link preview services
    $services = [
        // Service 1: LinkPreview.net (free tier available)
        function($url) {
            // Note: This requires an API key. You can get one at linkpreview.net
            // For now, we'll skip this and use other methods
            return null;
        },
        
        // Service 2: Use screenshot service for preview
        function($url) {
            // Use screenshot API to get page preview
            // Example: screenshotapi.net, but requires API key
            return null;
        },
        
        // Service 3: Try oEmbed endpoint (if MakerWorld supports it)
        function($url) {
            $oEmbedUrl = 'https://makerworld.com/oembed?url=' . urlencode($url) . '&format=json';
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $oEmbedUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                if ($data) {
                    return [
                        'title' => $data['title'] ?? '',
                        'description' => $data['description'] ?? '',
                        'image' => $data['thumbnail_url'] ?? $data['image'] ?? '',
                    ];
                }
            }
            return null;
        }
    ];
    
    // Try each service
    foreach ($services as $service) {
        $result = $service($url);
        if ($result && !empty($result['image'])) {
            return $result;
        }
    }
    
    return null;
}

/**
 * Extract image directly from HTML using regex and DOM parsing
 */
function extractImageFromHTML($html, $url) {
    $result = ['image' => '', 'imageUrls' => []];
    
    // Method 1: Use regex to find all makerworld.bblmw.com image URLs (fastest)
    // Pattern matches URLs like: https://makerworld.bblmw.com/makerworld/model/.../design/....jpg?x-oss-process=...
    $patterns = [
        // Full URL with query params
        '/https?:\/\/makerworld\.bblmw\.com\/[^"\'\s<>]+\.(jpg|jpeg|png|webp)(?:\?[^"\'\s<>]*)?/i',
        // Also try without makerworld subdomain
        '/https?:\/\/[^"\'\s<>]*bambulab\.com[^"\'\s<>]*\.(jpg|jpeg|png|webp)(?:\?[^"\'\s<>]*)?/i',
        // Generic pattern for any image URL in the HTML
        '/(https?:\/\/[^"\'\s<>]+\.(jpg|jpeg|png|webp|gif))(?:\?[^"\'\s<>]*)?/i'
    ];
    
    foreach ($patterns as $patternIndex => $pattern) {
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[0] as $imageUrl) {
                // Decode HTML entities
                $imageUrl = html_entity_decode($imageUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
                // Prefer MakerWorld CDN images
                if (strpos($imageUrl, 'makerworld.bblmw.com') !== false || strpos($imageUrl, 'bambulab.com') !== false) {
                    // Prefer images with /design/ path (actual model images)
                    if (strpos($imageUrl, '/design/') !== false) {
                        $result['image'] = $imageUrl;
                        error_log("Found MakerWorld design image (regex): " . $imageUrl);
                        if ($patternIndex === 0) break 2; // Break both loops if we found a design image with first pattern
                    } elseif (empty($result['image'])) {
                        $result['image'] = $imageUrl;
                    }
                    if (!in_array($imageUrl, $result['imageUrls'])) {
                        $result['imageUrls'][] = $imageUrl;
                    }
                } elseif ($patternIndex === 2 && empty($result['image'])) {
                    // For generic pattern, only use if we haven't found anything yet
                    if (!in_array($imageUrl, $result['imageUrls'])) {
                        $result['imageUrls'][] = $imageUrl;
                    }
                }
            }
            // If we found a MakerWorld image with first pattern, stop
            if (!empty($result['image']) && $patternIndex === 0) break;
        }
    }
    
    // Method 2: Try DOM parsing as fallback (for swiper-lazy images)
    if (empty($result['image'])) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Look for swiper-lazy images (MakerWorld uses these for gallery)
        $lazyImages = $xpath->query('//img[contains(@class, "swiper-lazy")]');
        if ($lazyImages && $lazyImages->length > 0) {
            foreach ($lazyImages as $img) {
                // Try data-src first (lazy loading), then src
                $imageUrl = $img->getAttribute('data-src') ?: $img->getAttribute('src');
                if (!empty($imageUrl)) {
                    // Make absolute if relative
                    if (strpos($imageUrl, 'http') !== 0) {
                        $parsedUrl = parse_url($url);
                        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                        if (strpos($imageUrl, '/') === 0) {
                            $imageUrl = $baseUrl . $imageUrl;
                        } else {
                            $imageUrl = $baseUrl . '/' . $imageUrl;
                        }
                    }
                    
                    // Prefer MakerWorld CDN images
                    if (strpos($imageUrl, 'makerworld.bblmw.com') !== false || strpos($imageUrl, 'bambulab.com') !== false) {
                        if (empty($result['image']) || strpos($imageUrl, '/design/') !== false) {
                            $result['image'] = $imageUrl;
                            error_log("Found MakerWorld image (DOM): " . $imageUrl);
                        }
                        if (!in_array($imageUrl, $result['imageUrls'])) {
                            $result['imageUrls'][] = $imageUrl;
                        }
                        if (!empty($result['image']) && strpos($imageUrl, '/design/') !== false) break;
                    }
                }
            }
        }
        
        // Also try regular img tags with makerworld URLs
        if (empty($result['image'])) {
            $allImages = $xpath->query('//img[@src or @data-src]');
            if ($allImages && $allImages->length > 0) {
                foreach ($allImages as $img) {
                    $imageUrl = $img->getAttribute('data-src') ?: $img->getAttribute('src');
                    if (!empty($imageUrl) && (strpos($imageUrl, 'makerworld.bblmw.com') !== false || strpos($imageUrl, 'bambulab.com') !== false)) {
                        if (empty($result['image']) || strpos($imageUrl, '/design/') !== false) {
                            $result['image'] = $imageUrl;
                            error_log("Found MakerWorld image (DOM fallback): " . $imageUrl);
                        }
                        if (!in_array($imageUrl, $result['imageUrls'])) {
                            $result['imageUrls'][] = $imageUrl;
                        }
                        if (!empty($result['image']) && strpos($imageUrl, '/design/') !== false) break;
                    }
                }
            }
        }
    }
    
    // If still no image found, log for debugging
    if (empty($result['image'])) {
        error_log("No MakerWorld image found in HTML for URL: " . $url);
        // Log a sample of the HTML to see what we're working with
        $htmlSample = substr($html, 0, 2000);
        error_log("HTML sample: " . $htmlSample);
    }
    
    return $result;
}

/**
 * Extract MakerWorld model ID from URL
 */
function extractMakerWorldModelId($url) {
    // MakerWorld URLs: https://makerworld.com/en/models/123456-model-name
    if (preg_match('/\/models\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Extract Open Graph meta tags (most reliable method)
 */
function extractOpenGraphData($html, $url) {
    $modelData = [
        'url' => $url,
        'title' => '',
        'description' => '',
        'image' => '',
        'author' => '',
        'likes' => 0,
        'downloads' => 0,
        'views' => 0,
        'tags' => []
    ];
    
    // Use regex to extract Open Graph tags (faster and more reliable than DOM parsing)
    // Open Graph tags are always in the <head> section, even with JavaScript
    
    // Extract og:title
    if (preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        $modelData['title'] = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    
    // Extract og:description
    if (preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        $modelData['description'] = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    
    // Extract og:image (most important for display)
    // Try multiple patterns to catch different HTML formats
    $imagePatterns = [
        '/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i',
        '/<meta\s+property=["\']og:image:secure_url["\']\s+content=["\']([^"\']+)["\']/i',
        '/<meta\s+name=["\']twitter:image["\']\s+content=["\']([^"\']+)["\']/i',
        '/<meta\s+name=["\']twitter:image:src["\']\s+content=["\']([^"\']+)["\']/i'
    ];
    
    foreach ($imagePatterns as $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            $imageUrl = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            // Keep query parameters for MakerWorld CDN URLs (x-oss-process)
            // Only remove query params for non-CDN images
            if (strpos($imageUrl, 'makerworld.bblmw.com') === false && strpos($imageUrl, 'bambulab.com') === false) {
                $imageUrl = preg_replace('/[?&#].*$/', '', $imageUrl);
            }
            
            // Make absolute URL if relative
            if (strpos($imageUrl, 'http') !== 0) {
                $parsedUrl = parse_url($url);
                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                if (strpos($imageUrl, '/') === 0) {
                    $imageUrl = $baseUrl . $imageUrl;
                } else {
                    $imageUrl = $baseUrl . '/' . $imageUrl;
                }
            }
            
            if (!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Prefer MakerWorld CDN URLs
                if (strpos($imageUrl, 'makerworld.bblmw.com') !== false || strpos($imageUrl, 'bambulab.com') !== false) {
                    $modelData['image'] = $imageUrl;
                    error_log("Found MakerWorld OG image: " . $imageUrl);
                    break;
                } elseif (empty($modelData['image'])) {
                    // Use as fallback
                    $modelData['image'] = $imageUrl;
                    error_log("Found OG image: " . $imageUrl);
                }
            }
        }
    }
    
    // If no OG image found, try to generate MakerWorld CDN URL from model ID
    if (empty($modelData['image'])) {
        $modelId = extractMakerWorldModelId($url);
        if ($modelId) {
            // Try MakerWorld CDN URLs
            $cdnUrls = [
                "https://makerworld-cdn.bambulab.com/model-images/{$modelId}/preview.jpg",
                "https://makerworld-cdn.bambulab.com/model-images/{$modelId}/cover.jpg",
                "https://makerworld-cdn.bambulab.com/model-images/{$modelId}/thumbnail.jpg"
            ];
            $modelData['image'] = $cdnUrls[0]; // Use first as primary
            $modelData['imageUrls'] = $cdnUrls; // Store all for fallback
            error_log("Using MakerWorld CDN image for model ID: {$modelId}");
        }
    }
    
    // Extract og:site_name or article:author for author info
    if (preg_match('/<meta\s+property=["\']article:author["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
        $modelData['author'] = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    
    return $modelData;
}

/**
 * Create basic model data from URL when scraping fails
 */
function createBasicModelData($url, $modelId = null) {
    $parsedUrl = parse_url($url);
    $pathParts = explode('/', trim($parsedUrl['path'] ?? '', '/'));
    
    // Extract model ID if not provided
    if (!$modelId) {
        $modelId = extractMakerWorldModelId($url);
    }
    
    // Try to extract a meaningful title from URL
    $title = 'MakerWorld Model';
    $description = '';
    
    // Find 'models' in path
    $modelsIndex = array_search('models', $pathParts);
    if ($modelsIndex !== false && isset($pathParts[$modelsIndex + 1])) {
        $modelSlug = $pathParts[$modelsIndex + 1];
        // Remove query parameters
        $modelSlug = preg_replace('/\?.*$/', '', $modelSlug);
        
        // Extract model name (after ID)
        if ($modelId && strpos($modelSlug, $modelId) === 0) {
            $modelName = substr($modelSlug, strlen($modelId));
            $modelName = ltrim($modelName, '-');
            if (!empty($modelName)) {
                $title = str_replace('-', ' ', $modelName);
                $title = ucwords($title);
            } else {
                $title = "Model #{$modelId}";
            }
        } else {
            $title = str_replace('-', ' ', $modelSlug);
            $title = ucwords($title);
        }
        
        $description = "3D model from MakerWorld" . ($modelId ? " (ID: {$modelId})" : "");
    }
    
    // Try to construct preview image URLs using MakerWorld's CDN pattern
    $image = '';
    $imageUrls = [];
    if ($modelId) {
        // MakerWorld uses CDN for images, try common patterns
        $imageUrls = [
            "https://makerworld-cdn.bambulab.com/model-images/{$modelId}/preview.jpg",
            "https://makerworld-cdn.bambulab.com/model-images/{$modelId}/cover.jpg",
            "https://makerworld-cdn.bambulab.com/model-images/{$modelId}/thumbnail.jpg"
        ];
        // Use first as primary image
        $image = $imageUrls[0];
    }
    
    return [
        'url' => $url,
        'title' => $title,
        'description' => $description,
        'image' => $image,
        'imageUrls' => $imageUrls,
        'author' => '',
        'likes' => 0,
        'downloads' => 0,
        'views' => 0,
        'tags' => [],
        'modelId' => $modelId,
        'extractedFromUrl' => true
    ];
}

/**
 * Extract JSON-LD structured data from HTML
 */
function extractJsonLdData($html, $url) {
    $modelData = [
        'url' => $url,
        'title' => '',
        'description' => '',
        'image' => '',
        'author' => '',
        'likes' => 0,
        'downloads' => 0,
        'views' => 0,
        'tags' => []
    ];
    
    // Look for JSON-LD script tags
    if (preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
        $jsonLd = json_decode(trim($matches[1]), true);
        if ($jsonLd) {
            // Handle array of JSON-LD objects
            if (isset($jsonLd['@type']) || isset($jsonLd[0])) {
                $data = isset($jsonLd[0]) ? $jsonLd[0] : $jsonLd;
                
                if (isset($data['name'])) {
                    $modelData['title'] = $data['name'];
                }
                if (isset($data['description'])) {
                    $modelData['description'] = $data['description'];
                }
                if (isset($data['image'])) {
                    $image = is_array($data['image']) ? ($data['image']['url'] ?? $data['image'][0] ?? '') : $data['image'];
                    $modelData['image'] = $image;
                }
                if (isset($data['author'])) {
                    $author = is_array($data['author']) ? ($data['author']['name'] ?? '') : $data['author'];
                    $modelData['author'] = $author;
                }
            }
        }
    }
    
    return $modelData;
}

/**
 * Extract data from JSON object (embedded in page)
 */
function extractFromJsonData($jsonData, $modelData) {
    // Recursively search for common keys
    $keys = ['title', 'name', 'description', 'image', 'thumbnail', 'author', 'creator', 'likes', 'downloads', 'views'];
    
    foreach ($keys as $key) {
        $value = findInArray($jsonData, $key);
        if ($value !== null) {
            switch ($key) {
                case 'title':
                case 'name':
                    if (empty($modelData['title'])) {
                        $modelData['title'] = is_string($value) ? $value : '';
                    }
                    break;
                case 'description':
                    if (empty($modelData['description'])) {
                        $modelData['description'] = is_string($value) ? $value : '';
                    }
                    break;
                case 'image':
                case 'thumbnail':
                    if (empty($modelData['image'])) {
                        $modelData['image'] = is_string($value) ? $value : '';
                    }
                    break;
                case 'author':
                case 'creator':
                    if (empty($modelData['author'])) {
                        $modelData['author'] = is_string($value) ? $value : (is_array($value) && isset($value['name']) ? $value['name'] : '');
                    }
                    break;
                case 'likes':
                    $modelData['likes'] = is_numeric($value) ? intval($value) : 0;
                    break;
                case 'downloads':
                    $modelData['downloads'] = is_numeric($value) ? intval($value) : 0;
                    break;
                case 'views':
                    $modelData['views'] = is_numeric($value) ? intval($value) : 0;
                    break;
            }
        }
    }
    
    return $modelData;
}

/**
 * Recursively find a key in array/object
 */
function findInArray($array, $key) {
    if (is_array($array)) {
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach ($array as $value) {
            if (is_array($value) || is_object($value)) {
                $result = findInArray((array)$value, $key);
                if ($result !== null) {
                    return $result;
                }
            }
        }
    }
    return null;
}

/**
 * Parse number string like "1.2k" or "500" to integer
 */
function parseNumber($str) {
    $str = trim(strtolower($str));
    $multiplier = 1;
    
    if (strpos($str, 'k') !== false) {
        $multiplier = 1000;
        $str = str_replace('k', '', $str);
    } elseif (strpos($str, 'm') !== false) {
        $multiplier = 1000000;
        $str = str_replace('m', '', $str);
    }
    
    $num = floatval($str);
    return intval($num * $multiplier);
}
