<template>
  <div class="app-container">
    <!-- Sidebar (Fixed) -->
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

    <!-- Main Content Area (Includes Header + Scrollable Content) -->
    <div class="main-wrapper">
      <!-- Dashboard Header (Fixed on top of main content) -->
      <header class="dashboard-header">
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
      </header>

      <!-- Scrollable Main Content -->
      <main class="main-content">
        <ProfileModal
          v-if="profileModalOpen"
          @close="closeProfileModal"
          @updated="onProfileUpdated"
        />

        <!-- Dashboard Overview -->
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

        <!-- Listings Section -->
        <section v-if="section === 'listings'" class="farmer-listings">
          <div class="header">
            <h2>🧺 My Product Listings</h2>
            <button @click="showForm = !showForm" class="btn-primary">
              {{ showForm ? (editMode ? 'Cancel Edit' : 'Cancel') : '➕ Add Product' }}
            </button>
          </div>

          <div class="filter-controls">
            <select v-model="selectedCategory" @change="filterProducts">
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Form -->
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
            <div class="form-group">
              <label>Category:</label>
              <select v-model="newProduct.category_id" required>
                <option value="">Select a category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Unit:</label>
              <select v-model="newProduct.unit_id" required>
                <option value="">Select a unit</option>
                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                  {{ unit.name }} ({{ unit.abbreviation }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Product Image:</label>
              <input type="file" @change="handleImageChange" accept="image/*" ref="fileInput" />
              <div v-if="imagePreview" class="image-preview">
                <img :src="imagePreview" alt="Preview" />
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
                <div class="meta-item">
                  <strong>₺{{ product.price }}</strong>
                  <span v-if="product.unit">/ {{ product.unit.abbreviation }}</span>
                </div>
                <div class="meta-item">
                  <strong>Qty:</strong> {{ product.quantity }}
                  <span v-if="product.unit"> {{ product.unit.abbreviation }}</span>
                </div>
                <div class="meta-item category-tag">
                  {{ getCategoryName(product.category_id) }}
                </div>
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
        <section v-if="section === 'messages'" class="messaging-section">
          <div class="messaging-container">
            <!-- Conversations List (Left Sidebar) -->
            <div class="conversation-list">
              <div class="conversation-header">
                <h3>Messages</h3>
                <div class="search-conversations">
                  <input
                    v-model="conversationSearchQuery"
                    type="text"
                    placeholder="Search conversations..."
                    @input="searchConversations"
                  />
                </div>
              </div>

              <!-- Loading State -->
              <div v-if="loadingConversations" class="loading-state">
                <p>Loading conversations...</p>
              </div>

              <!-- Empty State -->
              <div v-else-if="filteredConversations.length === 0" class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <p>No conversations found</p>
              </div>

              <!-- Conversations List -->
              <ul v-else class="conversations-list">
                <li
                  v-for="conv in filteredConversations"
                  :key="conv.id"
                  @click="selectConversation(conv)"
                  :class="['conversation-item', { active: currentConversation?.id === conv.id }]"
                >
                  <div class="conversation-info">
                    <img
                      :src="conv.avatarUrl || '/default-avatar.png'"
                      alt="Avatar"
                      class="conversation-avatar"
                    />
                    <div class="conversation-details">
                      <div class="conversation-header-row">
                        <strong class="conversation-title">{{ getConversationTitle(conv) }}</strong>
                        <span class="timestamp">{{ conv.lastMessageCreatedAt ? formatTime(conv.lastMessageCreatedAt) : '' }}</span>
                      </div>
                      <p class="last-message">{{ getLastMessagePreview(conv) }}</p>
                    </div>
                    <div v-if="conv.unread_count > 0" class="unread-indicator">
                      {{ conv.unread_count > 9 ? '9+' : conv.unread_count }}
                    </div>
                  </div>
                </li>
              </ul>
            </div>

            <!-- Chat Area (Right Side) -->
            <div class="chat-area">
              <!-- Placeholder -->
              <div v-if="!currentConversation" class="select-conversation">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>Select a conversation</h3>
                <p>Start messaging your contacts</p>
              </div>

              <!-- Active Chat -->
              <div v-else>
                <!-- Chat Header -->
                <div class="chat-header">
                  <div class="header-left">
                    <img
                      :src="currentConversation.avatarUrl || '/default-avatar.png'"
                      alt="Contact"
                      class="header-avatar"
                    />
                    <div class="header-info">
                      <h4>{{ getConversationTitle(currentConversation) }}</h4>
                      <p class="status">
                        {{ currentConversation.isOnline ? 'Online' : `Last seen ${formatTime(currentConversation.lastSeen)}` }}
                      </p>
                    </div>
                  </div>
                  <div class="header-actions">
                    <button class="action-button">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                      </svg>
                    </button>
                    <button class="action-button">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="1"/>
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                        <path d="M12 6v6l4 2"/>
                      </svg>
                    </button>
                  </div>
                </div>

            
        <!-- Messages Container -->
        <div
          class="messages-container"
          ref="messagesContainer"
          @scroll.passive="handleScroll"
        >
          <!-- Loading More Messages (top) -->
          <div v-if="loadingMoreMessages" class="loading-more-messages">
            <div class="loading-spinner"></div>
            <p>Loading older messages...</p>
          </div>

                  <!-- No Messages Yet -->
                  <div v-else-if="hasNoMessages" class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <p>No messages yet</p>
                    <small>Start the conversation</small>
                  </div>

                  <!-- Message List -->
                  <div v-else-if="!loadingMessages" class="messages-list">
                    <div
                      v-for="message in currentConversation.messages"
                      :key="message.id"
                      class="message-wrapper"
                      :class="{ sent: message.sender_id === userId, received: message.sender_id !== userId }"
                    >
                      <!-- Received Message -->
                      <div v-if="message.sender_id !== userId" class="received-message">
                        <img
                          v-if="showSenderInfo(message)"
                          :src="message.sender_avatar_url || '/default-avatar.png'"
                          alt="Sender"
                          class="sender-avatar"
                        />
                        <div class="message-content">
                          <div class="message-bubble received">
                            <p>{{ message.message }}</p>
                            <div class="message-meta">
                              <span class="timestamp">{{ formatTime(message.created_at) }}</span>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Sent Message -->
                      <div v-else class="sent-message">
                        <div class="message-content">
                          <div class="message-bubble sent">
                            <p>{{ message.message }}</p>
                            <div class="message-meta">
                              <span class="timestamp">{{ formatTime(message.created_at) }}</span>
                              <span class="message-status">
                                <svg v-if="message.status === 'pending'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M16 8v8l-8-4 8-4z"/>
                                </svg>
                                <svg v-else-if="message.status === 'sent'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg v-else-if="message.status === 'delivered'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M5 13l4 4L19 7" stroke-width="2"/>
                                </svg>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Initial Loading -->
                  <div v-if="loadingMessages && !hasNoMessages" class="loading-state">
                    <p>Loading messages...</p>
                  </div>
                </div>

                <!-- Message Input -->
                <div class="message-input">
                  <div class="input-actions">
                    <button class="emoji-button">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                        <line x1="9" y1="9" x2="9.01" y2="9"/>
                        <line x1="15" y1="9" x2="15.01" y2="9"/>
                      </svg>
                    </button>
                    <button class="attachment-button">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.19 9.19a2 2 0 0 1-2.83-2.83l9.19-9.19"/>
                      </svg>
                    </button>
                  </div>

                  <div class="input-wrapper">
                    <input
                      v-model="newMessage"
                      @keyup.enter="sendMessage"
                      placeholder="Type a message..."
                      @focus="markConversationAsRead"
                    />
                  </div>

                  <button
                    @click="sendMessage"
                    :disabled="!newMessage.trim()"
                    class="send-button"
                    :class="{ active: newMessage.trim() }"
                  >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <line x1="22" y1="2" x2="11" y2="13"/>
                      <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Feedback Section -->
        <section v-if="section === 'feedback'" class="content-section view-feedback">
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
                    <textarea v-model="replies[item.id]" placeholder="Write your reply here" rows="2" cols="25"></textarea>
                    <button @click="sendReply(item.id)" :disabled="!replies[item.id] || sendingReply[item.id]" class="btn-reply">
                      {{ sendingReply[item.id] ? 'Sending...' : 'Reply' }}
                    </button>
                  </div>
                </td>
                <td>
                  <button v-if="!item.approved" @click="approveFeedback(item.id)" class="btn-approve">✔ Approve</button>
                  <button @click="deleteFeedback(item.id)" class="btn-delete">🗑 Delete</button>
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
        </section>

        <!-- Sales History Section -->
        <section v-if="section === 'sales'" class="content-section">
          <h2>📦 Sales History</h2>
          <div v-if="salesLoading" class="loading">Loading sales data...</div>
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
          <div v-else class="empty-state">
            <p>Nothing has been sold yet.</p>
          </div>
        </section>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

// Initialize auth store
const authStore = useAuthStore()
const currentUser = computed(() => authStore.user)

// API configuration
axios.defaults.baseURL = 'http://127.0.0.1:8000'

// Section management
const section = ref('overview')
const switchSection = (newSection) => {
  section.value = newSection
  if (newSection === 'messages') {
    fetchConversations()
  } else if (newSection === 'feedback') {
    fetchFeedback()
  } else if (newSection === 'sales') {
    fetchSales()
  } else if (newSection === 'listings') {
    fetchProducts()
  }
}

// Profile modal
const profileModalOpen = ref(false)
const openProfileModal = () => profileModalOpen.value = true
const closeProfileModal = () => profileModalOpen.value = false
const onProfileUpdated = (newUserData) => {
  authStore.setUser(newUserData)
}

// Logout
const handleLogout = async () => {
  try {
    const token = localStorage.getItem('token')
    await axios.post('/api/logout', {}, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })
    localStorage.removeItem('token')
    window.location.href = '/login'
  } catch (error) {
    console.error('Logout failed:', error)
    localStorage.removeItem('token')
    window.location.href = '/login'
  }
}

// Product management
const products = ref([])
const categories = ref([])
const units = ref([])
const selectedCategory = ref('')
const showForm = ref(false)
const editMode = ref(false)
const editingProductId = ref(null)
const imagePreview = ref(null)
const fileInput = ref(null)

const newProduct = ref({
  name: '',
  description: '',
  price: 0,
  quantity: 0,
  category_id: '',
  unit_id: '',
  image: null
})

// Fetch data functions
const fetchProducts = async () => {
  try {
    const res = await axios.get('/api/farmer/products', {
      headers: getAuthHeaders()
    })
    products.value = res.data
  } catch (err) {
    console.error('Failed to fetch products:', err)
  }
}

const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories')
    categories.value = res.data
  } catch (err) {
    console.error('Failed to fetch categories:', err)
  }
}

