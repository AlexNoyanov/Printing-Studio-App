<template>
  <div class="container">
    <div class="page-header">
      <h1>Shop - Printed Models</h1>
      <p class="subtitle">Browse all previously printed models from MakerWorld</p>
    </div>
    
    <div v-if="isLoading" class="loading-state">
      <p>Loading models...</p>
    </div>
    
    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="loadModels" class="retry-btn">Retry</button>
    </div>
    
    <div v-else-if="models.length === 0" class="empty-state">
      <p>No printed models from MakerWorld found yet.</p>
    </div>
    
    <div v-else class="models-grid">
      <div
        v-for="model in models"
        :key="model.url"
        class="model-card"
      >
        <div v-if="model.loading" class="model-loading">
          <p>Loading model data...</p>
        </div>
        <div v-else class="model-content">
          <div class="model-image-container">
            <!-- Try to display image if available -->
            <a
              v-if="model.data && (model.data.image || (model.data.imageUrls && model.data.imageUrls.length > 0)) && !model.imageError"
              :href="model.url"
              target="_blank"
              rel="noopener noreferrer"
              class="model-image-link"
            >
              <img
                :src="getImageUrl(model)"
                :alt="model.data.title || 'MakerWorld Model'"
                class="model-image"
                @error="handleImageError($event, model)"
                @load="handleImageLoad($event, model)"
              />
              <div class="image-overlay">
                <span class="view-text">View on MakerWorld</span>
              </div>
            </a>
            <!-- MakerWorld preview card if no image -->
            <a
              v-else
              :href="model.url"
              target="_blank"
              rel="noopener noreferrer"
              class="makerworld-preview-card"
            >
              <div class="preview-header">
                <div class="preview-icon">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                  </svg>
                </div>
                <div class="preview-brand">MakerWorld</div>
              </div>
              <div class="preview-content">
                <h3 class="preview-title">{{ getModelTitle(model) }}</h3>
                <div v-if="model.data && model.data.description" class="preview-description">
                  {{ truncateText(model.data.description, 120) }}
                </div>
                <div v-if="model.data && model.data.modelId" class="preview-id">Model ID: {{ model.data.modelId }}</div>
              </div>
              <div class="preview-footer">
                <span class="preview-link-text">Open on MakerWorld</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                  <polyline points="15 3 21 3 21 9"></polyline>
                  <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
              </div>
            </a>
          </div>
          
          <div class="model-info">
            <h3 class="model-title">
              <a
                :href="model.url"
                target="_blank"
                rel="noopener noreferrer"
                class="model-link"
              >
                {{ getModelTitle(model) }}
              </a>
            </h3>
            
            <div v-if="model.data && model.data.description" class="model-description">
              <p>{{ truncateText(model.data.description, 150) }}</p>
            </div>
            
            <div class="model-meta">
              <div v-if="model.data && model.data.author" class="meta-item">
                <strong>Author:</strong> {{ model.data.author }}
              </div>
              <div class="meta-item">
                <strong>Printed by:</strong> {{ model.user_name }}
              </div>
              <div class="meta-item">
                <strong>Copies:</strong> {{ model.copies }}
              </div>
              <div v-if="model.data" class="model-stats">
                <span v-if="model.data.likes > 0" class="stat-item">
                  👍 {{ formatNumber(model.data.likes) }}
                </span>
                <span v-if="model.data.downloads > 0" class="stat-item">
                  ⬇ {{ formatNumber(model.data.downloads) }}
                </span>
                <span v-if="model.data.views > 0" class="stat-item">
                  👁 {{ formatNumber(model.data.views) }}
                </span>
              </div>
            </div>
            
            <div class="model-footer">
              <a
                :href="model.url"
                target="_blank"
                rel="noopener noreferrer"
                class="view-model-btn"
              >
                View on MakerWorld →
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { storage } from '../utils/storage'

const models = ref([])
const isLoading = ref(false)
const error = ref('')

