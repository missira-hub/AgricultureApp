<template>
  <div class="dashboard">
    <aside class="sidebar">
      <h2>FASI-MARKET</h2>
      <nav>
        <ul>
          <li @click="switchSection('listings')" :class="{ active: section === 'listings' }">🧺 My Listings</li>
          <li @click="switchSection('messages')" :class="{ active: section === 'messages' }">
            💬 Messages <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
          </li>
          <li @click="switchSection('feedback')" :class="{ active: section === 'feedback' }">⭐ Feedback</li>
          <li @click="switchSection('sales')" :class="{ active: section === 'sales' }">📦 Sales History</li>
        </ul>
      </nav>
    </aside>

    <main class="main-content">
      <div class="farmer-listings">
        <div class="header">
          <h2>🧺 My Product Listings</h2>
          <button @click="showForm = !showForm" class="btn-primary">
            {{ showForm ? (editMode ? 'Cancel Edit' : 'Cancel') : '➕ Add Product' }}
          </button>
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
          

          <!-- Image Upload -->
          <div class="form-group">
            <label>Product Image:</label>
            <input type="file" @change="handleImageChange" accept="image/*" />
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
        <div v-if="products.length === 0" class="empty-state">
          <p>No products listed yet.</p>
        </div>

        <div v-else class="product-grid">
          <div v-for="product in products" :key="product.id" class="product-card">
            <div class="product-image-container">
              <img v-if="product.image_url" :src="product.image_url" :alt="product.name" />
              <div v-else class="image-placeholder">No Image</div>
            </div>

            <div class="product-details">
              <h3>{{ product.name }}</h3>
              <p class="description">{{ product.description }}</p>
              <div class="product-meta">
                <span class="price">₺ {{ product.price }}</span>
                <span class="quantity">Qty: {{ product.quantity }}</span>
              </div>
              <div class="product-actions">
                <button @click="startEdit(product)" class="btn-secondary">✏️ Edit</button>
                <button @click="deleteProduct(product.id)" class="btn-danger">🗑 Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>
  

      <!-- Messages Section -->
      
      <section v-if="section === 'messages'" class="content-section">
        <div class="conversation-layout">
          <!-- Conversation List -->
          <div class="conversation-list">
            <h2>💬 Conversations</h2>
            <div v-if="conversationsLoading" class="loading">Loading conversations...</div>
            <div v-else>
              <div v-if="conversations.length">
                <div 
                  v-for="convo in conversations" 
                  :key="convo.id"
                  @click="selectConversation(convo)"
                  :class="['conversation-item', { active: selectedConversation?.id === convo.id, unread: hasUnreadMessages(convo) }]"
                >
                  <div class="conversation-header">
                    <span class="participant">
                      {{ getOtherParticipant(convo.users)?.name || 'Unknown User' }}
                    </span>
                    <span class="time">{{ formatDate(convo.last_message?.created_at) }}</span>
                  </div>
                  <p class="preview">
                    {{ convo.last_message?.body || 'No messages yet' }}
                  </p>
                </div>
              </div>
              <p v-else class="empty-state">No conversations yet.</p>
            </div>
          </div>

          <!-- Message View -->
          <div class="message-view" v-if="selectedConversation">
            <div class="message-header">
              <h3>
                {{ getOtherParticipant(selectedConversation.users)?.name || 'Unknown User' }}
                <span v-if="selectedConversation.product">about {{ selectedConversation.product.name }}</span>
              </h3>
              <button @click="selectedConversation = null" class="btn-close">✕</button>
            </div>

            <div class="messages-container" ref="messagesContainer">
              <div v-if="messagesLoading" class="loading">Loading messages...</div>
              <div v-else>
                <div v-for="message in messages" :key="message.id" 
                     :class="['message', { 'sent': message.user_id === currentUser.id, 'received': message.user_id !== currentUser.id }]">
                  <div class="message-content">
                    <p>{{ message.body }}</p>
                    <span class="time">{{ formatTime(message.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <form @submit.prevent="sendMessage" class="message-composer">
              <textarea v-model="newMessage" placeholder="Type your message..." required></textarea>
              <button type="submit" class="btn-primary">Send</button>
            </form>
          </div>
          <div v-else class="select-conversation">
            <p>Select a conversation to view messages</p>
          </div>
        </div>
      </section>

      <!-- Feedback Section -->
      <section v-if="section === 'feedback'" class="content-section">
        <h2>⭐ Product Feedback</h2>
        <div v-if="feedbackLoading" class="loading">Loading feedback...</div>
        <div v-else>
          <div v-if="feedback.length" class="feedback-list">
            <div v-for="item in feedback" :key="item.id" class="feedback-card">
              <div class="feedback-header">
                <span class="user">{{ item.user.name }}</span>
                <span class="rating">⭐ {{ item.rating }}/5</span>
              </div>
              <p class="comment">{{ item.comment }}</p>
              <div v-if="item.reply" class="reply">
                <strong>Your reply:</strong> {{ item.reply }}
              </div>
              <div v-else class="reply-form">
                <textarea v-model="replyText[item.id]" placeholder="Write a reply..."></textarea>
                <button @click="submitReply(item.id)" class="btn-primary">Send Reply</button>
              </div>
              <div class="feedback-actions">
                <span class="date">{{ formatDate(item.created_at) }}</span>
                <button v-if="!item.approved" @click="approveFeedback(item.id)" class="btn-secondary">
                  Approve
                </button>
              </div>
            </div>
          </div>
          <p v-else class="empty-state">No feedback received yet.</p>
        </div>
      </section>

      <!-- Sales History -->
      <section v-if="section === 'sales'" class="content-section">
        <h2>📦 Sales History</h2>
        <div v-if="salesLoading" class="loading">Loading sales data...</div>
        <div v-else>
          <div v-if="sales.length" class="sales-grid">
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
          <p v-else class="empty-state">No sales recorded yet.</p>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import axios from 'axios';

import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const currentUser = computed(() => authStore.user);

// Section management
const section = ref('listings');
const switchSection = (newSection) => {
  section.value = newSection;
  if (newSection === 'messages') {
    fetchConversations();
  }
};

// Product management
const products = ref([]);
const showForm = ref(false);
const editMode = ref(false);
const editingProductId = ref(null);
const imagePreview = ref(null);
const fileInput = ref(null);

const newProduct = ref({
  name: '',
  description: '',
  price: '',
  quantity: '',
  image: null,
});

// Fetch products from API
const fetchProducts = async () => {
  try {
    const res = await axios.get('/api/farmer/products');
    products.value = res.data;
  } catch (err) {
    console.error('Failed to fetch products:', err);
  }
};

// Handle image selection
const handleImageChange = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  newProduct.value.image = file;

  // Create preview URL
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Remove selected image
const removeImage = () => {
  newProduct.value.image = null;
  imagePreview.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

// Submit product form
const submitProduct = async () => {
  try {
    const formData = new FormData();
    formData.append('name', newProduct.value.name);
    formData.append('description', newProduct.value.description);
    formData.append('price', newProduct.value.price);
    formData.append('quantity', newProduct.value.quantity);

    if (newProduct.value.image) {
      formData.append('image', newProduct.value.image);
    }

    let response;
    if (editMode.value && editingProductId.value) {
      // Update existing product
      response = await axios.post(
        `/api/farmer/products/${editingProductId.value}?_method=PUT`,
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        }
      );
    } else {
      // Create new product
      response = await axios.post('/api/farmer/products', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
    }

    await fetchProducts();
    resetForm();
  } catch (err) {
    console.error('Failed to submit product:', err.response?.data || err.message);
    alert('Error: ' + (err.response?.data?.message || 'Failed to submit product'));
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
    image: null, // We don't set the file object here
  };

  // Set preview to existing image URL
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

// Initialize
onMounted(() => {
  fetchProducts();
});


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
    const res = await axios.get('/api/messages/conversations');
    conversations.value = res.data.data || res.data;
    calculateUnreadCount();
  } catch (error) {
    console.error('Error fetching conversations:', error);
  } finally {
    conversationsLoading.value = false;
  }
};

const fetchMessages = async (conversationId) => {
  try {
    messagesLoading.value = true;
    const res = await axios.get(`/api/messages/conversations/${conversationId}/messages`);
    messages.value = res.data.data || res.data;
    await nextTick();
    scrollToBottom();
    markAsRead(conversationId);
  } catch (error) {
    console.error('Error fetching messages:', error);
  } finally {
    messagesLoading.value = false;
  }
};

const selectConversation = async (conversation) => {
  selectedConversation.value = conversation;
  await fetchMessages(conversation.id);
};

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedConversation.value) return;

  try {
    const res = await axios.post(
      `/api/messages/conversations/${selectedConversation.value.id}/messages`,
      { body: newMessage.value }
    );
    
    messages.value.push(res.data);
    newMessage.value = '';
    await nextTick();
    scrollToBottom();
    
    // Update last message in conversation list
    const convoIndex = conversations.value.findIndex(c => c.id === selectedConversation.value.id);
    if (convoIndex !== -1) {
      conversations.value[convoIndex].last_message = res.data;
    }
  } catch (error) {
    console.error('Error sending message:', error);
  }
};

const markAsRead = async (conversationId) => {
  try {
    await axios.post(`/api/messages/conversations/${conversationId}/read`);
    calculateUnreadCount();
  } catch (error) {
    console.error('Error marking as read:', error);
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

const hasUnreadMessages = (conversation) => {
  return !conversation.last_read_at || 
    (conversation.last_message && 
     new Date(conversation.last_message.created_at) > new Date(conversation.last_read_at));
};

const getOtherParticipant = (users) => {
  return users.find(user => user.id !== currentUser.value.id);
};

const scrollToBottom = () => {
  const container = messagesContainer.value;
  if (container) {
    container.scrollTop = container.scrollHeight;
  }
};

// Feedback System
const feedback = ref([]);
const feedbackLoading = ref(false);
const replyText = ref({});

const fetchFeedback = async () => {
  try {
    feedbackLoading.value = true;
    const res = await axios.get('/api/feedback/farmer');
    feedback.value = res.data.data || res.data;
  } catch (error) {
    console.error('Error fetching feedback:', error);
  } finally {
    feedbackLoading.value = false;
  }
};

const approveFeedback = async (id) => {
  try {
    await axios.post(`/api/feedback/${id}/approve`);
    await fetchFeedback();
  } catch (error) {
    console.error('Error approving feedback:', error);
  }
};

const submitReply = async (feedbackId) => {
  if (!replyText.value[feedbackId]?.trim()) return;

  try {
    await axios.post(`/api/feedback/${feedbackId}/reply`, {
      reply: replyText.value[feedbackId]
    });
    replyText.value[feedbackId] = '';
    await fetchFeedback();
  } catch (error) {
    console.error('Error submitting reply:', error);
  }
};

// Sales History
const sales = ref([]);
const salesLoading = ref(false);

const fetchSales = async () => {
  try {
    salesLoading.value = true;
    const res = await axios.get('/api/farmer/orders');
    sales.value = res.data.data || res.data;
  } catch (error) {
    console.error('Error fetching sales:', error);
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
  await fetchFeedback();
  await fetchSales();
});
</script>


<style scoped>
.dashboard {
  display: flex;
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
  background: #f0f2f5;
}

/* Sidebar */
.sidebar {
  width: 250px;
  background: #1e293b;
  color: white;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.sidebar h2 {
  font-size: 1.5rem;
  font-weight: bold;
  text-align: center;
}

.sidebar nav ul {
  list-style: none;
  padding: 0;
}

.sidebar nav li {
  padding: 0.75rem 1rem;
  margin: 0.5rem 0;
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.3s;
  font-weight: 500;
}

.sidebar nav li:hover {
  background: #334155;
}

.sidebar nav li.active {
  background: #0ea5e9;
  color: white;
}

.badge {
  background: #ef4444;
  color: white;
  border-radius: 999px;
  padding: 0.25rem 0.6rem;
  font-size: 0.75rem;
  margin-left: 0.5rem;
}

/* Main Area */
.main-content {
  flex: 1;
  padding: 2rem;
  background: #f9fafb;
}

/* Section Container */
.content-section {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Farmer Listings */
.farmer-listings .header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.btn-primary {
  background-color: #10b981;
  color: white;
  border: none;
  padding: 0.6rem 1rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-primary:hover {
  background-color: #059669;
}

.btn-secondary {
  background: #3b82f6;
  color: white;
  padding: 0.4rem 0.8rem;
  border-radius: 5px;
  border: none;
  cursor: pointer;
}

.btn-danger {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-danger:hover {
  background: #dc2626;
}

.form input,
.form textarea {
  width: 100%;
  padding: 0.8rem;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  margin-bottom: 1rem;
}

.form-row {
  display: flex;
  gap: 1rem;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.product-card {
  background: white;
  padding: 1.2rem;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.product-meta {
  display: flex;
  justify-content: space-between;
  color: #6b7280;
  font-size: 0.9rem;
}

.empty-state {
  text-align: center;
  color: #9ca3af;
  padding: 2rem;
}

/* Messaging */
.conversation-layout {
  display: flex;
  gap: 1rem;
  height: 70vh;
}

.conversation-list {
  width: 300px;
  border-right: 1px solid #e5e7eb;
  padding-right: 1rem;
  overflow-y: auto;
}

.conversation-item {
  padding: 1rem;
  border-radius: 8px;
  cursor: pointer;
  margin-bottom: 0.5rem;
  transition: background 0.3s;
}

.conversation-item:hover {
  background: #f3f4f6;
}

.conversation-item.active {
  background: #bae6fd;
}

.conversation-item.unread {
  font-weight: bold;
}

.message-view {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 10px;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
  margin-bottom: 1rem;
}

.message {
  max-width: 75%;
  margin-bottom: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  position: relative;
}

.message.sent {
  background: #dcfce7;
  align-self: flex-end;
}

.message.received {
  background: #e0f2fe;
  align-self: flex-start;
}

.message .time {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.message-composer {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.message-composer textarea {
  flex: 1;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  resize: none;
}

/* Feedback Cards */
.feedback-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.feedback-card {
  background: #fff;
  padding: 1.25rem;
  border-radius: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.feedback-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.feedback-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 0.75rem;
  font-size: 0.85rem;
  color: #6b7280;
}

/* Sales Cards */
.sales-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.sale-card {
  background: white;
  padding: 1.25rem;
  border-radius: 10px;
  box-shadow: 0 1px 6px rgba(0,0,0,0.05);
}

.sale-info h3 {
  margin-bottom: 0.25rem;
}

.sale-meta {
  font-size: 0.85rem;
  color: #6b7280;
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.sale-stats {
  display: flex;
  justify-content: space-between;
  font-weight: 500;
}

.stat .label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
}
</style>
