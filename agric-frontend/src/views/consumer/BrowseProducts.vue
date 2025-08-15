<template>
  <div class="browse-page">
    <h2>Available Products</h2>

    <div v-if="loading" class="status-msg">Loading products...</div>
    <div v-if="!loading && products.length === 0" class="status-msg">No products available right now.</div>

    <div class="product-list">
      <div v-for="(product, index) in products" :key="index" class="product-card">
        <h3>{{ product.name }}</h3>
        <p>Price: {{ product.price }} XAF</p>
        <p>In stock: {{ product.quantity }}</p>
        <button @click="orderProduct(product.id)">Place Order</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'BrowseProducts',
  data() {
    return {
      products: [],
      loading: true
    };
  },
  methods: {
    async fetchProducts() {
      this.loading = true;
      try {
        const token = localStorage.getItem('token');
        const res = await axios.get('http://127.0.0.1:8000/api/products', {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        this.products = res.data;
      } catch (err) {
        console.error('Error fetching products:', err);
      } finally {
        this.loading = false;
      }
    },
    async orderProduct(productId) {
      alert(`Order for product ID ${productId} will be handled here.`);
      // Example POST request you can build:
      // await axios.post('http://127.0.0.1:8000/api/orders', { product_id: productId }, { headers: { Authorization: `Bearer ${token}` } });
    }
  },
  mounted() {
    this.fetchProducts();
  }
};
</script>

<style scoped>
.browse-page {
  padding: 2rem;
  background: #f5f5f5;
  min-height: 100vh;
  font-family: 'Segoe UI', sans-serif;
}

.status-msg {
  text-align: center;
  color: #888;
  margin-top: 2rem;
}

.product-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.product-card {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
  text-align: center;
}

.product-card h3 {
  margin-bottom: 0.5rem;
}

.product-card button {
  margin-top: 1rem;
  padding: 10px 20px;
  background: #42b983;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.product-card button:hover {
  background: #36996b;
}
</style>