const fetchUnits = async () => {
  try {
    const res = await axios.get('/api/units')
    units.value = res.data
  } catch (err) {
    console.error('Failed to fetch units:', err)
  }
}

// Product form handlers
const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (!file) return

  newProduct.value.image = file
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const submitProduct = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      alert('You are not logged in!')
      return
    }

    const formData = new FormData()
    formData.append('name', newProduct.value.name)
    formData.append('description', newProduct.value.description)
    formData.append('price', newProduct.value.price)
    formData.append('quantity', newProduct.value.quantity)
    formData.append('category_id', newProduct.value.category_id)
    formData.append('unit_id', newProduct.value.unit_id)

    if (newProduct.value.image) {
      formData.append('image', newProduct.value.image)
    }

    let response
    if (editMode.value && editingProductId.value) {
      // Update product
      response = await axios.post(
        `/api/farmer/products/${editingProductId.value}?_method=PUT`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        }
      )
    } else {
      // Create product
      response = await axios.post('/api/farmer/products', formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      })
    }

    await fetchProducts()
    resetForm()
  } catch (err) {
    console.error('Failed to submit product:', err.response?.data || err.message)
    alert('Error: ' + (err.response?.data?.message || 'Failed to submit product'))
  }
}

const startEdit = (product) => {
  editMode.value = true
  editingProductId.value = product.id
  showForm.value = true

  newProduct.value = {
    name: product.name,
    description: product.description,
    price: product.price,
    quantity: product.quantity,
    category_id: product.category_id,
    unit_id: product.unit_id,
    image: null
  }

  imagePreview.value = product.image_url || null
}

