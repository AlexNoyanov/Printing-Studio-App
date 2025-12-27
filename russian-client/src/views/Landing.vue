<template>
  <div class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title">Студия 3D-печати</h1>
        <p class="hero-subtitle">Превращаем ваши идеи в реальность</p>
        <div class="hero-actions">
          <router-link to="/register" class="cta-button primary">
            Начать работу
          </router-link>
          <router-link to="/login" class="cta-button secondary">
            Войти
          </router-link>
        </div>
      </div>
    </section>

    <!-- About Studio Section -->
    <section class="about-section">
      <div class="container">
        <h2 class="section-title">О нашей студии</h2>
        <div class="about-content">
          <p class="about-text">
            Мы — профессиональная студия 3D-печати, специализирующаяся на создании качественных изделий 
            из различных материалов. Наша команда имеет многолетний опыт работы с современным оборудованием 
            и использует только проверенные материалы для достижения лучших результатов.
          </p>
          <div class="features-grid">
            <div class="feature-card">
              <div class="feature-icon">🎯</div>
              <h3>Профессионализм</h3>
              <p>Опытная команда с многолетним опытом в 3D-печати</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">⚡</div>
              <h3>Скорость</h3>
              <p>Быстрое выполнение заказов без потери качества</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">🎨</div>
              <h3>Разнообразие</h3>
              <p>Широкий выбор материалов и цветов</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">💎</div>
              <h3>Качество</h3>
              <p>Высокое качество печати и точность деталей</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section">
      <div class="container">
        <h2 class="section-title">Как это работает</h2>
        <div class="steps-grid">
          <div class="step-card">
            <div class="step-number">1</div>
            <h3>Загрузите модель</h3>
            <p>Предоставьте ссылки на ваши 3D-модели в формате STL, OBJ или других поддерживаемых форматах</p>
          </div>
          <div class="step-card">
            <div class="step-number">2</div>
            <h3>Выберите материалы</h3>
            <p>Выберите из нашего широкого ассортимента филаментов и цветов</p>
          </div>
          <div class="step-card">
            <div class="step-number">3</div>
            <h3>Подтвердите заказ</h3>
            <p>Мы проверим ваш заказ и свяжемся с вами для подтверждения</p>
          </div>
          <div class="step-card">
            <div class="step-number">4</div>
            <h3>Получите результат</h3>
            <p>Заберите готовое изделие или получите его с доставкой</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Delivery Section -->
    <section class="delivery-section">
      <div class="container">
        <h2 class="section-title">Доставка и самовывоз</h2>
        <div class="delivery-grid">
          <div class="delivery-card">
            <div class="delivery-icon">🚗</div>
            <h3>Самовывоз</h3>
            <div class="delivery-info">
              <p><strong>Адрес:</strong></p>
              <p>г. Москва, ул. Электрозаводская</p>
              <p class="delivery-time">⏰ <strong>Время работы:</strong> Пн-Пт: 10:00 - 20:00, Сб-Вс: 11:00 - 18:00</p>
              <p class="delivery-note">Вы можете забрать готовый заказ в удобное для вас время. Мы свяжемся с вами, когда заказ будет готов.</p>
            </div>
          </div>
          <div class="delivery-card">
            <div class="delivery-icon">📦</div>
            <h3>Доставка</h3>
            <div class="delivery-info">
              <p><strong>Доставка по Москве:</strong></p>
              <p>• В пределах МКАД: 500₽</p>
              <p>• За МКАД: от 800₽ (зависит от расстояния)</p>
              <p><strong>Доставка по России:</strong></p>
              <p>• СДЭК, Почта России, Boxberry</p>
              <p>• Стоимость рассчитывается индивидуально</p>
              <p class="delivery-time">⏱️ <strong>Срок доставки:</strong> 1-3 рабочих дня (Москва), 3-7 дней (Россия)</p>
            </div>
          </div>
        </div>
        <div class="contact-info">
          <h3>Контакты</h3>
          <p>📧 Email: info@print-studio.ru</p>
          <p>📱 Телефон: +7 (999) 123-45-67</p>
          <p>💬 Telegram: @print_studio</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <h2>Готовы начать?</h2>
        <p>Создайте свой первый заказ прямо сейчас</p>
        <router-link to="/register" class="cta-button primary large">
          Создать заказ
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
// Landing page component - no logic needed
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Hero Section */
.hero-section {
  padding: 120px 20px 80px;
  text-align: center;
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hero-content {
  max-width: 800px;
  margin: 0 auto;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  text-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
}

.hero-subtitle {
  font-size: 1.5rem;
  margin-bottom: 2.5rem;
  opacity: 0.95;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.cta-button {
  padding: 14px 32px;
  border-radius: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 2px solid transparent;
}

.cta-button.primary {
  background: white;
  color: #667eea;
}

.cta-button.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.cta-button.secondary {
  background: transparent;
  color: white;
  border-color: white;
}

.cta-button.secondary:hover {
  background: white;
  color: #667eea;
}

.cta-button.large {
  padding: 18px 40px;
  font-size: 1.1rem;
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Section Styles */
section {
  padding: 80px 20px;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  text-align: center;
  margin-bottom: 3rem;
  color: #1a1a1a;
}

/* About Section */
.about-section {
  background: white;
}

.about-text {
  font-size: 1.2rem;
  line-height: 1.8;
  text-align: center;
  max-width: 800px;
  margin: 0 auto 3rem;
  color: #555;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-top: 3rem;
}

.feature-card {
  text-align: center;
  padding: 2rem;
  border-radius: 16px;
  background: #f8f9fa;
  transition: transform 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #1a1a1a;
}

.feature-card p {
  color: #666;
  line-height: 1.6;
}

/* How It Works Section */
.how-it-works-section {
  background: #f8f9fa;
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.step-card {
  text-align: center;
  padding: 2.5rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease;
}

.step-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.step-number {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-size: 1.8rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.step-card h3 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #1a1a1a;
}

.step-card p {
  color: #666;
  line-height: 1.6;
}

/* Delivery Section */
.delivery-section {
  background: white;
}

.delivery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2.5rem;
  margin-bottom: 3rem;
}

.delivery-card {
  padding: 2.5rem;
  background: #f8f9fa;
  border-radius: 16px;
  border: 2px solid #e9ecef;
}

.delivery-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.delivery-card h3 {
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  color: #1a1a1a;
}

.delivery-info p {
  margin-bottom: 0.8rem;
  line-height: 1.6;
  color: #555;
}

.delivery-time {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
  font-weight: 500;
}

.delivery-note {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
  font-style: italic;
  color: #666;
}

.contact-info {
  text-align: center;
  padding: 2.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  color: white;
  margin-top: 2rem;
}

.contact-info h3 {
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}

.contact-info p {
  font-size: 1.1rem;
  margin-bottom: 0.8rem;
}

/* CTA Section */
.cta-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  text-align: center;
  color: white;
  padding: 100px 20px;
}

.cta-section h2 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.cta-section p {
  font-size: 1.3rem;
  margin-bottom: 2.5rem;
  opacity: 0.95;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1.2rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .delivery-grid {
    grid-template-columns: 1fr;
  }
  
  .features-grid,
  .steps-grid {
    grid-template-columns: 1fr;
  }
}
</style>

