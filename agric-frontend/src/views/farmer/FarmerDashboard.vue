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
          <li @click="switchSection('orders')" :class="{ active: section === 'orders' }">
  📦 Orders
</li>
          <li @click="switchSection('sales')" :class="{ active: section === 'sales' }">📦 Sales History</li>
          <li class="logout" @click="handleLogout">
            <span>🚪 Logout</span>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="main-wrapper">
      <!-- Dashboard Header -->
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
    <p><strong>{{ sale.product?.name }}</strong> × {{ sale.quantity }}</p>
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
            <button @click="switchSection('listings'); showForm = !showForm;" class="btn-primary">
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

          <!-- Add/Edit Product Form -->
          <form v-if="showForm" @submit.prevent="submitProduct" enctype="multipart/form-data" class="form">
            <div class="form-row">
              <div class="form-group">
                <label for="productName">Product Name</label>
                <input
                  id="productName"
                  v-model="newProduct.name"
                  type="text"
                  placeholder="e.g. Organic Tomatoes"
                  required
                />
              </div>
              <div class="form-group">
                <label for="productPrice">Price (₺)</label>
                <input
                  id="productPrice"
                  v-model.number="newProduct.price"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="e.g. 12.99"
                  required
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="productQuantity">Quantity in Stock</label>
                <input
                  id="productQuantity"
                  v-model.number="newProduct.quantity"
                  type="number"
                  min="0"
                  placeholder="e.g. 100"
                  required
                />
              </div>
              <div class="form-group">
                <label for="productUnit">Unit of Measure</label>
                <select id="productUnit" v-model="newProduct.unit_id" required>
                  <option value="">Select a unit</option>
                  <option v-for="unit in units" :key="unit.id" :value="unit.id">
                    {{ unit.name }} ({{ unit.abbreviation }})
                  </option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="productCategory">Category</label>
              <select id="productCategory" v-model="newProduct.category_id" required>
                <option value="">Select a category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label for="productDescription">Description</label>
              <textarea
                id="productDescription"
                v-model="newProduct.description"
                rows="3"
                placeholder="Describe your product: freshness, origin, packaging, etc."
              ></textarea>
            </div>

            <div class="form-group">
              <label for="productImage">Product Image</label>
              <input
                id="productImage"
                type="file"
                @change="handleImageChange"
                accept="image/*"
                ref="fileInput"
              />
              <div v-if="imagePreview" class="image-preview">
                <img :src="imagePreview" alt="Image Preview" />
                <button type="button" @click="removeImagePreview">Remove</button>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary">
                {{ editMode ? 'Update Product' : 'Add Product' }}
              </button>
              <button v-if="editMode" type="button" @click="cancelEdit" class="btn-secondary">
                Cancel Edit
              </button>
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

        <!-- MESSAGES SECTION -->
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

              <!-- Empty State - No Conversations -->
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
              <!-- Placeholder: No Conversation Selected -->
              <div v-if="!currentConversation" class="select-conversation">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>Select a conversation</h3>
                <p>Choose a conversation to start messaging</p>
              </div>

              <!-- Active Conversation View -->
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
                      <p v-if="currentConversation.isOnline" class="status">Online</p>
                      <p v-else class="status">Last seen {{ formatTime(currentConversation.lastSeen) }}</p>
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
                  style="overflow-y: auto; height: 60vh;"
                >
                  <!-- Loading More Messages -->
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
                      :class="{ 'sent': message.sender_id === userId, 'received': message.sender_id !== userId }"
                    >
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
                      <div v-else class="sent-message">
                        <div class="message-content">
                          <div class="message-bubble sent">
                            <p>{{ message.message }}</p>
                            <div class="message-meta">
                              <span class="timestamp">{{ formatTime(message.created_at) }}</span>
                              <span class="message-status">
                                <svg v-if="message.status === 'pending'" class="status-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M16 8v8l-8-4 8-4z"/>
                                </svg>
                                <svg v-else-if="message.status === 'sent'" class="status-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg v-else-if="message.status === 'delivered'" class="status-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                  <path d="M5 13l4 4L19 7" stroke-width="2"/>
                                </svg>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Initial Loading Spinner -->
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