const deleteProduct = async (id) => {
  if (!confirm('Are you sure you want to delete this product?')) return
  
  try {
    await axios.delete(`/api/farmer/products/${id}`, {
      headers: getAuthHeaders()
    })
    await fetchProducts()
  } catch (err) {
    console.error('Failed to delete product:', err)
    alert('Failed to delete product')
  }
}

const resetForm = () => {
  showForm.value = false
  editMode.value = false
  editingProductId.value = null
  newProduct.value = {
    name: '',
    description: '',
    price: '',
    quantity: '',
    category_id: '',
    unit_id: '',
    image: null
  }
  imagePreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const cancelEdit = () => {
  resetForm()
}

const filteredProducts = computed(() => {
  if (!selectedCategory.value) return products.value
  return products.value.filter(product => product.category_id == selectedCategory.value)
})

const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id == categoryId)
  return category ? category.name : 'Uncategorized'
}

// Dashboard calculations
const calculateMonthlyRevenue = () => {
  const currentMonth = new Date().getMonth()
  const currentYear = new Date().getFullYear()
  
  const monthlyTotal = sales.value
    .filter(sale => {
      const saleDate = new Date(sale.created_at)
      return saleDate.getMonth() === currentMonth && saleDate.getFullYear() === currentYear
    })
    .reduce((total, sale) => total + parseFloat(sale.total_price || 0), 0)
  
  return monthlyTotal.toFixed(0)
}

