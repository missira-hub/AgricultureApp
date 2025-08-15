<template>
  <div class="cart-page">
    <h1>Your Cart</h1>

    <div v-if="loading">Loading cart...</div>

    <div v-else-if="cart.length === 0">
      <p>Your cart is empty.</p>
    </div>

    <div v-else class="cart-items">
      <div v-for="item in cart" :key="item.product_id" class="cart-item">
        <h3>{{ item.name }}</h3>
        <p>Quantity: {{ item.quantity }}</p>
        <p>Unit Price: {{ item.unit_price }} ₺</p>
        <p>Total Price: {{ item.total_price }} ₺</p>
      </div>

      <div class="cart-summary">
        <p><strong>Total Cart Value:</strong> {{ totalCartValue }} ₺</p>
        <button @click="checkout">Proceed to Checkout</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const cart = ref([])
const totalCartValue = ref(0)
const loading = ref(true)

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('Please login to view your cart.')
    return
  }

  try {
    const res = await axios.get('http://127.0.0.1:8000/api/cart', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    cart.value = res.data.cart
    totalCartValue.value = res.data.total_cart_value
  } catch (error) {
    console.error('Failed to load cart:', error)
    alert('Error loading cart.')
  } finally {
    loading.value = false
  }
})

const checkout = () => {
  alert('Redirecting to payment gateway...')
}
</script>

<style scoped>
.cart-page {
  padding: 1rem;
  font-family: 'Poppins', sans-serif;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-item {
  background: #f9fff0;
  border: 1px solid #cdd;
  border-radius: 10px;
  padding: 1rem;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.cart-summary {
  margin-top: 2rem;
  padding: 1rem;
  border-top: 2px dashed #ccc;
  font-size: 1.1rem;
  font-weight: bold;
}

button {
  margin-top: 1rem;
  background: #84c57a;
  color: white;
  border: none;
  padding: 0.7rem 1.5rem;
  border-radius: 10px;
  cursor: pointer;
}

button:hover {
  background: #6bb76e;
}
</style>