const loadModels = async () => {
  isLoading.value = true
  error.value = ''
  
  try {
    const shopModels = await storage.getShopModels()
    models.value = shopModels.map(model => ({
      ...model,
      loading: true,
      error: false,
      data: null,
      imageError: false
    }))
    
    // Load data for each model
    for (const model of models.value) {
      await loadModelData(model)
    }
  } catch (e) {
    console.error('Error loading shop models:', e)
    error.value = 'Failed to load models. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const extractModelInfoFromUrl = (url) => {
  // Extract model information from MakerWorld URL
  // Example: https://makerworld.com/en/models/123456-model-name
  try {
    const urlObj = new URL(url)
    const pathParts = urlObj.pathname.split('/').filter(p => p)
    const modelsIndex = pathParts.indexOf('models')
    
    if (modelsIndex !== -1 && pathParts[modelsIndex + 1]) {
      const modelSlug = pathParts[modelsIndex + 1]
      // Remove query parameters and hash
      const cleanSlug = modelSlug.split('?')[0].split('#')[0]
      
      // Extract model ID (numbers at the start)
      const modelIdMatch = cleanSlug.match(/^(\d+)/)
      const modelId = modelIdMatch ? modelIdMatch[1] : null
      
      // Extract model name (after the ID, replace dashes with spaces)
      let modelName = cleanSlug
      if (modelId) {
        modelName = cleanSlug.replace(new RegExp(`^${modelId}-?`), '')
      }
      modelName = modelName.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()).trim()
      
      // Try multiple image URL patterns for MakerWorld CDN
      const imageUrls = []
      if (modelId) {
        imageUrls.push(
          `https://makerworld-cdn.bambulab.com/model-images/${modelId}/preview.jpg`,
          `https://makerworld-cdn.bambulab.com/model-images/${modelId}/cover.jpg`,
          `https://makerworld-cdn.bambulab.com/model-images/${modelId}/thumbnail.jpg`
        )
      }
      
      return {
        title: modelName || (modelId ? `Model #${modelId}` : 'MakerWorld Model'),
        description: `3D model from MakerWorld${modelId ? ` (ID: ${modelId})` : ''}`,
        image: imageUrls[0] || '', // Use first URL, fallback will try others
        imageUrls: imageUrls, // Store all URLs to try
        author: '',
        likes: 0,
        downloads: 0,
        views: 0,
        modelId: modelId,
        extractedFromUrl: true
      }
    }
  } catch (e) {
    console.error('Error extracting model info from URL:', e)
  }
  
  // Fallback: use URL as title
  try {
    const urlObj = new URL(url)
    const domain = urlObj.hostname.replace('www.', '')
    return {
      title: `Model from ${domain}`,
      description: '',
      image: '',
      imageUrls: [],
      author: '',
      likes: 0,
      downloads: 0,
      views: 0,
      extractedFromUrl: true
    }
  } catch (e) {
    return {
      title: 'MakerWorld Model',
      description: '',
      image: '',
      imageUrls: [],
      author: '',
      likes: 0,
      downloads: 0,
      views: 0,
      extractedFromUrl: true
    }
  }
}

const loadModelData = async (model) => {
  model.loading = true
  model.error = false
  model.imageError = false
  
  // Always extract basic info from URL first (so we have something to show)
  const urlBasedData = extractModelInfoFromUrl(model.url)
  model.data = urlBasedData
  
  // Fetch actual data from API (includes Open Graph image)
  try {
    const modelData = await storage.fetchMakerWorldData(model.url)
    console.log('Fetched model data for', model.url, ':', modelData) // Debug log
    
    if (modelData) {
      // Merge API data with URL-based data (URL data as fallback)
      // Combine imageUrls arrays from both sources (deduplicated)
      const allImageUrls = [
        modelData.image, // Primary image as string
        modelData.screenshot, // Screenshot if available
        ...(modelData.imageUrls || []), // Array of image URLs from API
        ...(urlBasedData.imageUrls || []) // URL-extracted image URLs (CDN)
      ].filter((url, index, arr) => {
        // Remove duplicates, empty values, and invalid URLs
        return url && typeof url === 'string' && url.trim() !== '' && arr.indexOf(url) === index
      })
      
      // Determine best image to use (prioritize API image, then screenshot, then CDN)
      const primaryImage = modelData.image || modelData.screenshot || urlBasedData.image || (allImageUrls.length > 0 ? allImageUrls[0] : '')
      
      model.data = {
        ...urlBasedData,
        ...modelData,
        // Prefer API data over URL-extracted data
        title: modelData.title || urlBasedData.title,
        description: modelData.description || urlBasedData.description,
        image: primaryImage,
        author: modelData.author || urlBasedData.author,
        imageUrls: allImageUrls.length > 0 ? allImageUrls : (urlBasedData.imageUrls || []),
        extractedFromUrl: false
      }
      
      // Reset image loading state
      model.currentImageIndex = 0
      
      console.log('Final model data for', model.url, ':', {
        title: model.data.title,
        image: model.data.image,
        imageUrls: model.data.imageUrls,
        imageUrlsCount: model.data.imageUrls.length
      })
    } else {
      // No API data, use URL-extracted data with CDN images
      const primaryImage = urlBasedData.image || (urlBasedData.imageUrls && urlBasedData.imageUrls.length > 0 ? urlBasedData.imageUrls[0] : '')
      model.data = {
        ...urlBasedData,
        image: primaryImage,
        imageUrls: urlBasedData.imageUrls || []
      }
      model.currentImageIndex = 0
      console.log('Using URL-extracted data for', model.url, ':', {
        title: model.data.title,
        image: model.data.image,
        imageUrls: model.data.imageUrls,
        modelId: model.data.modelId
      })
    }
    
    model.error = false
  } catch (e) {
    console.error('Error loading model data from API, using URL-extracted data:', e)
    // Use URL-extracted data instead of showing error
    const primaryImage = urlBasedData.image || (urlBasedData.imageUrls && urlBasedData.imageUrls.length > 0 ? urlBasedData.imageUrls[0] : '')
    model.data = {
      ...urlBasedData,
      image: primaryImage,
      imageUrls: urlBasedData.imageUrls || []
    }
    model.currentImageIndex = 0
    model.error = false // Don't show error, just use URL data
    console.log('Using fallback URL-extracted data for', model.url, ':', {
      title: model.data.title,
      image: model.data.image,
      imageUrls: model.data.imageUrls
    })
  } finally {
    model.loading = false
  }
}

