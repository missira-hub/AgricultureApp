<template>
  <section class="services-section">
    <h1 class="services-title">Our Services</h1>
    <ul class="services-list">
      <li>
        <h3>🌾 Direct Farmer-to-Consumer Sales</h3>
        <p>We connect farmers and buyers directly — cutting out middlemen and ensuring fair pricing.</p>
      </li>
      <li>
        <h3>📦 Farm Product Listings</h3>
        <p>Farmers can list vegetables, fruits, dairy, fertilizers, and more for sale.</p>
      </li>
      <li>
        <h3>💬 Messaging System</h3>
        <p>Consumers and farmers can chat one-on-one to discuss products, negotiate, or ask questions.</p>
      </li>
      <li>
        <h3>💳 Secure Payments</h3>
        <p>Payments are safely handled through our secure system, ensuring protection for both parties.</p>
      </li>
      <li>
        <h3>🚚 Local Delivery Support</h3>
        <p>We assist with logistics by partnering with local delivery providers.</p>
      </li>
    </ul>
  </section>
</template>

<style scoped>
.services-section {
  background: linear-gradient(to bottom right, #ffffff, #f0fdf4);
  padding: 4rem 1.5rem;
  color: #1f2937;
}

.services-title {
  text-align: center;
  font-size: 2rem;
  font-weight: bold;
  color: #15803d;
  margin-bottom: 2.5rem;
}

.services-list {
  max-width: 900px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.services-list li {
  background: #ffffff;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: box-shadow 0.3s ease;
}

.services-list li:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.services-list h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #166534;
  margin-bottom: 0.5rem;
}

.services-list p {
  color: #475569;
  font-size: 0.95rem;
}
</style>