const calculateAverageRating = () => {
  if (!feedback.value || feedback.value.length === 0) return '0.0'
  
  const totalRating = feedback.value.reduce((sum, item) => sum + (item.rating || 0), 0)
  const average = totalRating / feedback.value.length
  
  return average.toFixed(1)
}

// Messaging system
const conversations = ref([]);
const filteredConversations = ref([]);
const currentConversation = ref(null);
const newMessage = ref('');
const unreadCount = ref(0);
const loadingConversations = ref(false);
const loadingMessages = ref(false);
const conversationSearchQuery = ref('');
const loadingMoreMessages = ref(false);
const hasNoMessages = ref(false);
const messageSending = ref(false);
const selectedConversation = ref(null); // ← use this consistently
const conversationsLoading = ref(false);
const messagesLoading = ref(false);
const userId = ref(1); // Current user ID
// Fetch conversations with pagination support
const fetchConversations = async () => {
  try {
    loadingConversations.value = true;
    const res = await axios.get('/api/conversations', { 
      headers: getAuthHeaders()
    });
    
    conversations.value = res.data.data || res.data;
    filteredConversations.value = [...conversations.value];
    calculateUnreadCount();
  } catch (error) {
    console.error('Error fetching conversations:', error);
    alert('Failed to load conversations. Please try again.');
  } finally {
    loadingConversations.value = false;
  }
};

// Calculate unread messages count
const calculateUnreadCount = () => {
  unreadCount.value = conversations.value.reduce((count, convo) => {
    const lastMessageDate = convo.last_message?.created_at 
      ? new Date(convo.last_message.created_at) 
      : null;
    const lastReadDate = convo.last_read_at 
      ? new Date(convo.last_read_at) 
      : null;
      
    if (!lastReadDate || (lastMessageDate && lastMessageDate > lastReadDate)) {
      return count + (convo.unread_count || 1);
    }
    return count;
  }, 0);
};

// Search conversations by name or message content
const searchConversations = () => {
  if (!conversationSearchQuery.value.trim()) {
    filteredConversations.value = [...conversations.value];
    return;
  }
  
  const query = conversationSearchQuery.value.toLowerCase().trim();
  filteredConversations.value = conversations.value.filter(conv => {
    return (
      conv.user?.name?.toLowerCase().includes(query) ||
      conv.last_message?.message?.toLowerCase().includes(query) ||
      conv.product?.name?.toLowerCase().includes(query)
    );
  });
};

// Select a conversation and load its messages
const selectConversation = async (conversation) => {
  if (currentConversation.value?.id === conversation.id) return;
  
  try {
    currentConversation.value = conversation;
    loadingMessages.value = true;
    hasNoMessages.value = false;

    const res = await axios.get(`/api/conversations/${conversation.id}/messages`, {
      headers: getAuthHeaders(),
      params: {
        page: 1,
        per_page: 50
      }
    });

    // Attach messages to the conversation
    currentConversation.value.messages = res.data.data || res.data;
    hasNoMessages.value = currentConversation.value.messages.length === 0;
    
    // Mark as read if there are messages
    if (currentConversation.value.messages.length > 0) {
      await markConversationAsRead();
    }
  } catch (error) {
    console.error('Failed to load messages:', error);
    alert('Could not load conversation. Please try again.');
    currentConversation.value = null;
  } finally {
    loadingMessages.value = false;
  }
};

