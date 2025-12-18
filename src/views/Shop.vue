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
          <p>Failed to load model data</p>
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
                {{ model.data && model.data.title ? model.data.title : 'MakerWorld Model' }}
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
      data: null
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

const loadModelData = async (model) => {
  model.loading = true
  model.error = false
  
  try {
    const modelData = await storage.fetchMakerWorldData(model.url)
    model.data = modelData
    model.error = false
  } catch (e) {
    console.error('Error loading model data:', e)
    model.error = true
    model.data = {
      title: 'MakerWorld Model',
      description: '',
      image: '',
      author: '',
      likes: 0,
      downloads: 0,
      views: 0
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