<!-- Feedback Section - Beautiful Card View -->
<section v-if="section === 'feedback'" class="content-section view-feedback">
  <h2 class="section-title">
    <span>⭐</span> Manage Feedback & Replies
  </h2>

  <!-- Feedback Cards -->
  <div v-if="feedback.length" class="feedback-cards-grid">
    <div v-for="item in feedback" :key="item.id" class="feedback-card">
      <!-- Header: User & Product -->
      <div class="card-header">
        <div class="user-chip">
          <div class="avatar">{{ (item.user?.name || 'U')[0].toUpperCase() }}</div>
          <span>{{ item.user?.name || 'Unknown User' }}</span>
        </div>
        <div class="product-tag">
          {{ item.product?.name || 'Unknown Product' }}
        </div>
      </div>

      <!-- Body -->
      <div class="card-body">
        <!-- Rating -->
        <div class="rating-stars">
          <span v-for="star in 5" :key="star" :class="{ filled: star <= item.rating }">
            ★
          </span>
          <small>Rated {{ item.rating }}/5</small>
        </div>

        <!-- Comment -->
        <blockquote class="comment">
          "{{ item.comment }}"
        </blockquote>

        <!-- Meta Info -->
        <div class="meta-info">
          <time>{{ new Date(item.created_at).toLocaleDateString('en-US', { 
              year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' 
            }) }}</time>
          <div class="status-badge" :class="item.approved ? 'approved' : 'pending'">
            {{ item.approved ? 'Approved' : 'Pending' }}
          </div>
        </div>

        <!-- Reply Section -->
        <div class="reply-section">
          <div v-if="item.reply" class="reply-box">
            <strong>✅ You replied:</strong>
            <p class="reply-text">{{ item.reply }}</p>
          </div>
          <div v-else class="reply-form">
            <textarea
              v-model="replies[item.id]"
              placeholder="Type your thoughtful reply..."
              rows="2"
              maxlength="500"
            ></textarea>
            <button
              @click="sendReply(item.id)"
              :disabled="!replies[item.id] || sendingReply[item.id]"
              class="btn primary"
            >
              {{ sendingReply[item.id] ? '📤 Sending...' : '📤 Reply' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Card Footer / Actions -->
      <div class="card-footer">
        <button
          v-if="!item.approved"
          @click="approveFeedback(item.id)"
          class="btn outline approve"
        >
          ✅ Approve
        </button>
        <button @click="deleteFeedback(item.id)" class="btn danger">
          🗑 Delete
        </button>
      </div>
    </div>
  </div>

  <!-- No Feedback -->
  <div v-else class="empty-state">
    <img src="https://img.icons8.com/ios-filled/100/cccccc/speech-bubble.png" alt="No feedback" class="empty-icon" />
    <p>No feedback found. Come back later!</p>
  </div>

  <!-- Pagination -->
  <div v-if="pagination.total > pagination.per_page" class="pagination-controls">
    <button
      class="btn pagination-btn"
      :disabled="pagination.current_page === 1"
      @click="changePage(pagination.current_page - 1)"
    >
      ← Prev
    </button>
    <span class="page-info">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
    <button
      class="btn pagination-btn"
      :disabled="pagination.current_page === pagination.last_page"
      @click="changePage(pagination.current_page + 1)"
    >
      Next →
    </button>
  </div>
</section>

        <!-- Order Management Section -->
<section v-if="section === 'orders'" class="content-section">
  <h2>📦 Order Management</h2>

  <div v-if="ordersLoading" class="loading">Loading orders...</div>

  <div v-else-if="orders.length === 0" class="empty-state">
    <p>No orders yet. Your products will appear here once a customer places an order.</p>
  </div>

  <div v-else class="orders-list">
    <div v-for="order in orders" :key="order.id" class="order-card">
      <!-- Order Header -->
      <div class="order-header">
        <div class="order-id">Order #{{ order.id }}</div>
        <div class="order-status" :class="order.status || 'unknown'">
          {{ formatStatus(order.status) }}
        </div>
      </div>

      <!-- Customer Info -->
      <div class="order-customer">
        <strong>Customer:</strong> {{ order.customer_name }}<br />
        <strong>Phone:</strong> {{ order.customer_phone || 'Not provided' }}<br />
        <strong>Address:</strong> {{ order.shipping_address }}
      </div>

      <!-- Order Items -->
      <div class="order-items">
        <div v-for="item in order.items" :key="item.id" class="order-item">
          <img
            v-if="item.product?.image"
            :src="`http://127.0.0.1:8000/storage/${item.product.image}`"
            :alt="item.product.name"
            class="item-image"
            @error="useFallbackImage"
          />
          <div v-else class="item-image placeholder">🖼️</div>
          <div class="item-details">
            <h4>{{ item.product?.name || 'Unknown Product' }}</h4>
            <p>
              {{ item.quantity }} × ₺{{ item.price }}
              <span v-if="item.product?.unit"> {{ item.product.unit.abbreviation }}</span>
              = <strong>₺{{ (item.quantity * item.price).toFixed(2) }}</strong>
            </p>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="order-summary">
        <div><strong>Total:</strong> ₺{{ order.total_price }}</div>
        <div><strong>Date:</strong> {{ formatDate(order.created_at) }}</div>
      </div>

      <!-- Farmer Instructions -->
      <div class="farmer-instructions">
        <h4>📋 What You Should Do:</h4>
        <ol>
          <li v-if="order.status === 'pending'">
            <strong>Wait for Payment</strong> – The customer has placed the order. Wait for status to change to <em>paid</em>.
          </li>
          <li v-if="order.status === 'paid'">
            <strong>Prepare the Order</strong> – Pack the items. This order is confirmed and paid.
          </li>
          <li v-if="order.status === 'paid' || order.status === 'shipped'">
            <strong>Delivery:</strong>
            <template v-if="order.delivery_method === 'pickup'">
              Customer will pick up from your farm.
            </template>
            <template v-else>
              Deliver to: {{ order.shipping_address }}
            </template>
          </li>
          <li v-if="order.status === 'shipped'">
            <strong>🚚 Shipped</strong> – Marked as shipped on {{ formatDate(order.shipped_at) }}
          </li>
          <li v-if="order.status === 'delivered'">
            <strong>✅ Delivered</strong> – Customer has received the order.
          </li>
          <li v-if="order.status === 'cancelled'">
            <strong>🚫 Cancelled</strong> – Order was cancelled.
          </li>
          <li v-if="order.status === 'unknown'">
            <strong>❓ Unknown Status</strong> – Contact support.
          </li>
        </ol>
      </div>

      <!-- Action Buttons -->
      <div class="order-actions">
        <button
          v-if="order.status === 'paid'"
          @click="markAsShipped(order.id)"
          class="btn-primary"
        >
          Mark as Shipped
        </button>
        <button
          v-if="order.status === 'shipped'"
          @click="markAsDelivered(order.id)"
          class="btn-success"
        >
          Mark as Delivered
        </button>
        <button @click="printOrder(order)" class="btn-secondary">
          🖨️ Print Order
        </button>
      </div>
    </div>
  </div>
</section>


<!-- Sales History Section -->
<section v-if="section === 'sales'" class="content-section">
  <h2>📦 Sales History</h2>

  <div v-if="salesLoading" class="loading">Loading sales data...</div>

  <div v-else-if="sales.length > 0" class="sales-grid">
    <div v-for="sale in sales" :key="sale.id" class="sale-card">
      <!-- Product Image & Info -->
      <div class="sale-header">
        <img
          v-if="sale.product?.image"
          :src="`http://127.0.0.1:8000/storage/${sale.product.image}`"
          :alt="sale.product.name"
          class="sale-product-image"
          @error="useFallbackImage"
        />
        <div v-else class="sale-product-image placeholder">🖼️</div>

        <div class="sale-details">
          <h3>{{ sale.product?.name || 'Unknown Product' }}</h3>
          <p class="farmer">
            <strong>Farmer:</strong> {{ sale.product?.user?.name || 'You' }}
          </p>
          <p class="category" v-if="sale.product?.category">
            <strong>Category:</strong> {{ sale.product.category.name }}
          </p>
        </div>
      </div>

      <!-- Sales Stats -->
      <div class="sale-stats">
        <div class="stat">
          <span class="label">Price</span>
          <span class="value">₺{{ sale.unit_price }}</span>
        </div>
        <div class="stat">
          <span class="label">Qty</span>
          <span class="value">
            {{ sale.quantity }}
            <span v-if="sale.product?.unit"> {{ sale.product.unit.abbreviation }}</span>
          </span>
        </div>
        <div class="stat">
          <span class="label">Total</span>
          <span class="value">₺{{ sale.total_price }}</span>
        </div>
      </div>

      <!-- Order & Date -->
      <div class="sale-meta">
        <span>Order #{{ sale.order_id }}</span>
        <span>{{ formatDate(sale.created_at) }}</span>
      </div>
    </div>
  </div>

  <div v-else class="empty-state">
    <p>Nothing has been sold yet.</p>
  </div>
</section>

<!-- Inline Confirmation Box -->
<div v-if="confirmBox.visible" class="confirm-overlay">
  <div class="confirm-box">
    <p>{{ confirmBox.message }}</p>
    <div class="actions">
      <button @click="confirmYes" class="btn-primary">Yes</button>
      <button @click="confirmNo" class="btn-secondary">No</button>
    </div>
  </div>
</div>

<!-- Inline Status Message -->
<div v-if="statusMessage.text" :class="['status-message', statusMessage.type]">
  {{ statusMessage.text }}
</div>


      </main>
    </div>
  </div>

  
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import ProfileModal from '@/components/ProfileModal2.vue'

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
  } else if (newSection === 'orders') {
    fetchOrders() // ✅ Refetch when tab is clicked
  }
}
  