// Load more messages for pagination
const loadMoreMessages = async () => {
  if (!currentConversation.value || loadingMoreMessages.value) return;
  
  try {
    loadingMoreMessages.value = true;
    const oldestMessage = currentConversation.value.messages[0];
    const oldestMessageDate = oldestMessage?.created_at;
    
    const res = await axios.get(`/api/conversations/${currentConversation.value.id}/messages`, {
      headers: getAuthHeaders(),
      params: {
        before: oldestMessageDate,
        per_page: 20
      }
    });

    const newMessages = res.data.data || res.data;
    if (newMessages.length > 0) {
      currentConversation.value.messages = [...newMessages, ...currentConversation.value.messages];
    }
  } catch (error) {
    console.error('Error loading more messages:', error);
  } finally {
    loadingMoreMessages.value = false;
  }
};

// Handle scroll events for infinite loading
const handleScroll = (event) => {
  const container = event.target;
  if (container.scrollTop === 0 && !loadingMoreMessages.value) {
    loadMoreMessages();
  }
};


const sendMessage = async () => {
  const messageContent = newMessage.value.trim();
  if (!messageContent || !currentConversation.value || messageSending.value) return;

  try {
    messageSending.value = true;

    // Create temporary message for optimistic UI
    const tempMessage = {
      id: `temp-${Date.now()}`,
      message: messageContent,
      sender_id: userId.value,
      created_at: new Date().toISOString(),
      status: 'pending',
      sender: {
        id: userId.value,
        name: currentUser.value.name,
        avatar_url: currentUser.value.avatar_url,
      },
    };

    // Add to messages list
    currentConversation.value.messages = [
      ...currentConversation.value.messages,
      tempMessage,
    ];
    newMessage.value = '';

    // Scroll to bottom
    nextTick(() => {
      const container = document.querySelector('.messages-container');
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    });

    // Send to server
    const res = await axios.post(
      `/api/conversations/${currentConversation.value.id}/send`,
      {
        message: messageContent,
      },
      {
        headers: getAuthHeaders(),
      }
    );

    // Replace temp message with server response
    const newMessageData = res.data.data || res.data;
    const messageIndex = currentConversation.value.messages.findIndex(
      (m) => m.id === tempMessage.id
    );
    if (messageIndex !== -1) {
      currentConversation.value.messages[messageIndex] = newMessageData;
    } else {
      currentConversation.value.messages = [
        ...currentConversation.value.messages,
        newMessageData,
      ];
    }
  } catch (error) {
    console.error('Error sending message:', error);
    // Mark message as failed
    const failedIndex = currentConversation.value.messages.findIndex(
      (m) => m.id === `temp-${Date.now()}`
    );
    if (failedIndex !== -1) {
      currentConversation.value.messages[failedIndex].status = 'failed';
    }
    alert('Failed to send message. Please try again.');
  } finally {
    messageSending.value = false;
  }
};

// Mark conversation as read
const markConversationAsRead = async () => {
  if (!currentConversation.value) return;
  
  try {
    await axios.post(
      `/api/conversations/${currentConversation.value.id}/read`,
      {},
      { headers: getAuthHeaders() }
    );
    
    // Update local state
    const convIndex = conversations.value.findIndex(c => c.id === currentConversation.value.id);
    if (convIndex !== -1) {
      conversations.value[convIndex].last_read_at = new Date().toISOString();
      conversations.value[convIndex].unread_count = 0;
    }
    
    calculateUnreadCount();
  } catch (error) {
    console.error('Error marking conversation as read:', error);
  }
};

// Get conversation title (user or product name)
const getConversationTitle = (conversation) => {
  return conversation.user?.name || conversation.product?.name || 'Unknown User';
};

// Get last message preview text
const getLastMessagePreview = (conversation) => {
  if (!conversation.last_message) return 'No messages yet';
  
  const message = conversation.last_message.message || '';
  return message.length > 30 
    ? message.substring(0, 30) + '...' 
    : message;
};

// Check if we should show sender info (for grouped messages)
const showSenderInfo = (message, index) => {
  if (!currentConversation.value?.messages) return true;
  
  const messages = currentConversation.value.messages;
  const prevMessage = messages[index - 1];
  
  // Show avatar if:
  // 1. First message in conversation
  // 2. Previous message was from a different sender
  // 3. Previous message was more than 5 minutes ago
  if (!prevMessage) return true;
  
  if (prevMessage.sender_id !== message.sender_id) return true;
  
  const currentTime = new Date(message.created_at);
  const prevTime = new Date(prevMessage.created_at);
  const timeDiff = (currentTime - prevTime) / (1000 * 60); // in minutes
  
  return timeDiff > 5;
};

