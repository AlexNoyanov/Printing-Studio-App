/**
 * MakerWorld Model Fetcher using Puppeteer
 * This service uses headless browser to fetch MakerWorld model data
 * 
 * Usage:
 *   POST http://localhost:3002/fetch
 *   Body: { "url": "https://makerworld.com/en/models/123456-model-name" }
 */

import express from 'express'
import cors from 'cors'

const app = express()
const PORT = process.env.PORT || 3002

app.use(cors())
app.use(express.json())

// Check if puppeteer is available, if not, provide fallback
let puppeteer = null
let browser = null

// Initialize Puppeteer
async function initBrowser() {
  try {
    puppeteer = await import('puppeteer')
    console.log('✅ Puppeteer loaded successfully')
    
    // Launch browser once (reusable)
    browser = await puppeteer.default.launch({
      headless: 'new',
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-accelerated-2d-canvas',
        '--disable-gpu'
      ]
    })
    console.log('✅ Browser launched successfully')
  } catch (error) {
    console.warn('⚠️  Puppeteer not available:', error.message)
    console.warn('   Install with: npm install puppeteer')
    console.warn('   Falling back to basic scraping')
  }
}

/**
 * Fetch MakerWorld model data using headless browser
 */
async function fetchMakerWorldWithBrowser(url) {
  if (!browser) {
    throw new Error('Browser not available')
  }

  const page = await browser.newPage()
  
  try {
    // Set viewport
    await page.setViewport({ width: 1280, height: 720 })
    
    // Set user agent
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
    
    // Navigate to page
    await page.goto(url, { 
      waitUntil: 'networkidle2',
      timeout: 15000 
    })
    
    // Wait for content to load
    await page.waitForTimeout(2000)
    
    // Extract data using JavaScript
    const modelData = await page.evaluate(() => {
      const data = {
        title: '',
        description: '',
        image: '',
        author: '',
        likes: 0,
        downloads: 0,
        views: 0
      }
      
      // Helper function to parse numbers with k/m suffix
      function parseNumber(str) {
        str = String(str).toLowerCase().trim()
        let multiplier = 1
        if (str.includes('k')) {
          multiplier = 1000
          str = str.replace('k', '')
        } else if (str.includes('m')) {
          multiplier = 1000000
          str = str.replace('m', '')
        }
        return Math.floor(parseFloat(str) * multiplier)
      }
      
      // Try to get title
      const titleEl = document.querySelector('h1') || 
                      document.querySelector('[data-testid="model-title"]') ||
                      document.querySelector('.model-title') ||
                      document.querySelector('meta[property="og:title"]')
      if (titleEl) {
        data.title = titleEl.content || titleEl.textContent || titleEl.innerText
      }
      
      // Try to get description
      const descEl = document.querySelector('meta[property="og:description"]') ||
                     document.querySelector('.model-description') ||
                     document.querySelector('[data-testid="model-description"]')
      if (descEl) {
        data.description = descEl.content || descEl.textContent || descEl.innerText
      }
      
      // Try to get image
      const imageEl = document.querySelector('meta[property="og:image"]') ||
                      document.querySelector('meta[name="twitter:image"]') ||
                      document.querySelector('.model-image img') ||
                      document.querySelector('[data-testid="model-image"] img')
      if (imageEl) {
        data.image = imageEl.content || imageEl.src
      }
      
      // Try to get author
      const authorEl = document.querySelector('[data-testid="author"]') ||
                       document.querySelector('.author-name') ||
                       document.querySelector('meta[property="article:author"]')
      if (authorEl) {
        data.author = authorEl.content || authorEl.textContent || authorEl.innerText
      }
      
      // Try to extract stats (these are usually in spans or divs)
      const statsText = document.body.innerText
      
      // Extract likes
      const likesMatch = statsText.match(/(\d+(?:\.\d+)?[kKmM]?)\s*(?:likes?|👍)/i)
      if (likesMatch) {
        data.likes = parseNumber(likesMatch[1])
      }
      
      // Extract downloads
      const downloadsMatch = statsText.match(/(\d+(?:\.\d+)?[kKmM]?)\s*(?:downloads?|⬇)/i)
      if (downloadsMatch) {
        data.downloads = parseNumber(downloadsMatch[1])
      }
      
      // Extract views
      const viewsMatch = statsText.match(/(\d+(?:\.\d+)?[kKmM]?)\s*(?:views?|👁)/i)
      if (viewsMatch) {
        data.views = parseNumber(viewsMatch[1])
      }
      
      return data
    })
    
    // Also get Open Graph data from meta tags (more reliable)
    const ogData = await page.evaluate(() => {
      const og = {}
      const ogTitle = document.querySelector('meta[property="og:title"]')
      const ogDesc = document.querySelector('meta[property="og:description"]')
      const ogImage = document.querySelector('meta[property="og:image"]')
      const ogAuthor = document.querySelector('meta[property="article:author"]')
      
      if (ogTitle) og.title = ogTitle.content
      if (ogDesc) og.description = ogDesc.content
      if (ogImage) og.image = ogImage.content
      if (ogAuthor) og.author = ogAuthor.content
      
      return og
    })
    
    // Merge OG data (prefer OG as it's more reliable)
    let result = {
      ...modelData,
      title: ogData.title || modelData.title,
      description: ogData.description || modelData.description,
      image: ogData.image || modelData.image,
      author: ogData.author || modelData.author
    }
    
    // Take a screenshot if image is not available from OG tags
    if (!result.image || result.image === '') {
      try {
        const screenshot = await page.screenshot({
          type: 'jpeg',
          quality: 80,
          clip: {
            x: 0,
            y: 0,
            width: 1280,
            height: 720
          }
        })
        // Convert screenshot to base64 data URL
        result.screenshot = `data:image/jpeg;base64,${screenshot.toString('base64')}`
      } catch (screenshotError) {
        console.warn('Failed to take screenshot:', screenshotError.message)
      }
    }
    
    return result
    
  } finally {
    await page.close()
  }
}

/**
 * Health check endpoint
 */
app.get('/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    puppeteer: !!puppeteer,
    browser: !!browser 
  })
})

/**
 * Fetch MakerWorld model data
 */
app.post('/fetch', async (req, res) => {
  try {
    const { url } = req.body
    
    if (!url) {
      return res.status(400).json({ error: 'URL is required' })
    }
    
    if (!url.includes('makerworld.com')) {
      return res.status(400).json({ error: 'URL must be from makerworld.com' })
    }
    
    if (!browser) {
      return res.status(503).json({ 
        error: 'Headless browser not available',
        message: 'Install Puppeteer: npm install puppeteer'
      })
    }
    
    const modelData = await fetchMakerWorldWithBrowser(url)
    
    res.json({
      ...modelData,
      url: url,
      fetchedAt: new Date().toISOString()
    })
    
  } catch (error) {
    console.error('Error fetching MakerWorld data:', error)
    res.status(500).json({ 
      error: 'Failed to fetch model data',
      message: error.message 
    })
  }
})

// Start server after browser initialization
initBrowser().then(() => {
  app.listen(PORT, () => {
    console.log(`🚀 MakerWorld Fetcher service running on port ${PORT}`)
    console.log(`   Health: http://localhost:${PORT}/health`)
    console.log(`   Fetch: POST http://localhost:${PORT}/fetch`)
  })
})

// Graceful shutdown
process.on('SIGTERM', async () => {
  console.log('Shutting down...')
  if (browser) {
    await browser.close()
  }
  process.exit(0)
})

