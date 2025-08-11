<template>
  <div class="dashboard">
    <aside class="sidebar">
      <h2>FASI-MARKET</h2>
      <nav>
        <ul>
          <li @click="switchSection('overview')" :class="{ active: section === 'overview' }">📊 Dashboard</li>
          <li @click="switchSection('listings')" :class="{ active: section === 'listings' }">🧺 My Listings</li>
          <li @click="switchSection('messages')" :class="{ active: section === 'messages' }">
            💬 Messages <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
          </li>
          <li @click="switchSection('feedback')" :class="{ active: section === 'feedback' }">⭐ Feedback</li>
          <li @click="switchSection('sales')" :class="{ active: section === 'sales' }">📦 Sales History</li>
          <li class="logout" @click="handleLogout">
            <span>🚪 Logout</span>
          </li>
        </ul>
      </nav>
    </aside>

    <main class="main-content">
      <!-- Dashboard Header -->
      <div class="dashboard-header">
        <div class="greeting">
          <h2>👋 Hello, {{ currentUser?.name || 'Farmer' }}</h2>
          <p>Welcome back to your dashboard</p>
        </div>
        <div class="user-profile" @click="openProfileModal" title="Click to update your profile">
          <img
            v-if="currentUser?.avatar_url"
            :src="currentUser.avatar_url"
            alt="Profile"
            class="profile-picture clickable"
          />
          <div v-else class="profile-picture placeholder">👤</div>
        </div>
      </div>

      <ProfileModal
        v-if="profileModalOpen"
        @close="closeProfileModal"
        @updated="onProfileUpdated"
      />

      <!-- Dashboard Overview Section -->
      <section v-if="section === 'overview'" class="dashboard-overview">
       

        <!-- Stats Grid -->
        <div class="stats-grid">
          <div class="stat-card" @click="switchSection('listings')">
            <div class="stat-icon">📦</div>
            <div class="stat-number">{{ products.length }}</div>
            <div class="stat-label">Active Products</div>
          </div>

          <div class="stat-card" @click="switchSection('sales')">
            <div class="stat-icon">💰</div>
            <div class="stat-number">₺{{ calculateMonthlyRevenue() }}</div>
            <div class="stat-label">This Month</div>
          </div>

          <div class="stat-card" @click="switchSection('messages')">
            <div class="stat-icon">📬</div>
            <div class="stat-number">{{ unreadCount }}</div>
            <div class="stat-label">New Messages</div>
          </div>

          <div class="stat-card" @click="switchSection('feedback')">
            <div class="stat-icon">⭐</div>
            <div class="stat-number">{{ calculateAverageRating() }}</div>
            <div class="stat-label">Rating</div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-section">
          <h3 class="section-title">Quick Actions</h3>
          <div class="quick-actions">
            <button class="action-btn" @click="switchSection('listings'); showForm = true;">
              <span>➕</span> Add Product
            </button>
            <button class="action-btn" @click="switchSection('sales')">
              <span>📋</span> View Orders
            </button>
            <button class="action-btn" @click="switchSection('listings')">
              <span>📊</span> Update Inventory
            </button>
            <button class="action-btn" @click="switchSection('feedback')">
              <span>📈</span> View Feedback
            </button>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="content-section">
          <h3 class="section-title">Recent Activity</h3>
          <div class="activity-list">
            <div v-for="sale in sales.slice(0, 5)" :key="sale.id" class="activity-item">
              <div class="activity-icon">💰</div>
              <div class="activity-content">
                <p><strong>{{ sale.product_name }}</strong> sold</p>
                <span>{{ formatDate(sale.created_at) }}</span>
              </div>
              <div class="activity-value">₺{{ sale.total_price }}</div>
            </div>
            <div v-if="sales.length === 0" class="empty-activity">
              No recent activity
            </div>
          </div>
        </div>
      </section>

      <!-- Existing Listings Section -->
      <section v-if="section === 'listings'" class="farmer-listings">
        <div class="header">
          <h2>🧺 My Product Listings</h2>
          <button @click="showForm = !showForm" class="btn-primary">
            {{ showForm ? (editMode ? 'Cancel Edit' : 'Cancel') : '➕ Add Product' }}
          </button>
        </div>

        <!-- Category Filter -->
        <div class="filter-controls">
          <select v-model="selectedCategory" @change="filterProducts">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <!-- Form Section -->
        <form v-if="showForm" @submit.prevent="submitProduct" enctype="multipart/form-data" class="form">
          <div class="form-group">
            <input v-model="newProduct.name" placeholder="Product Name" required />
          </div>

          <div class="form-group">
            <textarea v-model="newProduct.description" placeholder="Description" required></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <input v-model.number="newProduct.price" type="number" min="0" step="0.01" placeholder="Price (₺)" required />
            </div>
            <div class="form-group">
              <input v-model.number="newProduct.quantity" type="number" min="0" placeholder="Quantity" required />
            </div>
          </div>

          <!-- Category Selection -->
          <div class="form-group">
            <label>Category:</label>
            <select v-model="newProduct.category_id" required>
              <option value="">Select a category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Unit Selection -->
          <div class="form-group">
            <label>Unit:</label>
            <select v-model="newProduct.unit_id" required>
              <option value="">Select a unit</option>
              <option v-for="unit in units" :key="unit.id" :value="unit.id">
                {{ unit.name }} ({{ unit.abbreviation }})
              </option>
            </select>
          </div>

          <!-- Image Upload -->
          <div class="form-group">
            <label>Product Image:</label>
            <input type="file" @change="handleImageChange" accept="image/*" />
            <div v-if="imagePreview" class="image-preview">
              <button @click="imagePreview = null" type="button">Remove</button>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary">
              {{ editMode ? 'Update Product' : 'Save Product' }}
            </button>
            <button v-if="editMode" @click="cancelEdit" type="button" class="btn-secondary">Cancel Edit</button>
          </div>
        </form>

        <!-- Product Grid -->
        <div v-if="filteredProducts.length === 0" class="empty-state">
          <p>No products found {{ selectedCategory ? 'in this category' : '' }}.</p>
        </div>

        <div v-else class="product-grid">
          <div v-for="product in filteredProducts" :key="product.id" class="product-card">
            <div class="product-image-container">
              <img v-if="product.image_url" :src="product.image_url" :alt="product.name" />
              <div v-else class="image-placeholder">No Image</div>
            </div>

            <div class="product-info">
              <h3>{{ product.name }}</h3>
              <p>{{ product.description }}</p>
            </div>

            <div class="product-meta-beautified">
              <!-- Price per unit -->
              <div class="meta-item">
                <strong>₺{{ product.price }}</strong>
                <span v-if="product.unit">/ {{ product.unit.abbreviation }}</span>
              </div>

              <!-- Quantity with unit -->
              <div class="meta-item">
                <strong>Qty:</strong> {{ product.quantity }}
                <span v-if="product.unit"> {{ product.unit.abbreviation }}</span>
              </div>

              <!-- Category tag -->
              <div class="meta-item category-tag">
                {{ getCategoryName(product.category_id) }}
              </div>

              <!-- Stock status -->
              <div class="meta-item" :class="{ 'out-of-stock': product.quantity === 0 }">
                {{ product.quantity > 0 ? 'In Stock' : 'Out of Stock' }}
              </div>

              <div class="product-actions">
                <button @click="startEdit(product)" class="btn-secondary">✏️ Edit</button>
                <button @click="deleteProduct(product.id)" class="btn-danger">🗑 Delete</button>
              </div>
            </div>
          </div>
        </div>
      </section>
  
      <!-- Messages Section -->
      <section v-if="section === 'messages' && !loading" class="consumer-dashboard-messages">
        <ChatView />
      </section>

      <!-- Feedback Section -->
      <section v-if="section === 'feedback'" class="content-section">
        <div class="view-feedback">
          <h2>⭐ Manage Feedback & Reply</h2>

          <table class="feedback-table" v-if="feedback.length">
            <thead>
              <tr>
                <th>User</th>
                <th>Product</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Posted At</th>
                <th>Approved</th>
                <th>Reply</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in feedback" :key="item.id">
                <td>{{ item.user?.name || 'Unknown' }}</td>
                <td>{{ item.product?.name || 'Unknown' }}</td>
                <td>{{ item.rating }} / 5</td>
                <td>{{ item.comment }}</td>
                <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                <td>
                  <span v-if="item.approved">✅ Yes</span>
                  <span v-else>❌ No</span>
                </td>
                <td>
                  <div v-if="item.reply">
                    <strong>Your reply:</strong> {{ item.reply }}
                  </div>
                  <div v-else>
                    <textarea
                      v-model="replies[item.id]"
                      placeholder="Write your reply here"
                      rows="2"
                      cols="25"
                    ></textarea>
                    <button
                      @click="sendReply(item.id)"
                      :disabled="!replies[item.id] || sendingReply[item.id]"
                      class="btn-reply"
                    >
                      {{ sendingReply[item.id] ? 'Sending...' : 'Reply' }}
                    </button>
                  </div>
                </td>
                <td>
                  <button 
                    v-if="!item.approved" 
                    @click="approveFeedback(item.id)" 
                    class="btn-approve"
                  >
                    ✔ Approve
                  </button>
                  <button @click="deleteFeedback(item.id)" class="btn-delete">
                    🗑 Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <p v-else>No feedback found.</p>

          <!-- Pagination -->
          <div class="pagination" v-if="pagination.total > pagination.per_page">
            <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">Prev</button>
            <span>Page {{ pagination.current_page }} / {{ pagination.last_page }}</span>
            <button :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
          </div>
        </div>
      </section>