// Initialize messaging system
onMounted(() => {
  fetchConversations();
  
  // Optional: Set up real-time updates with WebSocket or polling
  // setupRealTimeUpdates();
});
// ✅ ADD: Watch for new messages in current conversation
watch(
  () => currentConversation.value?.messages?.length,
  (newLength, oldLength) => {
    // Only auto-scroll if messages were added (not initial load)
    if (oldLength && newLength > oldLength) {
      const wasAtBottom = isScrolledToBottom();
      if (wasAtBottom) {
        nextTick(() => scrollToBottom(true));
      }
    }
  }
);
 const scrollToBottom = (smooth = true) => {
  nextTick(() => {
    const container = messagesContainer.value;
    if (container) {
      container.scrollTo({
        top: container.scrollHeight,
        behavior: smooth ? 'smooth' : 'auto'
      });
    }
  });
};


// Feedback management
const feedback = ref([])
const feedbackLoading = ref(true)
const pagination = ref({})
const replies = reactive({})
const sendingReply = reactive({})

const fetchFeedback = async (page = 1) => {
  feedbackLoading.value = true
  try {
    const res = await axios.get(`/api/feedbacks/farmer?page=${page}`, {
      headers: getAuthHeaders()
    })
    feedback.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      per_page: res.data.per_page,
      total: res.data.total
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
    await axios.post(
      `/api/reviews/${id}/reply`, 
      { reply: replies[id] }, 
      { headers: getAuthHeaders() }
    )
    alert('Reply sent successfully.')
    fetchFeedback(pagination.value.current_page)
  } catch (err) {
    console.error('Failed to send reply:', err)
    alert('Failed to send reply.')
  } finally {
    sendingReply[id] = false
  }
}

const approveFeedback = async (id) => {
  try {
    await axios.post(
      `/api/reviews/${id}/approve`, 
      {}, 
      { headers: getAuthHeaders() }
    )
    alert('Feedback approved successfully.')
    fetchFeedback(pagination.value.current_page)
  } catch (err) {
    console.error('Failed to approve feedback:', err)
    alert('Failed to approve feedback.')
  }
}

const deleteFeedback = async (id) => {
  if (!confirm('Are you sure you want to delete this feedback?')) return
  try {
    await axios.delete(`/api/reviews/${id}`, {
      headers: getAuthHeaders()
    })
    alert('Feedback deleted successfully.')
    fetchFeedback(pagination.value.current_page)
  } catch (err) {
    console.error('Failed to delete feedback:', err)
    alert('Failed to delete feedback.')
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchFeedback(page)
  }
}

// Sales history
const sales = ref([])
const salesLoading = ref(false)

const fetchSales = async () => {
  try {
    salesLoading.value = true
    const res = await axios.get('/api/farmer/sales-history', {
      headers: getAuthHeaders()
    })
    sales.value = res.data
  } catch (error) {
    console.error('Error fetching sales history', error)
  } finally {
    salesLoading.value = false
  }
}

// Utility functions
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const getAuthHeaders = () => {
  const token = localStorage.getItem('token')
  return {
    Authorization: `Bearer ${token}`
  }
}

// Initialize data
onMounted(async () => {
  await fetchProducts()
  await fetchCategories()
  await fetchUnits()
  await fetchFeedback()
  await fetchSales()
})
</script>

<style scoped>
/* Your existing styles remain unchanged */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.app-container {
  display: flex;
  min-height: 100vh;
  background: rgba(30, 41, 59, 0.95);
  font-family: 'Segoe UI', sans-serif;
  color: white;
  position: relative;
}

.sidebar {
  width: 250px;
  height: calc(100vh - 72px);
  position: fixed;
  top: 72px;
  left: 0;
  background: rgba(30, 41, 59, 0.95);
  backdrop-filter: blur(10px);
  color: white;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  overflow-y: auto;
}

.sidebar h2 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #10b981;
  text-align: center;
}