const truncateText = (text, maxLength) => {
  if (!text || text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'k'
  }
  return num.toString()
}

const getModelTitle = (model) => {
  if (model.data && model.data.title) {
    return model.data.title
  }
  // Extract title from URL as fallback
  try {
    const urlObj = new URL(model.url)
    const pathParts = urlObj.pathname.split('/').filter(p => p)
    const modelsIndex = pathParts.indexOf('models')
    if (modelsIndex !== -1 && pathParts[modelsIndex + 1]) {
      const modelSlug = pathParts[modelsIndex + 1]
      const modelName = modelSlug.replace(/^\d+-?/, '').replace(/-/g, ' ')
      return modelName ? modelName.replace(/\b\w/g, l => l.toUpperCase()) : 'MakerWorld Model'
    }
  } catch (e) {
    // Ignore
  }
  return 'MakerWorld Model'
}

const getImageUrl = (model) => {
  if (!model.data) return ''
  
  // Use primary image if available
  if (model.data.image) return model.data.image
  
  // Otherwise use current image index or first imageUrl if available
  if (model.data.imageUrls && Array.isArray(model.data.imageUrls) && model.data.imageUrls.length > 0) {
    const index = model.currentImageIndex || 0
    return model.data.imageUrls[index] || model.data.imageUrls[0]
  }
  
  return ''
}

const handleImageError = (event, model) => {
  console.warn('Image failed to load:', getImageUrl(model))
  
  if (!model.data || !model.data.imageUrls || !Array.isArray(model.data.imageUrls) || model.data.imageUrls.length === 0) {
    model.imageError = true
    return
  }
  
  // Initialize currentImageIndex if not set
  if (model.currentImageIndex === undefined) {
    model.currentImageIndex = 0
  } else {
    model.currentImageIndex++
  }
  
  // Try next image URL if available
  if (model.currentImageIndex < model.data.imageUrls.length) {
    const nextUrl = model.data.imageUrls[model.currentImageIndex]
    console.log('Trying next image URL (' + (model.currentImageIndex + 1) + '/' + model.data.imageUrls.length + '):', nextUrl)
    
    // Update the image URL to trigger re-render
    model.data.image = nextUrl
    model.imageError = false
    
    // Force Vue to update by triggering reactivity
    return
  }
  
  // All image attempts failed
  console.warn('All image URLs failed for model:', model.url)
  model.imageError = true
}

const handleImageLoad = (event, model) => {
  model.imageError = false
  console.log('Image loaded successfully:', getImageUrl(model))
}

onMounted(() => {
  loadModels()
})
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  color: #87CEEB;
  font-size: 2.5rem;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.5), 0 0 20px rgba(135, 206, 235, 0.3), 2px 2px 4px rgba(0, 0, 0, 0.3);
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 50%, #4da6c2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #a0d4e8;
  font-size: 1.1rem;
  opacity: 0.8;
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 3rem;
  color: #a0d4e8;
}

.error-state {
  color: #ff6b6b;
}

.retry-btn {
  margin-top: 1rem;
  background: #87CEEB;
  color: #000;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.3s;
}

.retry-btn:hover {
  background: #6bb6d6;
}

.models-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.model-card {
  background: #2a2a2a;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  border: 1px solid #3a3a3a;
  transition: transform 0.3s, box-shadow 0.3s;
}

.model-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 50px rgba(135, 206, 235, 0.3);
}

.model-loading {
  padding: 3rem 2rem;
  text-align: center;
  color: #a0d4e8;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
}

.retry-btn-small {
  margin-top: 0.5rem;
  background: #87CEEB;
  color: #000;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 600;
  transition: background 0.3s;
}

.retry-btn-small:hover {
  background: #6bb6d6;
}