<!-- Sales History -->
<section v-if="section === 'sales'" class="content-section">
  <h2>📦 Sales History</h2>

  <!-- Loader -->
  <div v-if="salesLoading" class="loading">Loading sales data...</div>

  <!-- Sales Available -->
  <div v-else-if="sales.length > 0" class="sales-grid">
    <div v-for="sale in sales" :key="sale.id" class="sale-card">
      <div class="sale-info">
        <h3>{{ sale.product_name }}</h3>
        <div class="sale-meta">
          <span>Order #{{ sale.order_id }}</span>
          <span>{{ formatDate(sale.created_at) }}</span>
        </div>
      </div>
      <div class="sale-stats">
        <div class="stat">
          <span class="label">Quantity</span>
          <span class="value">{{ sale.quantity }}</span>
        </div>
        <div class="stat">
          <span class="label">Total</span>
          <span class="value">{{ sale.total_price }} ₺</span>
        </div>
      </div>
    </div>
  </div>

  <!-- No Sales Case -->
  <div v-else class="empty-state">
    <p>Nothing has been sold yet.</p>
  </div>
</section>


    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios';
import ProfileModal from '@/components/ProfileModal2.vue'

const avatarUrl = ref('/default-avatar.png')
const searchQuery = ref('')

const toggleModal = () => {
  profileModalOpen.value = true
}

