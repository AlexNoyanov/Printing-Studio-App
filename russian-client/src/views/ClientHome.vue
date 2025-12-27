<template>
  <div class="client-home">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title">Студия 3D-печати</h1>
        <p class="hero-subtitle">Превращаем ваши идеи в реальность</p>
        <div class="hero-actions">
          <router-link to="/create-order" class="cta-button primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 5v14M5 12h14"></path>
            </svg>
            Создать заказ
          </router-link>
          <router-link to="/orders" class="cta-button secondary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            Мои заказы
          </router-link>
        </div>
      </div>
      <div class="hero-visual">
        <div class="floating-shapes">
          <div class="shape shape-1"></div>
          <div class="shape shape-2"></div>
          <div class="shape shape-3"></div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
      <div class="container">
        <h2 class="section-title">О нашей студии</h2>
        <div class="about-grid">
          <div class="about-card">
            <div class="card-icon">🎯</div>
            <h3>Профессионализм</h3>
            <p>Опытная команда с многолетним опытом в 3D-печати. Мы используем современное оборудование и проверенные материалы для достижения лучших результатов.</p>
          </div>
          <div class="about-card">
            <div class="card-icon">⚡</div>
            <h3>Скорость</h3>
            <p>Быстрое выполнение заказов без потери качества. Мы понимаем, что время важно, и работаем максимально эффективно.</p>
          </div>
          <div class="about-card">
            <div class="card-icon">🎨</div>
            <h3>Разнообразие</h3>
            <p>Широкий выбор цветов и материалов. От стандартных PLA до экзотических материалов - у нас найдется то, что вам нужно.</p>
          </div>
          <div class="about-card">
            <div class="card-icon">💎</div>
            <h3>Качество</h3>
            <p>Контроль качества на каждом этапе. Мы гарантируем, что ваша модель будет напечатана с высочайшей точностью и качеством.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Process Section -->
    <section class="process-section">
      <div class="container">
        <h2 class="section-title">Как это работает</h2>
        <div class="process-steps">
          <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">
              <h3>Загрузите модель</h3>
              <p>Прикрепите ссылку на вашу 3D-модель (STL, OBJ или другие форматы)</p>
            </div>
          </div>
          <div class="step-arrow">→</div>
          <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">
              <h3>Выберите параметры</h3>
              <p>Укажите желаемый цвет, количество копий и дополнительные требования</p>
            </div>
          </div>
          <div class="step-arrow">→</div>
          <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">
              <h3>Отслеживайте заказ</h3>
              <p>Следите за статусом вашего заказа в реальном времени</p>
            </div>
          </div>
          <div class="step-arrow">→</div>
          <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">
              <h3>Получите результат</h3>
              <p>Ваша модель готова! Заберите или получите доставку</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
      <div class="container">
        <h2 class="section-title">Наши возможности</h2>
        <div class="features-grid">
          <div class="feature-card">
            <svg class="feature-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
              <path d="M2 17l10 5 10-5"></path>
              <path d="M2 12l10 5 10-5"></path>
            </svg>
            <h3>Множество материалов</h3>
            <p>PLA, PETG, ABS, TPU и другие материалы на выбор</p>
          </div>
          <div class="feature-card">
            <svg class="feature-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M12 6v6l4 2"></path>
            </svg>
            <h3>Быстрые сроки</h3>
            <p>Стандартные заказы выполняются в течение 2-5 дней</p>
          </div>
          <div class="feature-card">
            <svg class="feature-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <h3>Гарантия качества</h3>
            <p>Проверка каждой модели перед отправкой</p>
          </div>
          <div class="feature-card">
            <svg class="feature-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <h3>Отслеживание</h3>
            <p>Полный контроль над статусом вашего заказа</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>Готовы начать?</h2>
          <p>Создайте свой первый заказ прямо сейчас</p>
          <router-link to="/create-order" class="cta-button large">
            Создать заказ
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  // Smooth scroll animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in')
      }
    })
  }, observerOptions)

  document.querySelectorAll('.about-card, .step, .feature-card').forEach(el => {
    observer.observe(el)
  })
})
</script>

<style scoped>
.client-home {
  min-height: 100vh;
  background: linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
  color: #fff;
}

/* Hero Section */
.hero-section {
  position: relative;
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  overflow: hidden;
}

.hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  max-width: 800px;
}