// Reactive state
const confirmBox = reactive({
  visible: false,
  message: '',
  action: null, // function to call on confirm
})

const statusMessage = reactive({
  text: '',
  type: '' // success or error
})

// Show confirmation box
const showConfirm = (message, action) => {
  confirmBox.visible = true
  confirmBox.message = message
  confirmBox.action = action
}

// Handle confirm
const confirmYes = () => {
  if (confirmBox.action) confirmBox.action()
  confirmBox.visible = false
}

const confirmNo = () => {
  confirmBox.visible = false
}

// Handle status messages
const showStatus = (text, type = 'success') => {
  statusMessage.text = text
  statusMessage.type = type
  setTimeout(() => {
    statusMessage.text = ''
  }, 3000)
}



const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      showStatus('Authentication token not found. Please log in again.', 'error');
      return;
    }

    const res = await axios.get('/api/user/profile', {
      headers: { Authorization: `Bearer ${token}` },
    });

    const userData = res.data;

    // Ensure avatar_url has a fallback
    userData.avatar_url = userData.avatar_url || '/default-avatar.png';

    // Save to auth store
    authStore.setUser(userData);

    // Set current user ID
    userId.value = userData.id;

    console.log('Fetched user:', userData);
    showStatus('Profile loaded successfully.');
  } catch (error) {
    console.error('Failed to fetch user profile:', error);
    showStatus('Could not load your profile. Please log in again.', 'error');

    localStorage.removeItem('token');
    window.location.href = '/login';
  }
};