const onProfileUpdated = (newUserData) => {
  authStore.setUser(newUserData)
  avatarUrl.value = newUserData.avatar_url || '/default-avatar.png'
}

onMounted(() => {
  if (currentUser.value?.avatar_url) {
    avatarUrl.value = currentUser.value.avatar_url
  }
})



const profileModalOpen = ref(false)

const openProfileModal = () => {
  profileModalOpen.value = true
}

const closeProfileModal = () => {
  profileModalOpen.value = false
}

// Fetch current profile data
const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('http://127.0.0.1:8000/api/user/profile', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    authStore.setUser({
      ...res.data,
      avatar_url: res.data.avatar_url || '/default-avatar.png',
    })
  } catch (err) {
    console.error('Error fetching profile:', err)
  }
}

onMounted(fetchProfile)

axios.defaults.baseURL = 'http://127.0.0.1:8000';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const currentUser = computed(() => authStore.user);

// Section management - Updated to include overview
const section = ref('overview');
const switchSection = (newSection) => {
  section.value = newSection;
  if (newSection === 'messages') {
    fetchConversations();
  }
};

const handleLogout = async () => {
  try {
    const token = localStorage.getItem('token');
    await axios.post(
      'http://127.0.0.1:8000/api/logout',
      {},
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json'
        }
      }
    );
    localStorage.removeItem('token');
    window.location.href = '/login';
  } catch (error) {
    console.error('Logout failed:', error);
    localStorage.removeItem('token');
    window.location.href = '/login';
  }
};

const units = ref([]);

onMounted(async () => {
  try {
    const res = await axios.get('/api/units');
    units.value = res.data;
  } catch (error) {
    console.error('Failed to load units', error);
  }
});



// Product management
const products = ref([]);
const categories = ref([]);
const selectedCategory = ref('');
const showForm = ref(false);
const editMode = ref(false);
const editingProductId = ref(null);
const imagePreview = ref();
const fileInput = ref(null);

const newProduct = ref({
  name: '',
  description: '',
  price: 0,
  quantity: 0,
  category_id: '',
  unit_id: '',
  image: null,
});

// Dashboard calculation functions
const calculateMonthlyRevenue = () => {
  const currentMonth = new Date().getMonth();
  const currentYear = new Date().getFullYear();
  
  const monthlyTotal = sales.value
    .filter(sale => {
      const saleDate = new Date(sale.created_at);
      return saleDate.getMonth() === currentMonth && saleDate.getFullYear() === currentYear;
    })
    .reduce((total, sale) => total + parseFloat(sale.total_price || 0), 0);
  
  return monthlyTotal.toFixed(0);
};