.sidebar nav ul {
  list-style: none;
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

.main-wrapper {
  flex: 1;
  margin-left: 250px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.dashboard-header {
  position: fixed;
  top: 0;
  left: 250px;
  right: 0;
  height: 72px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  padding: 1.7rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  z-index: 400;
  color: white;
}

.greeting h2 {
  margin: 0;
  font-size: 1.8rem;
  color: white;
  font-weight: 600;
}

.greeting p {
  margin: 0.5rem 0 0 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 1rem;
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

.main-content {
  flex: 1;
  padding: 2rem;
  padding-top: 92px;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  overflow-y: auto;
  color: white;
}

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

.empty-state {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  padding: 3rem;
  font-style: italic;
  font-size: 1.1rem;
}

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

.loading {
  text-align: center;
  color: rgba(255, 255, 255, 0.7);
  padding: 2rem;
  font-style: italic;
}

/* Messaging section styles */
.messaging-section {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 20px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.messaging-container {
  display: flex;
  height: 70vh;
  border-radius: 15px;
  overflow: hidden;
}

.conversation-list {
  width: 300px;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  overflow-y: auto;
}

.conversation-header {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.search-conversations input {
  width: 100%;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.1);
  color: white;
  margin-top: 1rem;
}

.conversations-list {
  list-style: none;
}

.conversation-item {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: all 0.2s ease;
}

.conversation-item:hover {
  background: rgba(255, 255, 255, 0.1);
}

.conversation-item.active {
  background: rgba(16, 185, 129, 0.2);
}

.conversation-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.conversation-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.conversation-details {
  flex: 1;
}

.conversation-header-row {
  display: flex;
  justify-content: space-between;
}

.conversation-title {
  font-weight: 600;
}

.timestamp {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

.last-message {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  margin-top: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.unread-indicator {
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
}

.chat-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: rgba(255, 255, 255, 0.03);
}

.select-conversation {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: rgba(255, 255, 255, 0.6);
  text-align: center;
}

.select-conversation svg {
  margin-bottom: 1rem;
}

.chat-header {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.header-info h4 {
  margin: 0;
  font-size: 1.1rem;
}

.status {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 0.25rem;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.action-button {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
}

.action-button:hover {
  background: rgba(255, 255, 255, 0.1);
}

.messages-container {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message-wrapper {
  display: flex;
}

.sent {
  justify-content: flex-end;
}

.received {
  justify-content: flex-start;
}

.received-message {
  display: flex;
  gap: 0.5rem;
  max-width: 70%;
}

.sender-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  align-self: flex-end;
}

.message-content {
  display: flex;
  flex-direction: column;
}

.message-bubble {
  padding: 0.75rem 1rem;
  border-radius: 18px;
  max-width: 100%;
  word-wrap: break-word;
}

.message-bubble.sent {
  background: linear-gradient(135deg, #10b981, #059669);
  border-bottom-right-radius: 4px;
}

.message-bubble.received {
  background: rgba(255, 255, 255, 0.1);
  border-bottom-left-radius: 4px;
}

.message-meta {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.timestamp {
  font-size: 0.7rem;
  opacity: 0.8;
}

.message-status svg {
  width: 14px;
  height: 14px;
}

.message-input {
  display: flex;
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  align-items: center;
  gap: 0.5rem;
}

.input-actions {
  display: flex;
  gap: 0.5rem;
}

.emoji-button,
.attachment-button {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
}

.emoji-button:hover,
.attachment-button:hover {
  background: rgba(255, 255, 255, 0.1);
}

.input-wrapper {
  flex: 1;
}

.input-wrapper input {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.send-button {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
}

.send-button.active {
  color: #10b981;
}

.loading-state,
.loading-more-messages {
  text-align: center;
  padding: 1rem;
  color: rgba(255, 255, 255, 0.7);
}

.loading-spinner {
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-top: 2px solid #10b981;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  animation: spin 1s linear infinite;
  margin: 0 auto 0.5rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .app-container {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
    height: auto;
    position: static;
    padding: 1rem;
  }
  
  .main-wrapper {
    margin-left: 0;
  }
  
  .dashboard-header {
    left: 0;
  }
  
  .main-content {
    padding-top: 1rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .quick-actions {
    grid-template-columns: 1fr 1fr;
  }
  
  .messaging-container {
    flex-direction: column;
    height: auto;
  }
  
  .conversation-list {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }
  
  .chat-area {
    height: 60vh;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
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
  
  .feedback-table {
    font-size: 0.8rem;
  }
  
  .feedback-table th,
  .feedback-table td {
    padding: 0.5rem;
  }
}
</style>