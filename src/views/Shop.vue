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
        <div v-else-if="model.error" class="model-error">
          <p>{{ model.errorMessage || 'Failed to load model data' }}</p>
          <button @click="loadModelData(model)" class="retry-btn-small">Retry</button>
        </div>
        <div v-else class="model-content">
          <div class="model-image-container">
            <img
              v-if="model.data && model.data.image"
              :src="model.data.image"
              :alt="model.data.title || 'Model image'"
              class="model-image"
              @error="handleImageError($event)"
            />
            <div v-else class="model-image-placeholder">
              <span>No Image</span>
            </div>
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
            
            <div v-if="model.error && model.errorMessage" class="model-error-notice">
              <small>⚠ {{ model.errorMessage }}</small>
            </div>
            
            <div class="model-meta">
              <div v-if="model.data && model.data.author" class="meta-item">
                <strong>Author:</strong> {{ model.data.author }}
              </div>

              <div v-if="model.colors && model.colors.length" class="meta-item plastics-row">
                <strong>Plastics:</strong>
                <span
                  v-for="color in model.colors"
                  :key="color"
                  class="plastic-badge"
                >
                  {{ color }}
                </span>
              </div>

              <div v-if="model.data && (model.data.likes > 0 || model.data.downloads > 0 || model.data.views > 0)" class="model-stats">
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
              <button
                type="button"
                class="order-btn"
                @click="goToOrder(model)"
              >
                Order
              </button>
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
import { useRouter } from 'vue-router'
import { storage } from '../utils/storage'

const router = useRouter()

const models = ref([])
const isLoading = ref(false)
const error = ref('')

// Initialize model structure
const initializeModel = (model) => {
  return {
    ...model,
    loading: true,
    error: false,
    errorMessage: '',
    data: null
  }
}