const calculateAverageRating = () => {
  if (!feedback.value || feedback.value.length === 0) return '0.0';
  
  const totalRating = feedback.value.reduce((sum, item) => sum + (item.rating || 0), 0);
  const average = totalRating / feedback.value.length;
  
  return average.toFixed(1);
};

// Fetch products and categories from API
const fetchProducts = async () => {
  try {
    const res = await axios.get('/api/farmer/products');
    products.value = res.data;
  } catch (err) {
    console.error('Failed to fetch products:', err);
  }
};

const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories');
    categories.value = res.data;
  } catch (err) {
    console.error('Failed to fetch categories:', err);
  }
};

// Filter products by category
const filteredProducts = computed(() => {
  if (!selectedCategory.value) return products.value;
  return products.value.filter(product => product.category_id == selectedCategory.value);
});

const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id == categoryId);
  return category ? category.name : 'Uncategorized';
};

// Handle image selection
const handleImageChange = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  newProduct.value.image = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Submit product form
const submitProduct = async () => {
  try {
    const token = localStorage.getItem("token");
    if (!token) {
      alert("You are not logged in!");
      return;
    }

    const formData = new FormData();
    formData.append("name", newProduct.value.name);
    formData.append("description", newProduct.value.description);
    formData.append("price", newProduct.value.price);
    formData.append("quantity", newProduct.value.quantity);
    formData.append("category_id", newProduct.value.category_id);
    formData.append("unit_id", newProduct.value.unit_id);

    if (newProduct.value.image) {
      formData.append("image", newProduct.value.image);
    }

    let response;
    if (editMode.value && editingProductId.value) {
      // ✅ UPDATE PRODUCT
      response = await axios.post(
        `/api/farmer/products/${editingProductId.value}?_method=PUT`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "multipart/form-data",
          },
        }
      );
    } else {
      // ✅ CREATE PRODUCT
      response = await axios.post("/api/farmer/products", formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "multipart/form-data",
        },
      });
    }

    // ✅ Reload product list & reset form
    await fetchProducts();
    resetForm();
  } catch (err) {
    console.error(
      "Failed to submit product:",
      err.response?.data || err.message
    );
    alert(
      "Error: " +
        (err.response?.data?.message || "Failed to submit product")
    );
  }
};


// Edit product
const startEdit = (product) => {
  editMode.value = true;
  editingProductId.value = product.id;
  showForm.value = true;

  newProduct.value = {
    name: product.name,
    description: product.description,
    price: product.price,
    quantity: product.quantity,
    category_id: product.category_id,
    unit_id: product.unit_id,
    image: null,
  };

  imagePreview.value = product.image_url || null;
};

// Delete product
const deleteProduct = async (id) => {
  if (!confirm('Are you sure you want to delete this product?')) return;
  
  try {
    await axios.delete(`/api/farmer/products/${id}`);
    await fetchProducts();
  } catch (err) {
    console.error('Failed to delete product:', err);
    alert('Failed to delete product');
  }
};

// Reset form
const resetForm = () => {
  showForm.value = false;
  editMode.value = false;
  editingProductId.value = null;
  newProduct.value = {
    name: '',
    description: '',
    price: '',
    quantity: '',
    category_id: '',
    unit_id: '',
    image: null,
  };
  imagePreview.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

// Cancel edit
const cancelEdit = () => {
  resetForm();
};

// Messaging System
const conversations = ref([]);
const selectedConversation = ref(null);
const messages = ref([]);
const newMessage = ref('');
const unreadCount = ref(0);
const conversationsLoading = ref(false);
const messagesLoading = ref(false);

const fetchConversations = async () => {
  try {
    conversationsLoading.value = true;
    const res = await axios.get('/api/conversations', { headers: getAuthHeaders() });
    conversations.value = res.data.data || res.data;
    calculateUnreadCount();
  } catch (error) {
    console.error('Error fetching conversations:', error);
  } finally {
    conversationsLoading.value = false;
  }
};

const calculateUnreadCount = () => {
  unreadCount.value = conversations.value.reduce((count, convo) => {
    if (!convo.last_read_at || new Date(convo.last_message?.created_at) > new Date(convo.last_read_at)) {
      return count + 1;
    }
    return count;
  }, 0);
};

// Feedback management
const feedbackLoading = ref(true)
const feedback = ref([])
const pagination = ref({})
const replies = reactive({})
const sendingReply = reactive({})

const getAuthHeaders = () => {
  const token = localStorage.getItem('token')
  return {
    Authorization: `Bearer ${token}`,
  }
}

const fetchFeedback = async (page = 1) => {
  feedbackLoading.value = true
  try {
    const res = await axios.get(`/api/feedbacks/farmer?page=${page}`, {
      headers: getAuthHeaders(),
    })
    feedback.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      per_page: res.data.per_page,
      total: res.data.total,
    }

    feedback.value.forEach((fb) => {
      if (!replies[fb.id]) {
        replies[fb.id] = fb.reply || ''
      }
    })
  } catch (err) {
    console.error('Failed to load feedback:', err)
    alert('Failed to load feedback.')
  } finally {
    feedbackLoading.value = false
  }
}