// Orders
const orders = ref([])
const ordersLoading = ref(false)

// Fetch Orders - Safe & Verified
const fetchOrders = async () => {
  ordersLoading.value = true;
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      showStatus('Authentication token not found. Please log in again.', 'error');
      return;
    }

    const res = await axios.get('/api/farmer/orders', {
      headers: { Authorization: `Bearer ${token}` }
    });

    // Normalize: handle both [ ] and { [ ] }
    const data = Array.isArray(res.data) ? res.data : res.data.data || [];

    orders.value = data;
    console.log('✅ Orders loaded:', orders.value);
    showStatus('Orders loaded successfully.');
  } catch (error) {
    console.error('❌ Failed to fetch orders:', error);
    orders.value = []; // ✅ Fallback to empty array
    showStatus('Could not load orders. Please try again later.', 'error');
  } finally {
    ordersLoading.value = false;
  }
};

// Helper: Safely format status (handles undefined/null)
const formatStatus = (status) => {
  if (!status) return 'Unknown'
  return status.replace('_', ' ').replace('-', ' ').toUpperCase()
}
const markAsDelivered = async (orderId) => {
  try {
    await axios.post(
      `/api/farmer/orders/${orderId}/status`,
      { status: 'delivered' },
      { headers: getAuthHeaders() }
    )

    showStatus('✅ Order marked as delivered.')
    fetchOrders()
  } catch (error) {
    console.error('Failed to mark as delivered:', error)
    showStatus(error.response?.data?.message || '❌ Failed to update status.', 'error')
  }
}


// ✅ Updated markAsShipped without alert()
const markAsShipped = async (orderId) => {
  if (!confirm('Has the order been shipped?')) return

  try {
    await axios.post(`/api/farmer/orders/${orderId}/status`, {
      status: 'shipped'
    }, {
      headers: getAuthHeaders()
    })

    message.value = 'Order marked as shipped.'
    fetchOrders()

    // auto-clear message after 3 seconds
    setTimeout(() => {
      message.value = ''
    }, 3000)
  } catch (error) {
    console.error('Failed to mark as shipped:', error)
    message.value = 'Failed to mark as shipped.'
    setTimeout(() => {
      message.value = ''
    }, 3000)
  }
}


// Print Order
const printOrder = (order) => {
  const printWindow = window.open('', '_blank')
  printWindow.document.write(`
    <html>
      <head>
        <title>Order #${order.id}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 10px; }
          .item { display: flex; gap: 10px; margin: 10px 0; }
          .item img { width: 50px; height: 50px; object-fit: cover; }
        </style>
      </head>
      <body>
        <div class="header">
          <h2>FASI-MARKET – Order #${order.id}</h2>
          <p>Date: ${formatDate(order.created_at)}</p>
        </div>
        <h3>Customer: ${order.customer_name}</h3>
        <p>Phone: ${order.customer_phone || 'N/A'}</p>
        <p>Address: ${order.shipping_address}</p>
        <h3>Items:</h3>
        <ul>
          ${order.items.map(item => `
            <li class="item">
              <img src="http://127.0.0.1:8000/storage/${item.product.image}" />
              <div>
                <strong>${item.product.name}</strong><br/>
                ${item.quantity} × ₺${item.price} = ₺${(item.quantity * item.price).toFixed(2)}
              </div>
            </li>
          `).join('')}
        </ul>
        <h3>Total: ₺${order.total_price}</h3>
        <p>Status: ${order.status.toUpperCase()}</p>
      </body>
    </html>
  `)
  printWindow.document.close()
  printWindow.print()
}


