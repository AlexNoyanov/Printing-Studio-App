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
                        'order_created_at' => $link['order_created_at'],
                        // Will be populated below
                        'colors' => []
                    ];
                } else {
                    // If same model printed multiple times, sum copies
                    $uniqueModels[$normalizedUrl]['copies'] += intval($link['copies']);
                }
            }
            
            // Attach plastics (colors) used for each order
            if (!empty($links)) {
                $orderIds = array_values(array_unique(array_column($links, 'order_id')));
                if (!empty($orderIds)) {
                    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
                    $colorStmt = $pdo->prepare("SELECT order_id, color_name FROM order_colors WHERE order_id IN ($placeholders)");
                    $colorStmt->execute($orderIds);
                    $colorRows = $colorStmt->fetchAll();
                    
                    $colorsMap = [];
                    foreach ($colorRows as $row) {
                        $oid = $row['order_id'];
                        if (!isset($colorsMap[$oid])) {
                            $colorsMap[$oid] = [];
                        }
                        if (!in_array($row['color_name'], $colorsMap[$oid], true)) {
                            $colorsMap[$oid][] = $row['color_name'];
                        }
                    }
                    
                    foreach ($uniqueModels as &$model) {
                        $oid = $model['order_id'];
                        $model['colors'] = $colorsMap[$oid] ?? [];
                    }
                    unset($model);
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
            
            // Check if cURL is available
            if (!function_exists('curl_init')) {
                sendJSON(['error' => 'cURL extension is not available on the server'], 500);
            }
            
            // Fetch model data from makerworld.com.
            // This function always returns a model data array with sensible fallbacks,
            // even if MakerWorld blocks automated requests (e.g. HTTP 403).
            $result = fetchMakerWorldData($url);
            
            if ($result === null) {
                sendJSON(['error' => 'Failed to fetch model data from makerworld.com'], 500);
            }
            
            sendJSON($result);
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
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return makeFallbackModelData($url, 'Invalid URL format');
        }
        
        // Initialize cURL
        $ch = curl_init();
        
        if ($ch === false) {
            return makeFallbackModelData($url, 'Failed to initialize cURL');
        }
        
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9',
            ],
            CURLOPT_ENCODING => '', // Enable automatic decompression
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        
        if ($html === false || !empty($error)) {
            error_log("cURL error fetching makerworld.com: $error (URL: $url)");
            $reason = $error ? "cURL error: $error" : 'Unknown cURL error';
            return makeFallbackModelData($url, $reason);
        }
        
        if ($httpCode !== 200) {
            error_log("HTTP error fetching makerworld.com: HTTP $httpCode (URL: $url)");
            return makeFallbackModelData($url, "HTTP error: $httpCode");
        }
        
        if (empty($html)) {
            error_log("Empty response from makerworld.com (URL: $url)");
            return makeFallbackModelData($url, 'Received empty response from server');
        }
        
        // Create DOMDocument
        if (!class_exists('DOMDocument')) {
            error_log("DOMDocument class not available");
            return makeFallbackModelData($url, 'DOMDocument extension is not available');
        }
        
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        
        // Try to load HTML with proper encoding handling
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $loaded = @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        
        if (!$loaded) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            error_log("Failed to parse HTML from makerworld.com: " . print_r($errors, true));
            // Continue anyway - sometimes HTML parsing has warnings but still works
        } else {
            libxml_clear_errors();
        }
        
        $xpath = new DOMXPath($dom);
        
        // Extract model data
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
        
        // Try to extract title (multiple possible selectors)
        $titleSelectors = [
            '//h1[contains(@class, "title")]',
            '//h1',
            '//meta[@property="og:title"]/@content',
            '//title'
        ];
        
        foreach ($titleSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                $node = $nodes->item(0);
                if ($node->nodeName === 'meta') {
                    $modelData['title'] = $node->getAttribute('content');
                } else {
                    $modelData['title'] = trim($node->textContent);
                }
                if (!empty($modelData['title'])) break;
            }
        }
        
        // Try to extract description
        $descSelectors = [
            '//meta[@property="og:description"]/@content',
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
        
        // Try to extract image
        $imageSelectors = [
            '//meta[@property="og:image"]/@content',
            '//img[contains(@class, "model-image")]/@src',
            '//img[contains(@class, "preview")]/@src',
            '//img[1]/@src'
        ];
        
        foreach ($imageSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                $node = $nodes->item(0);
                $imageUrl = $node->nodeName === 'meta' ? $node->getAttribute('content') : $node->textContent;
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
        // These are typically in spans or divs with specific classes
        $statsSelectors = [
            '//span[contains(@class, "like")]',
            '//span[contains(@class, "download")]',
            '//span[contains(@class, "view")]'
        ];
        
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
            $modelData['title'] = end($pathParts) ?: 'MakerWorld Model';
        }
        
        return $modelData;
        
    } catch (Exception $e) {
        error_log("Exception fetching makerworld.com data: " . $e->getMessage() . " (URL: $url)");
        return makeFallbackModelData($url, 'Exception: ' . $e->getMessage());
    } catch (Error $e) {
        error_log("Fatal error fetching makerworld.com data: " . $e->getMessage() . " (URL: $url)");
        return makeFallbackModelData($url, 'Fatal error: ' . $e->getMessage());
    }
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

/**
 * Build a minimal fallback model data structure when MakerWorld
 * blocks automated requests (e.g. HTTP 403) or scraping fails.
 */
function makeFallbackModelData($url, $reason = null) {
    $parsedUrl = parse_url($url);
    $path = trim($parsedUrl['path'] ?? '', '/');
    $pathParts = $path !== '' ? explode('/', $path) : [];
    $slug = !empty($pathParts) ? end($pathParts) : 'MakerWorld Model';
    
    $note = 'Preview limited. Open on MakerWorld for full details.';
    if ($reason) {
        $note = "Preview limited ({$reason}). Open on MakerWorld for full details.";
    }
    
    return [
        'url' => $url,
        'title' => $slug,
        'description' => '',
        'image' => '',
        'author' => '',
        'likes' => 0,
        'downloads' => 0,
        'views' => 0,
        'tags' => [],
        'partial' => true,
        'note' => $note
    ];
}