const sendReply = async (id) => {
  if (!replies[id]) return alert('Reply cannot be empty.')
  sendingReply[id] = true
  try {
    await axios.post(`/api/reviews/${id}/reply`, { reply: replies[id] }, {
      headers: getAuthHeaders(),
    })
    alert('Reply sent successfully.')
    fetchFeedback()
  } catch (err) {
    console.error('Failed to send reply:', err)
    alert('Failed to send reply.')
  } finally {
    sendingReply[id] = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchFeedback(page)
  }
}

// Sales History
const sales = ref([]);
const salesLoading = ref(false);

const fetchSales = async () => {
  try {
    salesLoading.value = true;
    const res = await axios.get('/api/farmer/sales-history', { headers: getAuthHeaders() });
    sales.value = res.data;
  } catch (error) {
    console.error('Error fetching sales history', error);
  } finally {
    salesLoading.value = false;
  }
};

// Utility Functions
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

// Initialize data
onMounted(async () => {
  await fetchProducts();
  await fetchCategories();
  await fetchFeedback();
  await fetchSales();
});
</script>

<style scoped>
.dashboard {
  display: flex;
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background:  rgba(30, 41, 59, 0.95);
    position: relative;

  body, html {
  margin: 0;
  padding: 0;
  top: 0;
  left: 0;
  right: 0;
  box-sizing: border-box; /* Ensure consistent box model */
}
}

/* Sidebar */
.sidebar {
  width: 250px;
  background: rgba(30, 41, 59, 0.95);
  backdrop-filter: blur(10px);
  color: white;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  box-shadow: 2px 0 20px rgba(0,0,0,0.1);
}

.sidebar h2 {
  font-size: 1.5rem;
  font-weight: bold;
  text-align: center;
  color: #10b981;
  margin-bottom: 1rem;
}

.sidebar nav ul {
  list-style: none;
  padding: 0;
}

.sidebar nav li {
  padding: 0.75rem 1rem;
  margin: 0.5rem 0;
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.3s ease;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sidebar nav li:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(5px);
}

.sidebar nav li.active {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.badge {
  background: #ef4444;
  color: white;
  border-radius: 999px;
  padding: 0.25rem 0.6rem;
  font-size: 0.75rem;
  margin-left: auto;
}

/* Push all dashboard sections down */
.dashboard-overview,
.farmer-listings,
.consumer-dashboard-messages,
.view-feedback,
.sales-grid {
  margin-top: 100px;
}

/* Dashboard Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 1.5rem;
  border-radius: 0px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  min-height: 72px; /* Ensure consistent height */
  box-sizing: border-box;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  overflow-y: auto;
  
  /* Prevent overlap with fixed header */
  padding-top: calc(2rem + 72px); /* 32px + 72px = 104px */
}

.greeting h2 {
  margin: 0;
  font-size: 1.8rem;
  color: white;
  font-weight: 600;
    text-align: center;


}