const loadModels = async () => {
  isLoading.value = true
  error.value = ''
  
  try {
    const shopModels = await storage.getShopModels()
    models.value = shopModels.map(initializeModel)
    
    // Load data for each model (with delay to avoid overwhelming the server)
    for (let i = 0; i < models.value.length; i++) {
      await loadModelData(models.value[i])
      // Small delay between requests to avoid rate limiting
      if (i < models.value.length - 1) {
        await new Promise(resolve => setTimeout(resolve, 200))
      }
    }
  } catch (e) {
    console.error('Error loading shop models:', e)
    error.value = 'Failed to load models. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const goToOrder = (model) => {
  const colors = Array.isArray(model.colors) ? model.colors.join(',') : ''
  router.push({
    path: '/create-order',
    query: {
      modelUrl: model.url,
      colors
    }
  })
}

const loadModelData = async (model) => {
  model.loading = true
  model.error = false
  
  try {
    const modelData = await storage.fetchMakerWorldData(model.url)

    model.data = modelData || {
      title: 'MakerWorld Model',
      description: '',
      image: '',
      author: '',
      likes: 0,
      downloads: 0,
      views: 0
    }
    model.error = false
  } catch (e) {
    console.error('Error loading model data:', e)
    model.error = true
    model.errorMessage = e.message || 'Failed to load model data'
    
    // Provide fallback data with basic info from the URL
    const urlParts = model.url.split('/')
    const modelId = urlParts[urlParts.length - 1] || urlParts[urlParts.length - 2] || 'model'
    
    model.data = {
      title: `MakerWorld Model (${modelId})`,
      description: '',
      image: '',
      author: '',
      likes: 0,
      downloads: 0,
      views: 0,
      url: model.url
    }
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
  // Prefer scraped title when it exists and isn't the generic fallback
  const rawTitle =
    model.data && model.data.title && model.data.title !== 'MakerWorld Model'
      ? model.data.title
      : extractSlugFromUrl(model.url)

  return prettifySlugTitle(rawTitle)
}

const extractSlugFromUrl = (url) => {
  if (!url) return ''
  try {
    const u = new URL(url, 'https://makerworld.com')
    const path = u.pathname.replace(/^\/+|\/+$/g, '')
    const parts = path ? path.split('/') : []
    return parts[parts.length - 1] || parts[parts.length - 2] || ''
  } catch {
    const parts = url.split('/')
    return parts[parts.length - 1] || parts[parts.length - 2] || ''
  }
}

// Turn a MakerWorld slug like "80387-christmas-ball-set-3"
// into a nice title: "Christmas Ball Set 3"
const prettifySlugTitle = (slug) => {
  if (!slug) return 'MakerWorld Model'

  // Strip extension if present
  let s = slug.replace(/\.[^.]+$/, '')
  // Normalize separators
  s = s.replace(/[_-]+/g, ' ')

  let parts = s.split(/\s+/).filter(Boolean)

  // Drop leading pure-number tokens (often the model ID)
  while (parts.length && /^\d+$/.test(parts[0])) {
    parts.shift()
  }

  if (!parts.length) return 'MakerWorld Model'

  return parts
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
  const placeholder = event.target.nextElementSibling || event.target.parentElement.querySelector('.model-image-placeholder')
  if (placeholder) {
    placeholder.style.display = 'flex'
  }
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

.model-loading,
.model-error {
  padding: 3rem 2rem;
  text-align: center;
  color: #a0d4e8;
}

.model-error {
  color: #ff6b6b;
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
  height: 250px;
  background: #1a1a1a;
  overflow: hidden;
  position: relative;
}

.model-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.model-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #1a1a1a;
  color: #666;
  font-size: 1.1rem;
}

.model-info {
  padding: 1.5rem 1.5rem 1.25rem 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  background: radial-gradient(circle at top left, rgba(135, 206, 235, 0.08), transparent 55%),
              radial-gradient(circle at bottom right, rgba(76, 175, 80, 0.08), transparent 55%),
              linear-gradient(135deg, #202020 0%, #181818 60%, #151515 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.03);
  position: relative;
  overflow: hidden;
}

.model-title {
  margin: 0;
  font-size: 1.25rem;
  line-height: 1.2;
  letter-spacing: 0.02em;
  display: flex;
  align-items: center;
}

.model-link {
  color: #87CEEB;
  text-decoration: none;
  transition: color 0.3s;
  text-shadow: 0 0 5px rgba(135, 206, 235, 0.3);
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

.model-link:hover {
  color: #6bb6d6;
  text-shadow: 0 0 10px rgba(135, 206, 235, 0.6);
}

.model-description {
  margin-bottom: 0.5rem;
  color: #b8dce8;
  font-size: 0.95rem;
  line-height: 1.5;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.model-error-notice {
  margin-bottom: 1rem;
  padding: 0.5rem;
  background: rgba(255, 107, 107, 0.1);
  border-radius: 5px;
  border: 1px solid rgba(255, 107, 107, 0.3);
}

.model-error-notice small {
  color: #ff6b6b;
  font-size: 0.85rem;
}

.model-meta {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid #3a3a3a;
}

.plastics-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  align-items: center;
}

.plastic-badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  background: #1a1a1a;
  border: 1px solid #3a3a3a;
  font-size: 0.8rem;
  color: #a0d4e8;
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
  border-top: 1px solid #2f2f2f;
}

.model-footer {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.order-btn {
  flex: 1;
  background: linear-gradient(135deg, #4caf50, #66bb6a);
  color: #020b05;
  border: none;
  text-decoration: none;
  padding: 0.75rem 1.5rem;
  border-radius: 5px;
  font-weight: 600;
  transition: background 0.3s;
  text-align: center;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(76, 175, 80, 0.35);
  letter-spacing: 0.03em;
  text-transform: uppercase;
  font-size: 0.85rem;
}

.order-btn:hover {
  background: linear-gradient(135deg, #43a047, #5aa85d);
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.45);
}

.view-model-btn {
  flex: 1;
  display: inline-block;
  background: transparent;
  color: #87CEEB;
  text-decoration: none;
  padding: 0.55rem 0.9rem;
  border-radius: 5px;
  font-weight: 600;
  transition: background 0.3s;
  text-align: center;
  border: 1px solid rgba(135, 206, 235, 0.45);
  letter-spacing: 0.03em;
  text-transform: uppercase;
  font-size: 0.75rem;
  white-space: nowrap;
}

.view-model-btn:hover {
  background: radial-gradient(circle at top left, rgba(135, 206, 235, 0.16), transparent 55%);
  border-color: rgba(135, 206, 235, 0.9);
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
    height: 200px;
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
