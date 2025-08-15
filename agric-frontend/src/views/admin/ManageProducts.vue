<template>
  <div>
    <h2 class="title">📦 Manage Products</h2>

    <table class="product-table" v-if="products.length">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Price</th>
          <th>Farmer Name</th>
          <th>Farmer Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(product, index) in products" :key="product.id">
          <td>{{ index + 1 + (pagination.current_page - 1) * pagination.per_page }}</td>
          <td>{{ product.name }}</td>
          <td>${{ Number(product.price)?.toFixed(2) || '0.00' }}</td>
          <td>{{ product.user?.name || 'Unknown' }}</td>
          <td>{{ product.user?.email || 'Unknown' }}</td>
          <td>
            <button @click="openEditModal(product)" class="edit">✏️ Edit</button>
            <button class="remove" @click="removeProduct(product.id)">❌ Remove</button>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else>No products found.</p>

    <!-- Pagination controls -->
    <div class="pagination" v-if="pagination.total > pagination.per_page">
      <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">Prev</button>
      <span>Page {{ pagination.current_page }} / {{ pagination.last_page }}</span>
      <button :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal">
        <h3>Edit Product</h3>
        <form @submit.prevent="submitEdit">
          <label>
            Name:
            <input v-model="editForm.name" required />
          </label>
          <label>
            Price:
            <input type="number" v-model.number="editForm.price" min="0" step="0.01" required />
          </label>
          <label>
            Description:
            <textarea v-model="editForm.description"></textarea>
          </label>
          <label>
            Stock:
            <input type="number" v-model.number="editForm.stock" min="0" />
          </label>
          <label>
            Category ID:
            <input type="number" v-model.number="editForm.category_id" min="1" />
          </label>
          <!-- Add more fields as needed -->

          <label>
            Image:
            <input type="file" @change="handleImageUpload" accept="image/*" />
          </label>

          <div class="modal-actions">
            <button type="submit" class="btn-save">Save</button>
            <button type="button" class="btn-cancel" @click="closeEditModal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminManageProducts',
  data() {
    return {
      products: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
      },

      // Edit modal state
      showEditModal: false,
      editForm: {
        id: null,
        name: '',
        price: 0,
        description: '',
        stock: 0,
        category_id: null,
        image: null, // File object for image upload
      },
    };
  },
  methods: {
    async fetchProducts(page = 1) {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get(`http://127.0.0.1:8000/api/admin/products?page=${page}`, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        });

        this.products = response.data.data;
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total,
        };
      } catch (error) {
        console.error('Failed to fetch products:', error.response || error);
      }
    },

    async removeProduct(productId) {
      if (!confirm('Are you sure you want to remove this product?')) return;
      try {
        const token = localStorage.getItem('token');
        await axios.delete(`http://127.0.0.1:8000/api/admin/products/${productId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        alert('Product removed successfully.');
        await this.fetchProducts(this.pagination.current_page);
      } catch (error) {
        console.error('Failed to remove product:', error.response || error);
        alert('Failed to remove product.');
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.fetchProducts(page);
      }
    },

    // Open modal and fill form with product data
    openEditModal(product) {
      this.editForm.id = product.id;
      this.editForm.name = product.name || '';
      this.editForm.price = product.price || 0;
      this.editForm.description = product.description || '';
      this.editForm.stock = product.stock || 0;
      this.editForm.category_id = product.category_id || null;
      this.editForm.image = null; // reset image file input
      this.showEditModal = true;
    },

    // Close modal and reset form
    closeEditModal() {
      this.showEditModal = false;
      this.editForm = {
        id: null,
        name: '',
        price: 0,
        description: '',
        stock: 0,
        category_id: null,
        image: null,
      };
    },

    // Handle file input change
    handleImageUpload(event) {
      const file = event.target.files[0];
      this.editForm.image = file || null;
    },

    // Submit updated product data
    async submitEdit() {
      try {
        const token = localStorage.getItem('token');

        // Use FormData to handle file upload
        const formData = new FormData();
        formData.append('name', this.editForm.name);
        formData.append('price', this.editForm.price);
        formData.append('description', this.editForm.description);
        formData.append('stock', this.editForm.stock);
        if(this.editForm.category_id !== null) {
          formData.append('category_id', this.editForm.category_id);
        }
        if (this.editForm.image) {
          formData.append('image', this.editForm.image);
        }

        await axios.put(
          `http://127.0.0.1:8000/api/admin/products/${this.editForm.id}`,
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'multipart/form-data',
            },
          }
        );

        alert('Product updated successfully.');
        this.closeEditModal();
        await this.fetchProducts(this.pagination.current_page);
      } catch (error) {
        console.error('Failed to update product:', error.response || error);
        alert('Failed to update product.');
      }
    },
  },
  mounted() {
    this.fetchProducts();
  },
};
</script>

<style scoped>
.title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}
.product-table {
  width: 100%;
  border-collapse: collapse;
}
.product-table th,
.product-table td {
  border: 1px solid #ddd;
  padding: 0.75rem;
  text-align: left;
}
.product-table th {
  background-color: #f1f5f9;
}
button.remove {
  padding: 0.4rem 0.6rem;
  border: none;
  background-color: #ef4444;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  margin-left: 0.5rem;
}
button.remove:hover {
  background-color: #dc2626;
}
button.edit {
  padding: 0.4rem 0.6rem;
  border: none;
  background-color: #2563eb;
  color: white;
  border-radius: 4px;
  cursor: pointer;
}
button.edit:hover {
  background-color: #1e40af;
}
.pagination {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}
.pagination button {
  background-color: #2563eb;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
}
.pagination button[disabled] {
  background-color: #a5b4fc;
  cursor: not-allowed;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0,0,0,0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
.modal {
  background: white;
  padding: 1.5rem;
  border-radius: 6px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
.modal h3 {
  margin-top: 0;
  margin-bottom: 1rem;
}
.modal label {
  display: block;
  margin-bottom: 0.75rem;
}
.modal input,
.modal textarea {
  width: 100%;
  padding: 0.5rem;
  margin-top: 0.25rem;
  box-sizing: border-box;
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}
.btn-save {
  background-color: #16a34a;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
}
.btn-save:hover {
  background-color: #15803d;
}
.btn-cancel {
  background-color: #ef4444;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
}
.btn-cancel:hover {
  background-color: #dc2626;
}
</style>