.greeting p {
  margin: 0.5rem 0 0 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 1rem;
    text-align: center;

}
.user-profile {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
  border: 3px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.user-profile:hover {
  border-color: #10b981;
  transform: scale(1.05);
}

.profile-picture {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-picture.placeholder {
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

/* Search Container */
.search-container {
  margin-bottom: 2rem;
}

.search-bar {
  width: 100%;
  padding: 1rem 1.5rem;
  font-size: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 50px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  color: white;
  outline: none;
  transition: all 0.3s ease;
}

.search-bar::placeholder {
  color: rgba(255, 255, 255, 0.6);
}

.search-bar:focus {
  border-color: #10b981;
  box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 2rem;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  background: rgba(255, 255, 255, 0.15);
}

.stat-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.stat-number {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin-bottom: 0.5rem;
}

.stat-label {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Content Sections */
.content-section {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 2rem;
  border-radius: 20px;
  margin-bottom: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.section-title {
  color: white;
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

/* Quick Actions */
.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-btn {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  padding: 1rem 1.5rem;
  border-radius: 15px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
}

/* Activity List */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.activity-icon {
  font-size: 1.5rem;
  width: 40px;
  text-align: center;
}

.activity-content {
  flex: 1;
}

.activity-content p {
  margin: 0;
  color: white;
  font-weight: 500;
}

.activity-content span {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
}

.activity-value {
  color: #10b981;
  font-weight: bold;
  font-size: 1.1rem;
}

.empty-activity {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  padding: 2rem;
  font-style: italic;
}

/* Listings Section */
.farmer-listings {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 2rem;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header h2 {
  color: white;
  font-size: 1.5rem;
  margin: 0;
}

.btn-primary {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

/* Filter Controls */
.filter-controls {
  margin-bottom: 2rem;
}

.filter-controls select {
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  font-size: 1rem;
  min-width: 200px;
}

.filter-controls select option {
  background: #1e293b;
  color: white;
}

/* Form Styles */
.form {
  background: rgba(255, 255, 255, 0.05);
  padding: 2rem;
  border-radius: 15px;
  margin-bottom: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: white;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.2);
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.4);
}

/* Image Preview */
.image-preview {
  margin-top: 1rem;
  position: relative;
}

.image-preview img {
  max-width: 200px;
  max-height: 200px;
  border-radius: 12px;
  object-fit: cover;
}

.image-preview button {
  position: absolute;
  top: 5px;
  right: 5px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  font-size: 0.8rem;
}

/* Product Grid */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.product-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}

.product-image-container {
  width: 100%;
  height: 200px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.1);
}

.product-image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.5);
  font-style: italic;
}

.product-info {
  padding: 1rem;
}

.product-info h3 {
  color: white;
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
}

.product-info p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.4;
}

.product-meta-beautified {
  padding: 0 1rem 1rem 1rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  color: white;
  font-size: 0.9rem;
}

.meta-item strong {
  color: #10b981;
}

.category-tag {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  display: inline-block;
  margin: 0.5rem 0;
}

.out-of-stock {
  color: #ef4444 !important;
}

.product-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.btn-danger:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

/* Empty State */
.empty-state {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  padding: 3rem;
  font-style: italic;
  font-size: 1.1rem;
}

/* Feedback Section */
.view-feedback h2 {
  color: white;
  margin-bottom: 2rem;
}

.feedback-table {
  width: 100%;
  border-collapse: collapse;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  overflow: hidden;
}

.feedback-table th,
.feedback-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
}

.feedback-table th {
  background: rgba(16, 185, 129, 0.2);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.9rem;
  letter-spacing: 0.5px;
}

.feedback-table textarea {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  border-radius: 8px;
  padding: 0.5rem;
  resize: vertical;
}

.btn-reply,
.btn-approve,
.btn-delete {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  margin: 0.25rem;
  transition: all 0.3s ease;
}

.btn-reply {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.btn-approve {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-delete {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.btn-reply:hover,
.btn-approve:hover,
.btn-delete:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  color: white;
}

.pagination button {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination button:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Sales Section */
.sales-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.sale-card {
  background: rgba(255, 255, 255, 0.05);
  padding: 1.5rem;
  border-radius: 15px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.sale-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.sale-info h3 {
  color: white;
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
}

.sale-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.sale-meta span {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9rem;
}

.sale-stats {
  display: flex;
  justify-content: space-between;
}

.stat {
  text-align: center;
}

.stat .label {
  display: block;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
}

.stat .value {
  color: #10b981;
  font-weight: bold;
  font-size: 1.1rem;
}

/* Loading States */
.loading {
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
  padding: 2rem;
  font-style: italic;
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
    height: auto;
    padding: 1rem;
  }
  
  .sidebar nav ul {
    display: flex;
    overflow-x: auto;
    gap: 0.5rem;
  }
  
  .sidebar nav li {
    white-space: nowrap;
    min-width: fit-content;
  }
  
  .main-content {
    padding: 1rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
  
  .quick-actions {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .product-grid {
    grid-template-columns: 1fr;
  }
  
  .sales-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .feedback-table {
    font-size: 0.8rem;
  }
  
  .feedback-table th,
  .feedback-table td {
    padding: 0.5rem;
  }
}

    </style>