.model-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.model-image-container {
  width: 100%;
  height: 400px;
  background: #1a1a1a;
  overflow: hidden;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.model-image-link {
  width: 100%;
  height: 100%;
  display: block;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.model-image-link:hover .image-overlay {
  opacity: 1;
}

.model-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.3s;
}

.model-image-link:hover .model-image {
  transform: scale(1.05);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, transparent 100%);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 1.5rem;
  opacity: 0;
  transition: opacity 0.3s;
}

.view-text {
  color: #fff;
  font-weight: 600;
  font-size: 1.1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.makerworld-preview-card {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
  border: 2px solid rgba(135, 206, 235, 0.3);
  border-radius: 8px;
  padding: 2rem;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.makerworld-preview-card:hover {
  border-color: rgba(135, 206, 235, 0.6);
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(135, 206, 235, 0.2);
}

.preview-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(135, 206, 235, 0.2);
}

.preview-icon {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(135, 206, 235, 0.1);
  border-radius: 8px;
  color: #87CEEB;
}

.preview-brand {
  font-size: 1.5rem;
  font-weight: 700;
  color: #87CEEB;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.5);
}

.preview-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.preview-title {
  font-size: 1.3rem;
  font-weight: 600;
  color: #fff;
  margin: 0;
  line-height: 1.3;
}

.preview-description {
  color: #a0d4e8;
  font-size: 0.95rem;
  line-height: 1.5;
  opacity: 0.9;
}

.preview-id {
  color: #87CEEB;
  font-size: 0.85rem;
  opacity: 0.7;
  font-family: monospace;
}

.preview-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(135, 206, 235, 0.2);
  color: #87CEEB;
  font-weight: 600;
  font-size: 1.1rem;
  transition: all 0.3s;
}

.makerworld-preview-card:hover .preview-footer {
  color: #a0d4e8;
  transform: translateX(5px);
}


.model-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
  color: #87CEEB;
  font-size: 1.1rem;
  border: 2px dashed rgba(135, 206, 235, 0.3);
  position: relative;
  padding: 2rem;
}

.placeholder-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  width: 100%;
  gap: 1rem;
}

.placeholder-icon {
  font-size: 4rem;
  opacity: 0.8;
  line-height: 1;
  filter: drop-shadow(0 0 10px rgba(135, 206, 235, 0.3));
}

.placeholder-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.placeholder-text {
  font-weight: 700;
  font-size: 1.25rem;
  color: #87CEEB;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.5);
}

.placeholder-id {
  font-size: 0.9rem;
  opacity: 0.7;
  color: #a0d4e8;
}

.placeholder-title {
  font-size: 1rem;
  color: #b8dce8;
  font-weight: 500;
  margin-top: 0.5rem;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.placeholder-link {
  margin-top: 0.5rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, rgba(135, 206, 235, 0.2) 0%, rgba(135, 206, 235, 0.1) 100%);
  border: 2px solid rgba(135, 206, 235, 0.4);
  border-radius: 8px;
  color: #87CEEB;
  text-decoration: none;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(135, 206, 235, 0.2);
}

.placeholder-link:hover {
  background: linear-gradient(135deg, rgba(135, 206, 235, 0.3) 0%, rgba(135, 206, 235, 0.2) 100%);
  border-color: rgba(135, 206, 235, 0.6);
  color: #a0d4e8;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(135, 206, 235, 0.3);
}

.placeholder-link svg {
  width: 16px;
  height: 16px;
}

.model-info {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.model-title {
  margin: 0 0 1rem 0;
  font-size: 1.3rem;
}

.model-link {
  color: #87CEEB;
  text-decoration: none;
  transition: color 0.3s;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
}

.model-link:hover {
  color: #6bb6d6;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.6);
}

.model-description {
  margin-bottom: 1rem;
  color: #b8dce8;
  font-size: 0.95rem;
  line-height: 1.5;
  flex: 1;
}

.model-meta {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid #3a3a3a;
}

.meta-item {
  margin-bottom: 0.5rem;
  color: #a0d4e8;
  font-size: 0.9rem;
}

.meta-item strong {
  color: #87CEEB;
}

.model-stats {
  display: flex;
  gap: 1rem;
  margin-top: 0.75rem;
  flex-wrap: wrap;
}

.stat-item {
  color: #a0d4e8;
  font-size: 0.85rem;
}

.model-footer {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #3a3a3a;
}

.view-model-btn {
  display: inline-block;
  background: #87CEEB;
  color: #000;
  text-decoration: none;
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  font-weight: 600;
  transition: background 0.3s;
  text-align: center;
  width: 100%;
}

.view-model-btn:hover {
  background: #6bb6d6;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .models-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .model-image-container {
    height: 300px;
  }
}

@media (max-width: 480px) {
  .page-header h1 {
    font-size: 1.5rem;
  }

  .subtitle {
    font-size: 1rem;
  }
}
</style>
