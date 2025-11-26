<template>
  <section class="shop-section">
    <div class="shop-container">
      <h1 class="shop-title">Shop Fresh Products</h1>
      <p class="shop-intro">Buy direct from farmers — fresh, organic, and fairly priced.</p>

      <div class="shop-grid">
        <div class="product-card">
          <h3>🥦 Vegetables</h3>
          <p>Pesticide-free greens and root crops.</p>
        </div>
        <div class="product-card">
          <h3>🍎 Fruits</h3>
          <p>Seasonal fruits from trusted farms.</p>
        </div>
        <div class="product-card">
          <h3>🧀 Dairy</h3>
          <p>Fresh milk, yogurt, and cheese.</p>
        </div>
        <div class="product-card">
          <h3>🌿 Fertilizers</h3>
          <p>Organic and natural soil boosters.</p>
        </div>
      </div>

      
    </div>
  </section>
</template>

<script setup>
// No special script needed for static content
</script>

<style scoped>
.shop-section {
  background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
  padding: 3rem 1.5rem;
  color: #1f2937; /* gray-800 */
  font-family: 'Poppins', sans-serif;
}

.shop-container {
  max-width: 96rem; /* 6xl ~ 960px */
  margin: 0 auto;
}

.shop-title {
  font-size: 2rem;
  font-weight: 700;
  color: #166534; /* green-800 */
  margin-bottom: 1.5rem;
  text-align: center;
}

.shop-intro {
  text-align: center;
  margin-bottom: 2rem;
  font-size: 1rem;
  color: #374151; /* gray-700 */
}

/* Grid for products */
.shop-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(1, 1fr);
}

@media (min-width: 640px) {
  /* sm */
  .shop-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 768px) {
  /* md */
  .shop-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.product-card {
  background-color: white;
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(22, 101, 52, 0.1);
  text-align: center;
  transition: box-shadow 0.3s ease;
  cursor: default;
}

.product-card:hover {
  box-shadow: 0 8px 20px rgba(22, 101, 52, 0.15);
}

.product-card h3 {
  font-weight: 600;
  color: #16a34a; /* green-600 */
  margin-bottom: 0.5rem;
  font-size: 1.125rem;
}

.product-card p {
  font-size: 0.9rem;
  color: #4b5563; /* gray-600 */
  margin: 0;
}

/* Login/Register links below grid */
.auth-links {
  text-align: center;
  margin-top: 2.5rem;
  font-size: 1rem;
  color: #166534; /* green-700 */
  font-weight: 600;
}

.auth-links a {
  color: #166534;
  text-decoration: underline;
  cursor: pointer;
  transition: color 0.3s ease;
}

.auth-links a:hover {
  color: #15803d; /* darker green */
}

.auth-links span {
  margin: 0 0.5rem;
  color: #1f2937;
}
</style>
