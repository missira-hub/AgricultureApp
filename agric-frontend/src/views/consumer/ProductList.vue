<template>
  <div class="product-grid">
    <div
      v-for="product in products"
      :key="product.id"
      class="product-card"
    >
      <h3>{{ product.name }}</h3>
      <p>{{ product.description }}</p>
      <p><strong>{{ product.price }} ₺</strong></p>

      <input
        type="number"
        v-model.number="quantities[product.id]"
        min="1"
        placeholder="Quantity"
      />
<button @click="addToCart(product)">Add to Cart</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const products = ref([])
const quantities = ref({})
const router = useRouter()

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('Please log in first.')
    router.push('/login')
    return
  }

  try {
    const res = await axios.get('http://127.0.0.1:8000/api/products', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })
    products.value = res.data
  } catch (error) {
    if (error.response?.status === 401) {
      alert('Unauthorized: Please log in again.')
      router.push('/login')
    } else {
      alert('Failed to load products.')
    }
    console.error('Error loading products:', error)
  }
})

const buyProduct = async (productId) => {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('Please log in first.')
    return
  }

  const quantity = quantities.value[productId] || 1

  try {
    await axios.post(
      'http://127.0.0.1:8000/api/orders',
      {
        product_id: productId,
        quantity,
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    )
    alert('Order placed successfully!')
    quantities.value[productId] = null
  } catch (error) {
    const msg = error.response?.data?.message || 'Order failed.'
    alert(`Order failed: ${msg}`)
    console.error('Failed to place order:', error)
  }
}
</script>

<style scoped>
.product-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 1rem;
}

.product-card {
  border: 1px solid #ccc;
  padding: 1rem;
  width: 200px;
  border-radius: 10px;
  background-color: #f7ffe9;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.product-card h3 {
  margin-bottom: 0.5rem;
  font-size: 1.1rem;
  color: #333;
}

.product-card p {
  margin: 0.25rem 0;
}

.product-card input {
  width: 100%;
  padding: 0.5rem;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.product-card button {
  background: #84c57a;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 1rem;
  cursor: pointer;
  width: 100%;
  transition: background 0.3s;
}

.product-card button:hover {
  background: #6bb76e;
}
</style>