// Profile modal
const profileModalOpen = ref(false)
const openProfileModal = () => profileModalOpen.value = true
const closeProfileModal = () => profileModalOpen.value = false
const onProfileUpdated = (newUserData) => {
  authStore.setUser(newUserData);
  userId.value = newUserData.id; // Sync ID
  avatarUrl.value = newUserData.avatar_url || '/default-avatar.png'; // ← Add this
};

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

const unitsFetched = ref(false)

const fetchUnits = async () => {
  if (unitsFetched.value) {
    console.log('Units already fetched. Skipping.')
    return
  }

  try {
    const res = await axios.get('/api/units')
    if (Array.isArray(res.data)) {
units.value = res.data
      unitsFetched.value = true
      console.log('Units loaded:', units.value)
    }
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
const userId = ref(null);
const messagesContainer = ref(null);

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
  const conv = currentConversation.value;
  if (!conv || loadingMoreMessages.value) return;

  // ✅ Guard: Check if there are any messages
  if (!conv.messages || conv.messages.length === 0) {
    console.log("No existing messages to paginate.");
    return;
  }

  const oldestMessage = conv.messages[0];
  const oldestMessageDate = oldestMessage?.created_at;

  // ✅ Guard: Ensure we have a valid date to paginate before
  if (!oldestMessageDate) {
    console.log("No valid timestamp for oldest message. Stopping pagination.");
    return;
  }

  try {
    loadingMoreMessages.value = true;

    const token = localStorage.getItem('token');
    if (!token) throw new Error('No auth token');

    const res = await axios.get(`/api/conversations/${conv.id}/messages`, {
      headers: { Authorization: `Bearer ${token}` },
      params: {
        before: oldestMessageDate,
        per_page: 20
      }
    });

    const newMessages = Array.isArray(res.data.data) ? res.data.data : res.data;

    // ✅ Stop if no older messages returned
    if (!newMessages || newMessages.length === 0) {
      console.log("No older messages found. Pagination complete.");
      return;
    }

    // ✅ Append older messages to the top
    currentConversation.value.messages = [...newMessages, ...conv.messages];

    // ✅ Optional: Adjust scroll position to avoid jump
    nextTick(() => {
      const container = messagesContainer.value;
      if (container) {
        // Try to keep scroll position relative to top content
        const scrollDiff = container.scrollHeight - conv.messages.length * 10; // rough estimate
        container.scrollTop = scrollDiff > 0 ? scrollDiff : 0;
      }
    });

  } catch (error) {
    console.error('Error loading older messages:', error);
    // Optionally show a toast or disable further loading
  } finally {
    loadingMoreMessages.value = false;
  }
};

// Add this ref near your other state
const hasReachedOldestMessages = ref(false);

// Updated handleScroll
const handleScroll = (event) => {
  const container = event.target;

  // Reset flag if new messages come in
  if (currentConversation.value?.messages?.length > 0 && hasReachedOldestMessages.value) {
    hasReachedOldestMessages.value = false;
  }

  if (
    container.scrollTop === 0 &&
    !loadingMoreMessages.value &&
    !hasReachedOldestMessages.value
  ) {
    loadMoreMessages();
  }
};


// Send a new message
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
    nextTick(() => scrollToBottom(true));

    // Send to server
    const res = await axios.post(
      `/api/conversations/${currentConversation.value.id}/messages`,
      { message: messageContent },
      { headers: getAuthHeaders() }
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

    // Update last message in conversation list
    const convIndex = conversations.value.findIndex(
      (c) => c.id === currentConversation.value.id
    );
    if (convIndex !== -1) {
      conversations.value[convIndex].last_message = {
        ...newMessageData,
        preview: messageContent.length > 30
          ? messageContent.substring(0, 30) + '...'
          : messageContent,
      };
      searchConversations(); // Refresh search results
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
// Check if the messages container is scrolled to the bottom
const isScrolledToBottom = () => {
  const container = messagesContainer.value;
  if (!container) return true; // If no container, assume "bottom" for safety
  const threshold = 50; // Allow 50px tolerance (helps on mobile)
  return container.scrollHeight - container.scrollTop - container.clientHeight <= threshold;
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
const deleteFeedback = (id) => {
  showConfirm('Are you sure you want to delete this feedback?', async () => {
    try {
      await axios.delete(`/api/reviews/${id}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      })
      showStatus('Feedback deleted.')
      fetchFeedback(pagination.value.current_page)
    } catch (err) {
      console.error('Failed to delete feedback:', err)
      showStatus('Could not delete feedback.', 'error')
    }
  })
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

// === NEW: Inline Notification System (No alerts/confirm) ===
const notification = ref({ show: false, message: '', type: 'info' });
const showNotification = (message, type = 'info') => {
  notification.value = { show: true, message, type };
  setTimeout(() => { notification.value.show = false; }, 5000);
};

// Initialize data
onMounted(async () => {
  try {
    await fetchProfile();         
    await fetchProducts();
    await fetchCategories();
    await fetchUnits();
    await fetchFeedback();
    await fetchSales();
    await fetchOrders() // ✅ Add this

  } catch (err) {
    console.error('Error during initialization:', err);
  }
});

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
  top: 85px;
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
  left: 0;
  right: 0;
  height: 85px;
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
  font-size: 1.9rem;
  color: white;
  font-weight: 600;
}

.greeting p {
  margin: 0rem 0 0 0;
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
/* === Sales History === */
.sales-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.sale-card {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.sale-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  background: rgba(255, 255, 255, 0.12);
}

.sale-header {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  align-items: flex-start;
}

.sale-product-image {
  width: 70px;
  height: 70px;
  border-radius: 12px;
  object-fit: cover;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.5);
  font-size: 1.2rem;
}

.sale-details h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  color: white;
}

.sale-details p {
  margin: 0;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
}

.sale-details p.farmer {
  color: #a8f7c5;
}

.sale-details p.category {
  color: #a0d8f1;
  font-size: 0.85rem;
}

.sale-stats {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 0.5rem;
  margin-bottom: 1rem;
  text-align: center;
}

.stat .label {
  display: block;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0.25rem;
}

.stat .value {
  font-weight: 700;
  color: #10b981;
  font-size: 1rem;
}

.sale-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
  padding-top: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-top: 1rem;
}

.empty-state {
  text-align: center;
  color: rgba(255, 255, 255, 0.6);
  padding: 3rem;
  font-style: italic;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  border: 1px dashed rgba(255, 255, 255, 0.2);
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
.messaging-section {
  display: flex;
  height: 100vh;
  background: rgba(30, 41, 59, 1); /* ✅ changed background */
  justify-content: center;
  align-items: center;
  padding: 0; /* removed extra padding to fill screen */
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin: 0;
}

.messaging-container {
  display: flex;
  width: 100%; /* ✅ full width */
  height: 680px; /* Fixed height (or use 80vh) */
  background-color:  rgba(30, 41, 59, 0.95);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  border-radius: 0; /* removed rounding for full-screen look */
  overflow: hidden;
  position:fixed;
}

/* Left sidebar */
.conversation-list {
  width: 350px;
    padding: 16px;
  border-bottom: rgba(30, 41, 59, 0.95);
  border-right: 1.5px solid #e6e9f0;
  display: flex;
  flex-direction: column;
  background-color:  rgba(30, 41, 59, 0.95);
}

.conversation-header {
  padding: 16px;
  border-bottom: rgba(30, 41, 59, 0.95);
  font-weight: 600;
  font-size: 18px;
  color: #f2f3f5ff;
  background-color:  rgba(30, 41, 59, 0.95);
}

.search-conversations {
  padding: 12px 16px;
  background-color: rgba(30, 41, 59, 0.95);
}

.search-conversations input {
  width: 100%;
  padding: 10px 12px;
  font-size: 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background-color: #f8fafc;
  transition: all 0.3s;
}

.search-conversations input:focus {
  outline: none;
  border-color: #a0aec0;
  background-color: white;
  box-shadow: 0 0 0 3px rgba(118, 169, 250, 0.2);
}


.conversations-list {
  overflow-y: auto;
  flex-grow: 1;
  margin: 0;
  padding: 0;
  list-style: none;
}

.conversation-item {
  display: flex;
  padding: 14px 16px;
  cursor: pointer;
  border-left: 4px solid transparent;
  transition: all 0.3s ease;
  align-items: center;
  background-color:  rgba(30, 41, 59, 0.95);
  margin: 4px 8px;
  border-radius: 8px;
}

.conversation-item.active {
  background: linear-gradient(90deg, #ebf4ff 0%, #e3eff9 100%);
  color: #2d3748;
  border-left-color: #4299e1;
}

.conversation-item:hover {
  background-color: #f0f5ff;
}

.avatar-wrapper {
  position: relative;
  margin-right: 12px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e2e8f0;
}

.unread-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: #48bb78;
  color: white;
  font-weight: 600;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Conversation details */
.conversation-details {
  flex: 1;
  font-size: 14px;
}

.conversation-header-row {
  display: flex;
  justify-content: space-between;
  font-weight: 600;
  margin-bottom: 4px;
  color: #2d3748;
}

.timestamp {
  color: #a0aec0;
  font-size: 12px;
}

.last-message {
  color: #718096;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Chat area */
.chat-area {
  flex-grow: 1;
    padding: 16px;

  display: flex;
  flex-direction: column;
  background: rgba(30, 41, 59, 0.95);
  
}

.chat-header {
  display: flex;
  justify-content: space-between;
  padding: 16px 24px;
  border-bottom: 1px solid #e6e9f0;
  align-items: center;
  background-color: rgba(30, 41, 59, 0.95);;
}

.header-left {
  display: flex;
  align-items: center;
}

.header-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 12px;
  object-fit: cover;
  border: 2px solid #e2e8f0;
}

.header-info h4 {
  margin: 0;
  font-weight: 700;
  color: #eff3faff;
}

.status {
  font-size: 13px;
  color: #48bb78;
  margin-top: 2px;
  display: flex;
  align-items: center;
}

.status::before {
  content: "";
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #48bb78;
  margin-right: 6px;
}

.header-actions button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  margin-left: 8px;
  border-radius: 8px;
  transition: all 0.3s;
  color: #718096;
}

.header-actions button:hover {
  background-color: rgba(30, 41, 59, 0.95);
  color: #2d3748;
}


/* Each message wrapper */
.message-wrapper {
  display: flex;
  width: 100%;
  height:100;
}

/* Sent message (align right) */
.message-wrapper.sent {
  justify-content: flex-end; /* pushes content to the right */
  
}
.message-wrapper.sent .message-bubble {
  background-color: #4f46e5;
  color: white;
  padding: -3rem 0.8rem;        /* smaller padding */
  max-width: 100%;
  word-wrap: break-word;
}


/* Received message (align left) */
.message-wrapper.received {
  justify-content: flex-start; /* pushes content to the left */
}

.message-wrapper.received .message-bubble {
  background-color: #bbc1cbff; /* received bubble color */
  color: #0c0d10ff;
  border-radius: 1rem 1rem 1rem 0.25rem;
}

/* Optional: spacing between avatar and bubble */
.received-message .sender-avatar {
  margin-right: 0.7rem;
}


/* Message groups */
.message-group {
  margin-bottom: 20px;
  max-width: 70%;
}

.message-group.current-user {
  margin-left: auto;
  max-width: 70%;
  text-align: right;
}

.message-sender {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.sender-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 8px;
  object-fit: cover;
  border: 2px solid #e2e8f0;
}

.sender-name {
  font-weight: 600;
  font-size: 14px;
  color: #115bdbff;
}

.message-bubble {
  background-color: white;
  padding: 12px 16px;
  border-radius: 18px;
  font-size: 15px;
  line-height: 1.4;
  position: relative;
  display: inline-block;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  color: #2d3748;
  border: 1px solid #e2e8f0;
}

.message-bubble.my-message {
  background: linear-gradient(90deg, #4299e1 0%, #3182ce 100%);
  color: white;
  border: none;
}

.message-content p {
  margin: 0;
}

.message-meta {
  margin-top: 8px;
  display: flex;
  justify-content: flex-end;
  font-size: 11px;
  color: #a0aec0;
  align-items: center;
}

.message-meta.my-message {
  color: rgba(255, 255, 255, 0.7);
}

.timestamp {
  margin-left: 6px;
}

/* Input area */
.message-input {
  display: flex;
  align-items: center;
  border-top: 1px solid #e6e9f0;
  padding: 12px 24px;
  background: rgba(30, 41, 59, 0.95);;
}

.input-actions button {
  background: rgba(30, 41, 59, 0.95);;
  border: none;
  margin-right: 12px;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  color: #718096;
  transition: all 0.3s;
}

.input-actions button:hover {
  background-color: #edf2f7;
  color: #2d3748;
}

.input-wrapper {
  flex-grow: 1;
}

.message-input input {
  width: 100%;
  padding: 12px 16px;
  font-size: 15px;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  background-color: rgba(30, 41, 59, 0.95);
  transition: all 0.3s;
}

.message-input input:focus {
  outline: none;
  border-color: #a0aec0;
  background-color: rgba(30, 41, 59, 0.95);;
  box-shadow: 0 0 0 3px rgba(118, 169, 250, 0.2);
}

.send-button {
  background:  #f6f7f8ff ;
  border: none;
  cursor: pointer;
  margin-left: 12px;
  padding: 10px;
  border-radius: 50%;
  transition: all 0.3s;
  color: blue;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 5px rgba(16, 16, 16, 0.96);
}

.send-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(66, 153, 225, 0.3);
}

.send-button:disabled {
  cursor: not-allowed;
  background: #a0aec0;
  box-shadow: none;
  transform: none;
}

/* Scrollbar styling */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
.send-button:disabled {
  cursor: not-allowed;
  color: #ccc;
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
/* === Form Improvements === */
.form {
  background: rgba(255, 255, 255, 0.05);
  padding: 2rem;
  border-radius: 15px;
  margin-bottom: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  color: rgba(11, 11, 11, 0.9);
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.95rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  background: rgba(255, 255, 255, 1);
  color: rgba(14, 14, 14, 1);
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

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.image-preview {
  margin-top: 1rem;
}

.image-preview img {
  max-width: 200px;
  max-height: 200px;
  border-radius: 12px;
  object-fit: cover;
}

.image-preview button {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}
/* === Orders Section === */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.order-card {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  font-weight: 600;
}

.order-id {
  color: white;
  font-size: 1.1rem;
}

.order-status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.order-status.pending { background: #f59e0b; color: white; }
.order-status.paid { background: #10b981; color: white; }
.order-status.shipped { background: #0ea5e9; color: white; }
.order-status.delivered { background: #16a34a; color: white; }
.order-status.cancelled { background: #ef4444; color: white; }

.order-customer {
  background: rgba(255, 255, 255, 0.05);
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1rem;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.order-item {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.item-image {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  object-fit: cover;
}

.item-details h4 {
  margin: 0 0 0.25rem 0;
  color: white;
  font-size: 1rem;
}

.item-details p {
  margin: 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.order-summary {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 12px;
  font-size: 0.95rem;
  color: white;
}

.farmer-instructions {
  background: rgba(255, 255, 255, 0.05);
  padding: 1rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
}

.farmer-instructions h4 {
  margin: 0 0 0.75rem 0;
  color: #10b981;
}

.farmer-instructions ol {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
}

.order-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-success {
  background: linear-gradient(135deg, #16a34a, #15803d);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-success:hover {
  background: #145f2f;
}

/* Section Title */
.section-title {
  font-size: 1.75rem;
  color: #2c3e50;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
}

.section-title span {
  font-size: 1.5em;
}

/* Grid Layout */
.feedback-cards-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  margin-top: 16px;
}

/* Feedback Card */
.feedback-card {
  background: rgba(46, 50, 77, 0.76);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #eaeaea;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.feedback-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

/* Card Header */
.card-header {
  padding: 16px 20px;
  background: #3a3f5dff;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.user-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #f6f6f7ff;
  font-size: 14px;
}

.avatar {
  width: 32px;
  height: 32px;
  background: #3498db;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: bold;
}

.product-tag {
  background: #f7f6f6ff;
  color: #303c6aff;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

/* Card Body */
.card-body {
  padding: 20px;
  color: #444;
  line-height: 1.6;
}

/* Rating */
.rating-stars {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 18px;
  margin-bottom: 10px;
}

.rating-stars .filled {
  color: #ffc107;
}

.rating-stars small {
  color: #c3d013ff;
  margin-left: 8px;
  font-size: 12px;
}

/* Comment */
.comment {
  font-style: italic;
  color: #0c0c0cff;
  margin: 12px 0;
  padding: 14px;
  background: #f1f5f9;
  border-left: 3px solid #3498db;
  border-radius: 6px;
  font-size: 14px;
  line-height: 1.5;
}

/* Meta Info */
.meta-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #fefbfbff;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 8px;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.approved {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-badge.pending {
  background: #fff8e1;
  color: #9c6b00;
}

/* Reply Section */
.reply-section {
  margin: 16px 0;
}

.reply-box {
  background: #e8f5e8;
  border: 1px solid #c8e6c9;
  padding: 12px;
  border-radius: 10px;
  font-size: 14px;
  color: #10abdeff;
}

.reply-text {
  margin: 6px 0 0;
  font-style: italic;
}

.reply-form textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  resize: vertical;
  font-family: inherit;
  font-size: 14px;
  margin-bottom: 8px;
  background: #fafafa;
}

.reply-form textarea:focus {
  outline: none;
  border-color: #3498db;
  background: white;
}

/* Buttons */
.btn {
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn.primary {
  background: #3498db;
  color: white;
}

.btn.primary:hover:not(:disabled) {
  background: #2980b9;
}

.btn.outline.approve {
  background: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #a5d6a7;
}

.btn.outline.approve:hover {
  background: #c8e6c9;
}

.btn.danger {
  background: #e53935;
  color: white;
}

.btn.danger:hover {
  background: #c62828;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Card Footer */
.card-footer {
  padding: 0 20px 20px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #aaa;
  font-size: 16px;
}

.empty-icon {
  opacity: 0.3;
  margin-bottom: 12px;
  max-width: 80px;
}

/* Pagination */
.pagination-controls {
  margin-top: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  font-size: 14px;
  color: #555;
}

.pagination-controls .page-info {
  font-weight: 500;
}

.pagination-controls .btn.pagination-btn {
  background: #f1f1f1;
  color: #333;
}

.pagination-controls .btn.pagination-btn:hover:not(:disabled) {
  background: #e0e0e0;
}

.confirm-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}
.confirm-box {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  max-width: 400px;
  width: 90%;
}
.confirm-box .actions {
  margin-top: 15px;
  display: flex;
  justify-content: center;
  gap: 10px;
}
.status-message {
  position: fixed;
  bottom: 20px; right: 20px;
  padding: 10px 15px;
  border-radius: 6px;
  font-weight: bold;
  z-index: 2001;
}
.status-message.success { background: #d4edda; color: #155724; }
.status-message.error { background: #f8d7da; color: #721c24; }

</style>