.hero-title {
  font-size: 4rem;
  font-weight: 800;
  margin: 0 0 1rem;
  background: linear-gradient(135deg, #87CEEB 0%, #a0d4e8 50%, #6bb6d6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 0 40px rgba(135, 206, 235, 0.3);
  letter-spacing: -2px;
}

.hero-subtitle {
  font-size: 1.5rem;
  color: #a0d4e8;
  margin: 0 0 2.5rem;
  opacity: 0.9;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.cta-button {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2rem;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.cta-button.primary {
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 100%);
  color: #000;
  box-shadow: 0 4px 20px rgba(135, 206, 235, 0.3);
}

.cta-button.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 30px rgba(135, 206, 235, 0.4);
}

.cta-button.secondary {
  background: rgba(135, 206, 235, 0.1);
  color: #87CEEB;
  border: 2px solid rgba(135, 206, 235, 0.3);
}

.cta-button.secondary:hover {
  background: rgba(135, 206, 235, 0.2);
  border-color: rgba(135, 206, 235, 0.5);
  transform: translateY(-2px);
}

.cta-button.large {
  padding: 1.25rem 2.5rem;
  font-size: 1.2rem;
}

.hero-visual {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
}

.floating-shapes {
  position: relative;
  width: 100%;
  height: 100%;
}

.shape {
  position: absolute;
  border-radius: 50%;
  filter: blur(60px);
  opacity: 0.2;
  animation: float 15s infinite ease-in-out;
}

.shape-1 {
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, #87CEEB 0%, transparent 70%);
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 250px;
  height: 250px;
  background: radial-gradient(circle, #6bb6d6 0%, transparent 70%);
  bottom: 20%;
  right: 15%;
  animation-delay: 5s;
}

.shape-3 {
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, #4da6c2 0%, transparent 70%);
  top: 50%;
  right: 30%;
  animation-delay: 10s;
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -30px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Section Styles */
section {
  padding: 5rem 0;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  text-align: center;
  margin: 0 0 3rem;
  background: linear-gradient(135deg, #87CEEB 0%, #a0d4e8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* About Section */
.about-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.about-card {
  background: rgba(26, 26, 42, 0.6);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid rgba(135, 206, 235, 0.2);
  transition: all 0.3s ease;
  opacity: 0;
  transform: translateY(30px);
}

.about-card.animate-in {
  opacity: 1;
  transform: translateY(0);
  transition: all 0.6s ease;
}

.about-card:hover {
  transform: translateY(-5px);
  border-color: rgba(135, 206, 235, 0.4);
  box-shadow: 0 10px 40px rgba(135, 206, 235, 0.2);
}

.card-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  filter: drop-shadow(0 0 10px rgba(135, 206, 235, 0.3));
}

.about-card h3 {
  font-size: 1.5rem;
  margin: 0 0 1rem;
  color: #87CEEB;
}

.about-card p {
  color: #a0d4e8;
  line-height: 1.6;
  margin: 0;
}

/* Process Section */
.process-section {
  background: rgba(10, 10, 10, 0.5);
}

.process-steps {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  gap: 2rem;
}

.step {
  flex: 1;
  min-width: 200px;
  max-width: 250px;
  text-align: center;
  opacity: 0;
  transform: translateY(30px);
}

.step.animate-in {
  opacity: 1;
  transform: translateY(0);
  transition: all 0.6s ease;
}

.step-number {
  width: 60px;
  height: 60px;
  margin: 0 auto 1rem;
  background: linear-gradient(135deg, #87CEEB 0%, #6bb6d6 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: #000;
  box-shadow: 0 4px 20px rgba(135, 206, 235, 0.3);
}

.step-content h3 {
  font-size: 1.25rem;
  margin: 0 0 0.5rem;
  color: #87CEEB;
}

.step-content p {
  color: #a0d4e8;
  line-height: 1.6;
  margin: 0;
}

.step-arrow {
  font-size: 2rem;
  color: #87CEEB;
  opacity: 0.5;
}

/* Features Section */
.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: rgba(26, 26, 42, 0.6);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 2.5rem;
  text-align: center;
  border: 1px solid rgba(135, 206, 235, 0.2);
  transition: all 0.3s ease;
  opacity: 0;
  transform: translateY(30px);
}

.feature-card.animate-in {
  opacity: 1;
  transform: translateY(0);
  transition: all 0.6s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
  border-color: rgba(135, 206, 235, 0.4);
  box-shadow: 0 10px 40px rgba(135, 206, 235, 0.2);
}

.feature-svg {
  color: #87CEEB;
  margin-bottom: 1.5rem;
  filter: drop-shadow(0 0 10px rgba(135, 206, 235, 0.3));
}

.feature-card h3 {
  font-size: 1.5rem;
  margin: 0 0 1rem;
  color: #87CEEB;
}

.feature-card p {
  color: #a0d4e8;
  line-height: 1.6;
  margin: 0;
}

/* CTA Section */
.cta-section {
  background: linear-gradient(135deg, rgba(135, 206, 235, 0.1) 0%, rgba(107, 182, 214, 0.05) 100%);
  border-top: 1px solid rgba(135, 206, 235, 0.2);
}

.cta-content {
  text-align: center;
}

.cta-content h2 {
  font-size: 2.5rem;
  margin: 0 0 1rem;
  color: #87CEEB;
}

.cta-content p {
  font-size: 1.2rem;
  color: #a0d4e8;
  margin: 0 0 2rem;
}

/* Responsive */
@media (max-width: 968px) {
  .hero-title {
    font-size: 3rem;
  }

  .process-steps {
    flex-direction: column;
  }

  .step-arrow {
    transform: rotate(90deg);
  }
}

@media (max-width: 640px) {
  .hero-title {
    font-size: 2.5rem;
  }

  .hero-subtitle {
    font-size: 1.2rem;
  }

  .section-title {
    font-size: 2rem;
  }

  .about-grid,
  .features-grid {
    grid-template-columns: 1fr;
  }
}
</style>

