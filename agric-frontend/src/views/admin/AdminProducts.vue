<template>
  <div class="admin-products">
    <h2>Product Management</h2>
    <table class="product-table">
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Farmer</th><th>Price</th><th>Stock</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>{{ product.id }}</td>
          <td>
            <input v-model="product.name" @blur="saveProduct(product)" />
          </td>
          <td>{{ product.user?.name || 'N/A' }}</td>
          <td>
            <input type="number" v-model="product.price" @blur="saveProduct(product)" />
          </td>
          <td>
            <input type="number" v-model="product.stock" @blur="saveProduct(product)" />
          </td>
          <td>
            <button @click="deleteProduct(product.id)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <button @click="loadProducts">Reload</button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      products: [],
    };
  },
  methods: {
    loadProducts() {
      axios.get('/api/admin/products')
        .then(res => {
          this.products = res.data.data;
        });
    },
    saveProduct(product) {
      axios.put(`/api/admin/products/${product.id}`, product)
        .then(() => alert('Product updated'))
        .catch(() => alert('Update failed'));
    },
    deleteProduct(id) {
      if (!confirm('Delete this product?')) return;
      axios.delete(`/api/admin/products/${id}`)
        .then(() => {
          this.products = this.products.filter(p => p.id !== id);
          alert('Deleted');
        });
    }
  },
  mounted() {
    this.loadProducts();
  }
};
</script>

<style scoped>
.product-table {
  width: 100%;
  border-collapse: collapse;
}
.product-table th, .product-table td {
  border: 1px solid #ccc;
  padding: 8px;
}
</style>
