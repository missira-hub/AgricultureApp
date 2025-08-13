<template>  
  <div class="consumer-app">
<header class="header">
  <div class="dashboard-header">
    <!-- Centered Greeting (now properly centered) -->
    <div class="greeting-center">
      <h2>👋 Hello, {{ firstName || 'User' }}</h2>
      <p>Welcome back to your dashboard</p>
    </div>

    <!-- Avatar with Settings Toggle (unchanged) -->
    <div class="settings-wrapper" @click.stop="toggleSettings">
      <img
        v-if="profilePictureUrl"
        :src="profilePictureUrl"
        alt="Profile"
        class="profile-avatar"
      />
      <div v-else class="profile-avatar placeholder">{{ userInitial }}</div>
      
      <!-- Settings Dropdown -->
      <div v-if="settingsOpen" class="settings-menu">
        <ul>
          <li @click.stop="openProfile">Profile</li>
          <li @click.stop="handleLogout">Logout</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Profile Modal -->
  <ProfileModal
    v-if="profileModalOpen"
    @close="closeProfile"
    @updated="onProfileUpdated"
  />
</header>
  
    <!-- Error Alert -->
    <div v-if="globalError" class="error-alert">
      <svg class="error-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
      <span>{{ globalError }}</span>
      <button @click="globalError = null" class="close-error">×</button>
    </div>

    <!-- Tab Navigation -->
    <nav class="bottom-nav">


      <div @click="switchSection('market')" :class="{ active: section === 'market' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <span>Market</span>
      </div>
       
      <div @click="switchSection('cart')" :class="{ active: section === 'cart' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8L5 3H3m4 10v6a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-6"></path>
        </svg>
        <span>Cart</span>
      </div>
      <div @click="switchSection('orders')" :class="{ active: section === 'orders' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
          <path d="M3.3 7l8.7 5 8.7-5"></path>
          <path d="M12 22V12"></path>
        </svg>
        <span>Orders</span>
      </div>
      <div @click="switchSection('messages')" :class="{ active: section === 'messages' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span>Messages</span>
      </div>
      <div @click="switchSection('feedback')" :class="{ active: section === 'feedback' }" class="nav-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
        </svg>
        <span>Feedback</span>
      </div>
      <div @click="switchSection('payment')" :class="{ active: section === 'payment' }" class="nav-item">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M12 1v22M17 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2z"></path>
  </svg>
  <span>Payment</span>
</div>

    </nav>
<main class="main-view">
  <!-- Loading Spinner -->
  <div v-if="loading" class="loading-spinner">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>
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
            <!-- Avatar -->
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

            <!-- Unread Badge -->
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
              :class="{ 'sent': message.sender_id === userId, 'received': message.sender_id !== userId }"
            >
              <!-- RECEIVED MESSAGE (from other person) -->
              <div v-if="message.sender_id !== userId" class="received-message">
                <!-- Show avatar only if this is the first message or sender changed -->
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

              <!-- SENT MESSAGE (by you) -->
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

  <section v-if="section === 'market' && !loading" class="card-section">
 <!-- SEARCH BAR -->
<div class="search-bar">
  <div class="search-input-wrapper">
    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"></circle>
      <path d="m21 21-4.35-4.35"></path>
    </svg>
    <input
      type="text"
      v-model="query"
      @input="searchProducts"
      placeholder="Search products..."
      autocomplete="off"
    />
  </div>
</div>

  <div class="section-header">
<!-- FILTER BUTTONS -->
<div class="filter-controls">
  <button
    class="filter-btn"
    :class="{ active: activeFilter === 'all' }"
    @click="setFilter('all')"
  >
    All
  </button>

  <button
    v-for="category in categories"
    :key="category.id"
    class="filter-btn"
    :class="{ active: activeFilter === category.slug }"
    @click="setFilter(category.slug)"
  >
    {{ category.name }}
  </button>
</div>

<!-- NO PRODUCTS MESSAGE -->
<div v-if="filteredProducts.length === 0" class="no-products">
  No products found in this category.
</div>

<!-- PRODUCT GRID -->
<div v-else class="product-grid">
  <div
    v-for="product in filteredProducts"
    :key="product.id"
    class="product-card"
  >
    <img
      :src="product.image_url || 'default-image.jpg'"
      :alt="product.name"
      class="product-image"
    />
<h3>{{ product.name }}</h3>
<p>{{ product.description }}</p>

<!-- Price per unit -->
<p>
  <strong>₺{{ product.price }}</strong>
  <span v-if="product.unit">/ {{ product.unit.abbreviation }}</span>
</p>

<!-- Quantity with unit -->
<p>
  <strong>Stock:</strong> {{ product.quantity }}
  <span v-if="product.unit"> {{ product.unit.abbreviation }}</span>
</p>

<!-- Farmer info -->
<p v-if="product.user">
  <strong>Farmer:</strong> {{ product.user.name }}
</p>
<!-- Review Toggle Button -->
<button class="review-btn" @click="toggleReview(product.id)">
  {{ reviewing[product.id] ? 'Cancel Review' : 'Leave a Review' }}
</button>



<!-- Review Form -->
<div v-if="reviewing[product.id]" class="review-box">
  <!-- Stars -->
  <div class="star-rating">
    <span
      v-for="n in 5"
      :key="n"
      class="star"
      :class="{ active: ratings[product.id] >= n }"
      @click="setRating(product.id, n)"
    >★</span>
  </div>

  <!-- Comment + Submit -->
  <div class="comment-box">
    <input
      type="text"
      placeholder="Leave a comment..."
      v-model="comments[product.id]"
    />
    <button @click="submitReview(product.id)">Submit</button>
  </div>
</div>
<!-- Quantity + Unit + Add to Cart -->
<div class="quantity-cart">
  <input
    type="number"
    v-model.number="quantities[product.id]"
    :min="1"
    :max="product.quantity"
    placeholder="Qty"
  />

  <!-- Dropdown for unit -->
<select v-model="selectedUnits[product.id]">
  <option
    v-for="unit in availableUnits"
    :key="unit.id"
    :value="unit.abbreviation"
  >
    {{ unit.name }} ({{ unit.abbreviation }})
  </option>
</select>


  <button @click="addToCart(product)">Add to Cart</button>
</div>
  </div>


  <!-- Empty State -->
  <div v-if="!products.length" class="empty-state">
    <p>No products available.</p>
  </div>
</div>
  </div>
</section>

<section v-if="section === 'cart' && !loading" class="cart-page">
  <!-- Header -->
  <div class="cart-header">
    <div class="header-content">
      <h1 class="cart-title">Shopping Cart</h1>
      <span v-if="cart.length > 0" class="items-count">{{ cart.length }} {{ cart.length === 1 ? 'item' : 'items' }}</span>
    </div>
    <button v-if="cart.length > 0" @click="clearCart" class="clear-cart-btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 6h18l-2 13H5L3 6z"/>
        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        <line x1="10" y1="11" x2="10" y2="17"/>
        <line x1="14" y1="11" x2="14" y2="17"/>
      </svg>
      Clear Cart
    </button>
  </div>

  <!-- Empty State -->
  <div v-if="cart.length === 0" class="empty-state">
    <div class="empty-content">
      <div class="empty-icon">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <circle cx="9" cy="21" r="1"/>
          <circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
      </div>
      <h2>Your cart is empty</h2>
      <p>Browse our categories and discover our best deals!</p>
      <button @click="switchSection('market')" class="continue-shopping-btn">
        Start Shopping
      </button>
    </div>
  </div>

  <!-- Cart Content -->
  <div v-else class="cart-layout">
    <!-- Items List -->
    <div class="cart-items">
      <div class="items-header">
        <h3>Items in your cart</h3>
      </div>
      
      <div class="items-list">
        <div v-for="item in cartWithUnits" :key="item.product_id" class="cart-item">
          <div class="item-image">
            <img :src="item.image_url || getImageUrl(item.image)" :alt="item.name" />
          </div>
          
          <div class="item-details">
            <h4 class="item-name">{{ item.name }}</h4>
            <p class="item-description">{{ item.description }}</p>
            
            <div class="item-meta">
              <span class="item-price">₺{{ item.unit_price }}</span>
              <span v-if="item.unit" class="item-unit">per {{ item.unit.abbreviation || item.unit }}</span>
              <span class="item-availability">In Stock</span>
            </div>
            
            <div class="item-actions">
              <div class="quantity-selector">
                <label>Qty:</label>
                <div class="quantity-controls">
                  <button 
                    @click="updateCartQuantity(item.product_id, item.quantity-1)"
                    :disabled="item.quantity <= 1"
                    class="qty-btn"
                  >
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                  </button>
                  
                  <input
                    type="number"
                    :value="item.quantity"
                    @change="updateCartQuantity(item.product_id, parseInt($event.target.value))"
                    min="1"
                    class="qty-input"
                  />
                  
                  <button 
                    @click="updateCartQuantity(item.product_id, item.quantity+1)"
                    class="qty-btn"
                  >
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="12" y1="5" x2="12" y2="19"/>
                      <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                  </button>
                </div>
                <span v-if="item.unit" class="unit-label">{{ item.unit.abbreviation || item.unit }}</span>
              </div>
              
              <button @click="removeFromCart(item.product_id)" class="remove-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3,6 5,6 21,6"/>
                  <path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
                </svg>
                Remove
              </button>
            </div>
          </div>
          
          <div class="item-total">
            <span class="total-price">₺{{ (item.unit_price * item.quantity).toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Summary Sidebar -->
    <div class="order-summary">
      <div class="summary-card">
        <h3 class="summary-title">Order Summary</h3>
        
        <div class="summary-details">
          <div class="summary-row">
            <span>Items ({{ cart.length }}):</span>
            <span>₺{{ totalCartValue }}</span>
          </div>
          <div class="summary-row">
            <span>Shipping & handling:</span>
            <span>₺0.00</span>
          </div>
          <div class="summary-row">
            <span>Total before tax:</span>
            <span>₺{{ totalCartValue }}</span>
          </div>
          <div class="summary-row">
            <span>Estimated tax:</span>
            <span>₺0.00</span>
          </div>
        </div>
        
        <div class="summary-total">
          <div class="total-row">
            <span>Order total:</span>
            <span class="total-amount">₺{{ totalCartValue }}</span>
          </div>
        </div>
        
        <div class="summary-actions">
          <button class="checkout-btn" @click="checkout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
              <line x1="8" y1="21" x2="16" y2="21"/>
              <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            Proceed to Checkout
          </button>
          
          <button class="continue-btn" @click="switchSection('market')">
            Continue Shopping
          </button>
        </div>
        
        <div class="trust-badges">
          <div class="badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Secure checkout</span>
          </div>
          <div class="badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            <span>Fast delivery</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- Orders Section -->
<section v-if="section === 'orders' && !loading" class="card-section">
  <h2>Your Orders</h2>

  <div v-if="orders.length" class="orders-grid">
    <div class="card order-card" v-for="order in orders" :key="order.id">
      <div class="order-header">
        <p><strong>Order #{{ order.id }}</strong></p>
        <span class="status-badge" :class="order.status.toLowerCase()">
          {{ order.status }}
        </span>
      </div>

      <p class="order-date">{{ formatDate(order.created_at) }}</p>
      <p class="order-total"><strong>Total:</strong> {{ order.total }} ₺</p>

      <ul v-if="order.items && order.items.length" class="order-items">
        <li v-for="(item, index) in order.items" :key="index">
          {{ item.product.name }} – {{ item.quantity }} x {{ item.price }} ₺
        </li>
      </ul>

      <div v-if="order.status === 'pending'" class="order-status-msg pending">
        🛒 Pending Payment
        <button @click="payOrder(order)" class="pay-now-btn">Pay Now</button>
      </div>

      <div v-else-if="order.status === 'paid'" class="order-status-msg waiting">
        ⏳ Waiting for delivery...
      </div>

      <div v-else-if="order.status === 'delivered'" class="order-status-msg delivered">
        ✅ Delivered
      </div>

      <button @click="deleteOrder(order.id)" class="delete-order-btn">
        Delete Order
      </button>
    </div>
  </div>

  <div v-else class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
    </svg>
    <p>No orders found.</p>
  </div>
</section>
<section v-if="section === 'feedback' && !loading" class="feedback-section">
  <h2 class="section-title">Feedback</h2>

  <div v-if="submittedReviews.length === 0" class="no-feedback">
    No feedback yet.
  </div>

  <div v-else class="feedback-grid">
    <div v-for="(review, i) in submittedReviews" :key="i" class="feedback-card">
      <h3 class="product-name">{{ review.product }}</h3>

      <div v-if="!editing[i]">
        <!-- Static stars -->
        <div class="stars">
          <span
            v-for="n in 5"
            :key="n"
            class="star"
            :class="{ active: review.rating >= n }"
          >★</span>
        </div>

        <p>{{ review.comment }}</p>
        <small class="timestamp">{{ new Date(review.timestamp).toLocaleString() }}</small>

        <!-- Farmer reply -->
        <div v-if="review.reply" class="farmer-reply">
          <strong>Farmer's reply:</strong>
          <p>{{ review.reply }}</p>
        </div>

        <!-- Edit button -->
        <button @click="startEdit(i)">Edit Review</button>
      </div>

      <div v-else>
        <!-- Editable stars -->
        <div class="stars">
          <span
            v-for="n in 5"
            :key="n"
            class="star"
            :class="{ active: editedRatings[i] >= n }"
            @click="setEditedRating(i, n)"
            style="cursor: pointer;"
          >★</span>
        </div>

        <!-- Editable comment -->
        <textarea v-model="editedComments[i]" rows="4" cols="40"></textarea>

        <!-- Save / Cancel buttons -->
        <button @click="saveEdit(i, review.id)">Save</button>
        <button @click="cancelEdit(i)">Cancel</button>
      </div>
    </div>
  </div>
</section>



    </main>
  </div>

  <section v-if="section === 'payment'" class="payment-section">
    <h2>Payment</h2>
    <div class="payment-container">
      <div v-if="cart.length === 0" class="empty-cart">
        <p>Your cart is empty</p>
        <button @click="switchSection('market')">Continue Shopping</button>
      </div>
      <div v-else>
        <div class="order-summary">
          <h3>Order Summary</h3>
          <div class="summary-items">
            <div v-for="item in cart" :key="item.product_id" class="summary-item">
              <span>{{ item.name }} ({{ item.quantity }})</span>
              <span>₺{{ (item.unit_price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>
          <div class="summary-total">
            <span>Total:</span>
            <span>₺{{ totalCartValue }}</span>
          </div>
        </div>

        <div class="payment-methods">
          <h3>Payment Method</h3>
          <button @click="payWithStripe" class="stripe-pay-button">
            Pay with Credit Card
          </button>
          <div v-if="paymentError" class="payment-error">
            {{ paymentError }}
          </div>
          <div v-if="paymentProcessing" class="payment-processing">
            Processing payment...
          </div>
        </div>
      </div>
    </div>
  </section>
  
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted , nextTick, watch  } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import avatar from '@/assets/avatar.png'
import ProfileModal from '@/components/ProfileModal.vue'
import { loadStripe } from '@stripe/stripe-js';


// Axios configuration
axios.defaults.baseURL = 'http://127.0.0.1:8000'
const router = useRouter()
const counter = ref(0)


const section = ref("market");      

const switchSection = async (target) => {
  section.value = target;            
  globalError.value = null;

  if (target === "market") await fetchProducts();
  if (target === "cart") await fetchCart();
  if (target === "orders") await fetchOrders();
  if (target === "payment") await fetchCartTotal();

 if (target === 'messages') {
    loading.value = true
    await loadConversations()
    loading.value = false
  }
};


watch(counter, (newVal, oldVal) => {
  console.log(`Counter changed from ${oldVal} to ${newVal}`)
})

// --- State (keep your existing state) ---
const conversations = ref([])
const currentConversation = ref(null)
const newMessage = ref('')
const conversationSearchQuery = ref('')
const loadingConversations = ref(false)
const loadingMessages = ref(false)

// Assume you have userId from auth (replace with real value if available)
const userId = ref(1) // Set dynamically from auth later

// ✅ ADD: Ref for messages container to control scrolling
const messagesContainer = ref(null)

// --- Computed Properties (keep existing) ---
const filteredConversations = computed(() => {
  const query = conversationSearchQuery.value.trim().toLowerCase()
  if (!query) return conversations.value

  return conversations.value.filter((conv) => {
    const title = getConversationTitle(conv).toLowerCase()
    return title.includes(query)
  })
})

const hasNoMessages = computed(() => {
  const conv = currentConversation.value;
  if (!conv) return true;
  const messages = conv.messages;
  return !messages || !Array.isArray(messages) || messages.length === 0;
});

// --- Helper Methods (keep existing) ---
const showSenderInfo = (message) => {
  return message.sender_id !== userId.value;
};

const getMessageSenderName = (message) => {
  return message.sender_name || 'Unknown User';
};

// ✅ ADD: Scrolling utility functions
const scrollToBottom = (smooth = true) => {
  nextTick(() => {
    const container = messagesContainer.value || document.querySelector('.messages-container');
    if (container) {
      if (smooth) {
        container.scrollTo({
          top: container.scrollHeight,
          behavior: 'smooth'
        });
      } else {
        container.scrollTop = container.scrollHeight;
      }
    }
  });
};

const isScrolledToBottom = () => {
  const container = messagesContainer.value || document.querySelector('.messages-container');
  if (!container) return true;
  
  const threshold = 50; // pixels from bottom
  return container.scrollHeight - container.scrollTop - container.clientHeight <= threshold;
};

// ✅ UPDATED: selectConversation with proper scrolling
const selectConversation = async (conversation) => {
  console.log('Selecting conversation:', conversation);
  currentConversation.value = { ...conversation, messages: [] };
  loadingMessages.value = true;

  try {
    const token = localStorage.getItem('token');
    if (!token) throw new Error('No auth token');

    const res = await axios.get(`/api/conversations/${conversation.id}/messages`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    console.log('Fetched messages:', res.data);

    currentConversation.value.messages = Array.isArray(res.data) ? res.data : [];
  } catch (err) {
    console.error('Error loading messages:', err);
    currentConversation.value.messages = [];
  } finally {
    loadingMessages.value = false;
    
    // ✅ IMPROVED: Scroll to bottom after messages load
    await nextTick();
    scrollToBottom(false); // No smooth scroll on initial load for better UX
  }
};

// --- Load Conversations (keep existing) ---
const loadConversations = async () => {
  loadingConversations.value = true
  try {
    const token = localStorage.getItem('token')
    if (!token) throw new Error('No auth token')

    const res = await axios.get('/api/conversations', {
      headers: { Authorization: `Bearer ${token}` }
    })

    console.log('Conversations API Response:', res.data)

    conversations.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error('Error loading conversations:', err)
    console.error('Error response:', err.response?.data)
    conversations.value = []
  } finally {
    loadingConversations.value = false
  }
}

// ✅ UPDATED: sendMessage with improved scrolling logic
const sendMessage = async () => {
  if (!newMessage.value.trim() || !currentConversation.value) return

  const messageText = newMessage.value.trim()
  const tempId = Date.now()
  
  // Check if user was at bottom before sending
  const wasAtBottom = isScrolledToBottom();

  // ✅ Get token
  const token = localStorage.getItem('token')
  if (!token) {
    console.error('No auth token found')
    return
  }

  const tempMessage = {
    id: tempId,
    message: messageText,
    sender_id: userId.value,
    sender_name: 'You', // Add sender name for consistency
    created_at: new Date().toISOString(),
    status: 'sending' // Add status for better UX
  }

  // ✅ Optimistic update
  currentConversation.value.messages.push(tempMessage)
  newMessage.value = ''

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


  try {
    const res = await axios.post(
      `/api/conversations/${currentConversation.value.id}/send`,
      { message: messageText },
      { headers: { Authorization: `Bearer ${token}` } }
    )

    // ✅ Replace temp message with real one
    const idx = currentConversation.value.messages.findIndex(m => m.id === tempId)
    if (idx !== -1) {
      // Preserve the position and update with server response
      currentConversation.value.messages[idx] = {
        ...res.data,
        status: 'sent'
      };
    }

    // ✅ Refresh conversation list (to update last message)
    await loadConversations()
    
    // ✅ Ensure we stay scrolled if we were at bottom
    if (wasAtBottom) {
      scrollToBottom(true);
    }
    
  } catch (err) {
    console.error('Failed to send message:', err)
    
    // ✅ IMPROVED: Mark message as failed with retry option
    const idx = currentConversation.value.messages.findIndex(m => m.id === tempId)
    if (idx !== -1) {
      currentConversation.value.messages[idx].status = 'failed'
    }
  }
}

// ✅ ADD: Mark conversation as read when focused
const markConversationAsRead = () => {
  if (!currentConversation.value) return;
  
  // You can implement API call to mark as read here
  // Example:
  // const token = localStorage.getItem('token');
  // axios.patch(`/api/conversations/${currentConversation.value.id}/read`, {}, {
  //   headers: { Authorization: `Bearer ${token}` }
  // }).catch(err => console.error('Failed to mark as read:', err));
  
  // Update local state
  if (currentConversation.value.unread_count > 0) {
    currentConversation.value.unread_count = 0;
  }
};

// ✅ ADD: Handle received messages (for real-time updates)
const handleNewMessage = (message) => {
  if (!currentConversation.value || message.conversation_id !== currentConversation.value.id) {
    // Update conversation list for other conversations
    loadConversations();
    return;
  }

  // Check if user was at bottom before new message
  const wasAtBottom = isScrolledToBottom();
  
  // Add message to current conversation
  currentConversation.value.messages.push(message);
  
  // Only auto-scroll if user was already at bottom (good UX practice)
  if (wasAtBottom) {
    scrollToBottom(true);
  }
};

// ✅ ADD: Auto-scroll on window resize to maintain position
const handleResize = () => {
  // Debounce resize events
  clearTimeout(handleResize.timeoutId);
  handleResize.timeoutId = setTimeout(() => {
    if (isScrolledToBottom()) {
      scrollToBottom(false);
    }
  }, 100);
};


// --- Helper Functions (keep existing) ---
const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.round(diffMs / 60000)
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m`
  const diffHours = Math.round(diffMs / 3600000)
  if (diffHours < 24) return `${diffHours}h`
  const diffDays = Math.round(diffMs / 86400000)
  if (diffDays < 7) return `${diffDays}d`
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}

const getConversationTitle = (conversation) => {
  if (conversation.with_farmer) return conversation.with_farmer.name
  if (conversation.participants && conversation.participants.length > 0) {
    return conversation.participants
      .filter(p => p.id !== userId.value)
      .map(p => p.name)
      .join(', ')
  }
  return `Conversation #${conversation.id}`
}

const getLastMessagePreview = (conversation) => {
  const lastMsg = conversation.last_message
  if (!lastMsg || !lastMsg.message) return 'No messages yet'
  const text = lastMsg.message
  return text.length > 50 ? text.substring(0, 50) + '...' : text
}

// ✅ UPDATED: Mount Hook with event listeners
onMounted(() => {
  if (section.value === 'messages') {
    loadConversations()
  }
  
  // Add resize listener for better scrolling behavior
  window.addEventListener('resize', handleResize);
})

// ✅ ADD: Cleanup on unmount
onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
  clearTimeout(handleResize.timeoutId);
})

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



const paymentError = ref(null);
const paymentProcessing = ref(false); // ✅ Define it here

const stripePromise = loadStripe('pk_test_51RpdA0H7s9nJbI2dsiOJmgCJkE0Z6TgQhv7eThKVDPjTxqSmzHWXMetbcUwYUFZGSuvi47TTCMvwOGRXAGGKpq9700BGtC5EvM');

// Enhanced payment function
const payWithStripe = async () => {
  if (!totalCartValue.value || totalCartValue.value <= 0) {
    paymentError.value = 'Cart total must be greater than 0.';
    return;
  }

  paymentProcessing.value = true;
  paymentError.value = null;

  try {
    const orderResponse = await axios.post(
      '/api/checkout',
      {},
      {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      }
    );

    const response = await axios.post(
      '/api/create-checkout-session',
      {
        order_id: orderResponse.data.order_id,
        amount: Math.round(totalCartValue.value * 100),
        currency: 'TRY'
      }
    );

    const stripe = await stripePromise;
    const { error } = await stripe.redirectToCheckout({
      sessionId: response.data.sessionId
    });

    if (error) {
      paymentError.value = error.message;
    }
  } catch (err) {
    console.error('Payment error:', err);
    paymentError.value = err.response?.data?.message || 'Payment failed. Please try again.';
  } finally {
    paymentProcessing.value = false;
  }
};

const payOrder = async (order) => {
  try {
    // Create Stripe session on backend
    const res = await axios.post(
      '/api/create-checkout-session',
      { order_id: order.id },
      { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
    );

    const stripe = await stripePromise;

    // Redirect to Stripe checkout page
    await stripe.redirectToCheckout({ sessionId: res.data.sessionId });
  } catch (err) {
    console.error('Payment error:', err);
    alert('Payment failed. Please try again.');
  }
};

const deleteOrder = async (orderId) => {
  if (!confirm('Are you sure you want to delete this order?')) return;

  try {
    const token = localStorage.getItem('token');

  await axios.delete(`/api/orders/${orderId}`, {
  headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
});

    await fetchOrders(); // Refresh orders after deletion
    alert('Order deleted successfully.');
  } catch (err) {
    console.error('Failed to delete order:', err);
    alert('Failed to delete order.');
  }
  
};
// Add this with your other refs
const units = ref([]);

// Add this to your onMounted or initialization function
const fetchUnits = async () => {
  try {
    const res = await axios.get('/api/units');
    units.value = res.data;
  } catch (err) {
    console.error('Failed to fetch units:', err);
  }
};

// Call it in your onMounted
onMounted(async () => {
  // ... existing code
  await fetchUnits();
});



// UI State
const loading = ref(false)
const globalError = ref(null)
const isLoadingProfile = ref(true)
const settingsOpen = ref(false)
const profileModalOpen = ref(false)
const profilePictureUrl = ref('')
const defaultAvatar = '/default-avatar.png' // Path to your default avatar image

// User data
const userProfile = ref({
  name: '',
  email: '',
  avatar_url: null
})

// Computed properties
const firstName = computed(() => userProfile.value.name?.split(' ')[0] || 'User')
const userInitial = computed(() => userProfile.value.name?.charAt(0).toUpperCase() || 'U')


const closeSettings = () => {
  settingsOpen.value = false
}


// Profile modal methods
const openProfile = () => {
  profileModalOpen.value = true
}

const closeProfile = () => {
  profileModalOpen.value = false
}

const onProfileUpdated = (updatedUser) => {
  userProfile.value = updatedUser
  profilePictureUrl.value = updatedUser.avatar_url || defaultAvatar
  closeProfile()
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.settings-wrapper')) {
    closeSettings()
  }
}



onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Fetch user profile
const fetchUserProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) return
    
    const response = await axios.get('/api/user/profile', {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    userProfile.value = response.data
    profilePictureUrl.value = response.data.avatar_url || defaultAvatar
  } catch (error) {
    console.error('Error fetching profile:', error)
    profilePictureUrl.value = defaultAvatar
  }
}

// Initialize on component mount
onMounted(() => {
  fetchUserProfile()
})


const updateProfile = async (form) => {
  try {
    const token = localStorage.getItem('token')
    if (!token) return alert('Authentication error')

    const formData = new FormData()
    formData.append('name', form.name)
    formData.append('email', form.email)
    formData.append('avatar', form.value.avatar);

    await axios.post('/api/user/profile', formData, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      },
    })

    profileModalOpen.value = false
  } catch (err) {
    console.error('Update failed:', err)
    globalError.value = 'Profile update failed. Please try again.'
  }
}


// Modal controls
const toggleSettings = () => (settingsOpen.value = !settingsOpen.value)


// Add this with your other functions
const setAuthToken = () => {
  const token = localStorage.getItem('token')
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  } else {
    delete axios.defaults.headers.common['Authorization']
    router.push('/login') // Redirect to login if no token
  }
}

// Auth actions
const handleLogout = async () => {
  try {
    await axios.post('/api/logout')
    localStorage.removeItem('token')
    router.push('/login')
  } catch {
    alert('Logout failed.')
  }
}

const products = ref([])
const categories = ref([])
const activeFilter = ref('all')
const query = ref('')
const results = ref([])

const searchProducts = () => {
  const lowerQuery = query.value.toLowerCase().trim();
  if (!lowerQuery) {
    results.value = [];
    return;
  }

  results.value = products.value.filter((product) =>
    product.name.toLowerCase().includes(lowerQuery)
  );
};

const selectProduct = (product) => {
  selectedProduct.value = product;
};



const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories')
    categories.value = res.data
  } catch (err) {
    globalError.value = handleApiError(err, 'Fetch categories')
  }
}

const setFilter = (slug) => {
  activeFilter.value = slug
}

const filteredProducts = computed(() => {
  let base = products.value;

  // Filter by category if not 'all'
  if (activeFilter.value !== 'all') {
    base = base.filter(
      product => product.category?.slug === activeFilter.value
    );
  }

  // Further filter by search query if provided
  const searchTerm = query.value.trim().toLowerCase();
  if (searchTerm !== '') {
    base = base.filter(
      product => product.name.toLowerCase().includes(searchTerm)
    );
  }

  return base;
});

// Fetching logic
const fetchProducts = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/products')
    products.value = Array.isArray(res.data.data) ? 
    res.data.data : []
    
    products.value.forEach(p => {
      if (!p.image_url && p.image) {
        p.image_url = getImageUrl(p.image)
      }
    })
  } catch (err) {
    globalError.value = handleApiError(err, 'Fetch products')
    products.value = []
  } finally {
    loading.value = false
  }
}


const allReviews = ref([]);
const submittedReviews = ref([]);

const reviewing = reactive({});
const ratings = reactive({});
const comments = reactive({});

const editing = reactive({});
const editedRatings = reactive({});
const editedComments = reactive({});

// Helper to get token and throw if missing
function getToken() {
  const token = localStorage.getItem("token");
  if (!token) {
    alert("Please log in first.");
    throw new Error("No token found");
  }
  return token;
}

// ✅ Toggle review form
const toggleReview = (productId) => {
  reviewing[productId] = !reviewing[productId];
};

// ✅ Set star rating when leaving a new review
const setRating = (productId, rating) => {
  ratings[productId] = rating;
};

// ✅ Submit new review to backend
const submitReview = async (productId) => {
  try {
    const token = getToken();

    const res = await fetch("http://127.0.0.1:8000/api/feedbacks", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        product_id: productId,
        rating: ratings[productId],
        comment: comments[productId] || "",
      }),
    });

    const data = await res.json();

    if (res.ok) {
      // Optionally you can fetch the full product name here if needed
      submittedReviews.value.push({
        id: data.data.id,
        product: data.data.product_id,  // if you want product name, fetch separately
        rating: data.data.rating,
        comment: data.data.comment,
        timestamp: data.data.created_at,
      });

      reviewing[productId] = false;
      ratings[productId] = 0;
      comments[productId] = "";
    } else {
      alert(data.message || "Failed to submit review");
    }
  } catch (err) {
    console.error("Error submitting review:", err);
    alert("An error occurred. Please try again.");
  }
};

// ✅ Fetch reviews for a specific product (used when displaying product feedback)
const fetchProductReviews = async (productId) => {
  try {
    const token = getToken();

    const res = await fetch(
      `http://127.0.0.1:8000/api/feedbacks/product/${productId}`, // Adjusted to match your backend route name: productFeedback
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    const data = await res.json();
    return data.feedback || [];
  } catch (err) {
    console.error("Error loading product reviews:", err);
    return [];
  }
};

// ✅ Fetch current user's submitted reviews
const fetchReviews = async () => {
  try {
    const token = getToken();

    const res = await fetch("http://127.0.0.1:8000/api/feedbacks/user", {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (!res.ok) throw new Error("Failed to fetch user reviews");

    const data = await res.json();

    submittedReviews.value = data.map((r) => ({
      id: r.id,
      product: r.product?.name ?? "Unknown",
      rating: r.rating,
      comment: r.comment,
      reply: r.reply || null,
      timestamp: r.created_at,
    }));
  } catch (error) {
    console.error("Error fetching user reviews:", error);
  }
};

// ✅ Fetch all approved reviews (for public display)
const fetchAllReviews = async () => {
  try {
    const res = await axios.get(
      "http://127.0.0.1:8000/api/feedbacks/approved"
    );
    allReviews.value = res.data.feedback || [];
  } catch (err) {
    console.error("Error loading all reviews:", err);
  }
};

// ✅ Start editing a review
function startEdit(index) {
  if (submittedReviews.value[index].reply) {
    alert("❌ You cannot edit this review because the farmer has already replied.");
    return;
  }

  editing[index] = true;
  editedRatings[index] = submittedReviews.value[index].rating;
  editedComments[index] = submittedReviews.value[index].comment;
}

// ✅ Change star rating in edit mode
function setEditedRating(index, rating) {
  editedRatings[index] = rating;
}

// ✅ Cancel editing without saving
function cancelEdit(index) {
  editing[index] = false;
  editedRatings[index] = 0;
  editedComments[index] = "";
}
async function saveEdit(index, reviewId) {
  try {
    const token = getToken();

    const payload = {
      rating: editedRatings[index],
      comment: editedComments[index],
    };

    const response = await axios.put(
      `http://127.0.0.1:8000/api/feedbacks/${reviewId}`,
      payload,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      }
    );

    alert("✅ Review updated successfully.");
    submittedReviews.value[index].rating = payload.rating;
    submittedReviews.value[index].comment = payload.comment;
    cancelEdit(index);

  } catch (error) {
    console.error("Save edit error:", error);

    const status = error.response?.status;
    const msg = error.response?.data?.message || "Unknown error";

    if (status === 403) {
      alert("🔒 You can't edit this review (permission denied).");
    } else if (status === 404) {
      alert("❌ Review not found.");
    } else {
      alert(`❌ Update failed: ${msg}`);
    }
  }
}

// ✅ Fetch reviews on component mount
onMounted(() => {
  fetchReviews();
  fetchAllReviews();
});


const availableUnits = ref([]);
const selectedUnits = ref({});

onMounted(async () => {
  try {
    const res = await axios.get("/api/units");
    availableUnits.value = res.data; // ✅ same as other dashboard
  } catch (error) {
    console.error("Failed to load units", error);
  }
});

// Cart & Orders
const cart = ref([])
const orders = ref([])
const quantities = ref({})
const totalAmount = ref(0)
const paymentMethod = ref('card')
const paymentStatus = ref('')
const card = ref({ number: '', expiry: '', cvc: '' })

// Image helper
const getImageUrl = (image) =>
  image ? `http://localhost:8000/storage/${image}` : 'https://via.placeholder.com/150'

// API error handler
const handleApiError = (error, context = 'Operation') => {
  console.error(`${context} error:`, error)
  let msg = `${context} failed.`
  if (error.response) {
    const status = error.response.status
    const data = error.response.data
    switch (status) {
      case 400: msg = data.message || 'Bad request'; break
      case 401:
        msg = 'Unauthorized. Please login again.'
        localStorage.removeItem('token')
        router.push('/login')
        return
      case 403: msg = 'Forbidden'; break
      case 404: msg = 'Not found'; break
      case 422: msg = data.message || 'Validation failed'; break
      case 500: msg = 'Server error'; break
      default: msg = data.message || `Error (${status})`
    }
  } else if (error.request) {
    msg = 'Network error.'
  }
  return msg
}

const fetchCart = async () => {
  console.log('Fetching cart...')  // Add this
  loading.value = true
  try {
    const res = await axios.get('/api/consumer/cart')
    console.log('Cart response:', res.data)  // Add this
    cart.value = res.data.cart || []
    cart.value.forEach(item => {
      if (!item.image_url && item.image) {
        item.image_url = getImageUrl(item.image)
      }
    })
  } catch (err) {
    globalError.value = handleApiError(err, 'Fetch cart')
  } finally {
    loading.value = false
  }
}

const cartWithUnits = computed(() =>
  cart.value.map((item) => {
    // Find the matching unit from availableUnits
    const unitObj = availableUnits.value.find((u) => u.id === item.unit_id);
    return {
      ...item,
      unit: unitObj || null, // Attach full unit info
    };
  })
);

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}


const fetchOrders = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('token');

    const response = await axios.get('/api/orders', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    orders.value = response.data.data.map(order => ({
      id: order.order_id,
      created_at: order.order_date, // formatted date string from backend
      total: order.total_price,
      status: order.status.toLowerCase(), // lowercase to match CSS classes
      items: order.items.map(item => ({
        product: { name: item.product_name },
        quantity: item.quantity,
        price: item.price_each,
      })),
    }));
  } catch (error) {
    console.error('Failed to fetch orders:', error);
  } finally {
    loading.value = false;
  }
};

// Fetch orders when the component mounts
onMounted(() => {
  if (section.value === 'orders') {
    fetchOrders();
  }
});


const addToCart = async (product) => {
  const quantity = quantities.value[product.id];
  const unit = selectedUnits.value[product.id] || null;

  // Validation
  if (!quantity || quantity < 1) {
    alert("Please enter a valid quantity.");
    return;
  }
  if (!unit) {
    alert("Please select a unit.");
    return;
  }

  try {
    const res = await axios.post("/api/consumer/cart", {
      product_id: product.id,
      quantity,
      unit,
    });

    // ✅ SUCCESS: Backend should return updated product data or cart item
    const updatedProduct = res.data.product; // ← Expect this from backend
    if (updatedProduct && updatedProduct.quantity !== undefined) {
      product.quantity = updatedProduct.quantity; // Sync with backend
    } else {
      // Fallback: just subtract locally (less safe)
      product.quantity = Math.max(0, product.quantity - quantity);
    }

    // Optional: Show feedback
    alert(`${quantity} ${unit} of ${product.name} added to cart!`);

    // Reset input
    quantities.value[product.id] = 0;

    // Optionally refresh cart
    await fetchCart();
  } catch (err) {
    console.error("Add to cart error:", err);
    globalError.value = handleApiError(err, "Add to cart");
  }
};


const removeFromCart = async (productId) => {
  const item = cart.value.find(i => i.product_id === productId)
  if (!item) return alert('Not in cart')
  try {
    await axios.delete(`/api/consumer/cart/${item.id}`)
    await fetchCart()
  } catch (err) {
    globalError.value = handleApiError(err, 'Remove from cart')
  }
}

const updateCartQuantity = async (productId, newQty) => {
  if (newQty < 1) return await removeFromCart(productId)
  const item = cart.value.find(i => i.product_id === productId)
  if (!item) return
  try {
    await axios.put(`/api/consumer/cart/${item.id}`, { quantity: newQty })
    await fetchCart()
  } catch (err) {
    globalError.value = handleApiError(err, 'Update quantity')
  }
}

const clearCart = async () => {
  if (!confirm('Clear entire cart?')) return
  try {
axios.delete('http://127.0.0.1:8000/api/consumer/cart/clear')
    cart.value = []
  } catch (err) {
    globalError.value = handleApiError(err, 'Clear cart')
  }
}

const totalCartValue = computed(() => {
  return cart.value.reduce((sum, item) => {
    const unit = item.unit_price || 0
    const qty = item.quantity || 1
    return sum + unit * qty
  }, 0).toFixed(2)
})

const fetchCartTotal = async () => {
  try {
    const res = await axios.get('/api/cart/total', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    totalAmount.value = res.data.total
  } catch (err) {
    console.error('Cart total error:', err)
  }
}

// Checkout logic
function checkout() {
  section.value = 'payment'
}

// Initialization
onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('Please login first.')
    return router.push('/login')
  }

  setAuthToken()
  await fetchUserProfile()
  await fetchProducts()
  await fetchCart()
  await fetchOrders()
  await fetchCartTotal()
  await fetchCategories()
})

// Computed properties for dashboard
const pendingOrdersCount = computed(() => 
  orders.value.filter(order => order.status === 'pending').length
)

const totalSpent = computed(() => 
  orders.value
    .filter(order => order.status === 'delivered' || order.status === 'paid')
    .reduce((sum, order) => sum + parseFloat(order.total || 0), 0)
    .toFixed(2)
)

const averageRatingGiven = computed(() => {
  if (submittedReviews.value.length === 0) return '0.0'
  const sum = submittedReviews.value.reduce((acc, review) => acc + review.rating, 0)
  return (sum / submittedReviews.value.length).toFixed(1)
})

const recentOrders = computed(() => 
  orders.value
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 3)
)

const cartPreview = computed(() => 
  cart.value.slice(0, 3)
)

const recentReviews = computed(() => 
  submittedReviews.value
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 2)
)


// Fix the featuredProducts computed property
const featuredProducts = computed(() => {
  const productList = products.value || []  // ✅ Use a different variable name
  return productList
    .sort(() => 0.5 - Math.random())
    .slice(0, 4)
})

const goToProduct = (product) => {
  router.push(`/product/${product.id}`)
}

</script>

<style scoped>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .consumer-app {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            margin: 0;
            padding: 0;
        }

        /* Professional Subtle Background */
        .consumer-app::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(59, 130, 246, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.02) 0%, transparent 50%);
            z-index: -1;
        }




/* Header Container (unchanged) */
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: #ffffff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  position: relative; /* Needed for absolute positioning */
}

/* NEW: Centered Greeting Solution */
.greeting-center {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  text-align: center;
  width: max-content; /* Prevents stretching */
}

.greeting-center h2 {
  margin: 0;
  font-size: 1.6rem;
  color: #375f9eff;
}

.greeting-center p {
  margin: 0.25rem 0 0;
  color: #080808ff;
  font-size: 1rem;
}

/* Existing Avatar/Settings Styles (unchanged) */
.settings-wrapper {
  position: relative;
  cursor: pointer;
  margin-left: auto; /* Pushes to far right */
}

.profile-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e2e8f0;
}

.profile-avatar.placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #375f9eff;
  color: white;
  font-weight: bold;
  font-size: 1.5rem;
}

.settings-menu {
  position: absolute;
  top: 65px;
  right: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  min-width: 150px;
  z-index: 100;
  border: 1px solid #e2e8f0;
}

.settings-menu ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.settings-menu li {
  padding: 12px 16px;
  cursor: pointer;
  color: #64748b;
  font-weight: 500;
}

.settings-menu li:hover {
  background: #375f9eff;
  color: white;
}

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            backdrop-filter: blur(20px);
            display: flex;
            justify-content: space-around;
            padding: 1rem 0;
            border-top: 1px solid #e2e8f0;
            z-index: 100;
            box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.1);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #64748b;
            position: relative;
        }

        .nav-item.active {
            color: #375f9eff;
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        .nav-item:hover {
            transform: translateY(-2px);
            color: #375f9eff;
        }

        .nav-item svg, .nav-item span {
            position: relative;
            z-index: 1;
        }

        /* Main Content */
        .main-view {
            flex: 1;
            padding: 2rem;
            padding-bottom: 5rem;
            overflow-y: auto;
        }
        

        /* Search Bar */
        .search-bar {
            max-width: 600px;
            margin: 5rem auto 2rem; /* Increased top margin from 0 to 3rem */
        }

        .search-input-wrapper {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .search-input-wrapper:focus-within {
            border-color: #375f9eff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-input-wrapper input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 1rem;
            margin-left: 1rem;
            outline: none;
            color: #1e293b;
        }

        .search-input-wrapper input::placeholder {
            color: #375f9eff;
        }

        .search-icon {
            color: #375f9eff;
        }

        /* Filter Controls */
        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            position: relative;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #375f9eff;
            color: white;
            border-color: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .filter-btn span {
            position: relative;
            z-index: 1;
        }
 .no-products {
  text-align: center;
  padding: 2rem;
  font-size: 1.2rem;
  color: #64748b;
  width: 100%;
}
    /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            
        }

        .product-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;


        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            object-fit: cover;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.02);
        }

        .product-card h3 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #050505ff;
            margin-bottom: 0.5rem;
           font-family: 'Dancing Script', cursive;
        }

        .product-card p {
            color: #020202ff;
            margin-bottom: 0.5rem;
            line-height: 1.5;
            font-size: 1rem;
        }

        .cart-unit {
       margin-left: 6px;
      font-weight: 500;
      color: #444;
     }


.quantity-cart {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 10px;
}

.quantity-cart input {
  width: 47px;
  padding: 8px 5px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 10x;
}

.quantity-cart select {
  padding: 10px 0px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 10px;
  background-color: white;
  cursor: pointer;
  transition: 0.2s ease;
    width: 56px;


}

.quantity-cart select:hover {
  border-color: #38bff8;
}

.quantity-cart button {
  background-color: #2563eb;
  color: white;
  padding: 10px 55px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.2s ease;

}

.quantity-cart button:hover {
  background-color: #1d4ed8;
}


    

        /* Review System */
        .review-btn {
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.5rem;
            border: none;
            border-radius: 8px;
            background: #10b981;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .review-btn:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .review-box {
            background: rgba(248, 250, 252, 0.8);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e2e8f0;
        }

        .star-rating {
            display: flex;
            gap: 0.2rem;
            margin-bottom: 1rem;
            justify-content: center;
        }

        .star {
            font-size: 1.5rem;
            color: #d1d5db;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star.active {
            color: #e5f50bff;
        }

        .star:hover {
            transform: scale(1.1);
        }

        .comment-box {
            display: flex;
            gap: 0.5rem;
        }

        .comment-box input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            transition: all 0.2s ease;
            
        }

        .comment-box input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .comment-box button {
            padding: 0.85rem 0.5rem;
            border: none;
            border-radius: 6px;
            background: #3b82f6;
            color: white;
            font-weight: 400;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .comment-box button:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }


 * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Amazon Ember', Arial, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }

        .cart-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            margin-top: 40px;
            padding: 20px 30px;
            border-bottom: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cart-title {
            font-size: 28px;
            font-weight: 400;
            color: #0f1111;
            margin: 0;
        }

        .items-count {
            background: #e7f3ff;
            color: #0066c0;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .clear-cart-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fe0a0aff;
            color: #fbf4f4ff;
            border: 1px solid #fecaca;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .clear-cart-btn:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        /* Empty State */
        .empty-state {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .empty-content {
            text-align: center;
            max-width: 400px;
        }

        .empty-icon {
            margin-bottom: 20px;
            color: #9ca3af;
        }

        .empty-content h2 {
            font-size: 24px;
            color: #111827;
            margin-bottom: 10px;
            font-weight: 400;
        }

        .empty-content p {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .continue-shopping-btn {
            background: #ff9500;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        .continue-shopping-btn:hover {
            background: #e88500;
        }

        /* Cart Layout */
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            align-items: start;
        }

        /* Items List */
        .cart-items {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .items-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8f9fa;
        }

        .items-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            color: #111827;
        }

        .items-list {
            padding: 0;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 20px;
            padding: 25px 30px;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
        }

        .cart-item:hover {
            background: #fafafa;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-name {
            font-size: 16px;
            font-weight: 500;
            color: #0066c0;
            margin: 0;
            line-height: 1.3;
            cursor: pointer;
        }

        .item-name:hover {
            color: #c7511f;
            text-decoration: underline;
        }

        .item-description {
            color: #565959;
            font-size: 14px;
            margin: 0;
            line-height: 1.4;
        }

        .item-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 5px 0;
        }

        .item-price {
            font-size: 18px;
            font-weight: 700;
            color: #b12704;
        }

        .item-unit {
            color: #565959;
            font-size: 14px;
        }

        .item-availability {
            color: #007600;
            font-size: 14px;
            font-weight: 500;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 10px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-selector label {
            font-size: 14px;
            color: #111827;
            font-weight: 500;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
        }

        .qty-btn {
            background: none;
            border: none;
            padding: 10px 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
            transition: background 0.2s;
        }

        .qty-btn:hover:not(:disabled) {
            background: #f3f4f6;
        }

        .qty-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .qty-input {
            border: none;
            width: 50px;
            text-align: center;
            padding: 8px 8px;
            font-size: 14px;
            background: none;
            outline: none;
        }

        .unit-label {
            font-size: 14px;
            color: #6b7280;
            margin-left: 5px;
        }

  

        .item-total {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .total-price {
            font-size: 18px;
            font-weight: 700;
            color: #b12704;
        }

        /* Order Summary */
        .order-summary {
            position: sticky;
            top: 20px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .summary-title {
            font-size: 18px;
            font-weight: 500;
            color: #111827;
            margin: 0;
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8f9fa;
        }

        .summary-details {
            padding: 20px 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .summary-row span:first-child {
            color: #374151;
        }

        .summary-row span:last-child {
            font-weight: 500;
            color: #111827;
        }

        .summary-total {
            padding: 20px 25px;
            border-top: 1px solid #e5e7eb;
            background: #f8f9fa;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-row span:first-child {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }

        .total-amount {
            font-size: 20px;
            font-weight: 700;
            color: #b12704;
        }

        .summary-actions {
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .checkout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #ff9500;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
        }

        .checkout-btn:hover {
            background: #e88500;
        }

        .continue-btn {
            background: white;
            color: #0066c0;
            border: 1px solid #d5d9d9;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .continue-btn:hover {
            background: #f7f8f8;
            border-color: #adb1b8;
        }

        .trust-badges {
            padding: 20px 25px;
            border-top: 1px solid #e5e7eb;
            background: #f8f9fa;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 12px;
            color: #565959;
        }

        .badge:last-child {
            margin-bottom: 0;
        }

        .badge svg {
            color: #007600;
        }

/* Remove Button */
.remove-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  margin-top: 6px;
  cursor: pointer;
  width: 20%;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.61);

}
.remove-btn:hover {
  background: #dc2626;
}

.payment-section {
    background: #ffffff;
    border-radius: 0 0 12px 12px; /* Only bottom corners rounded */
    padding: 2rem;
    max-width: 500px;
    margin: 0 auto; /* Remove top margin */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    border-top: none; /* Remove top border */
    position: fixed; /* Make it stick to top */
    top: 0;
    left: 50%;
    transform: translateX(-50%); /* Center horizontally */
    z-index: 1000; /* Ensure it stays on top */
    width: 100%;
    max-width: 500px;
}

.payment-section {
    background: #ffffff;
    border-radius: 12px; /* All corners rounded since it's not at very top */
    padding: 2rem;
    max-width: 500px;
    margin: 0 auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    position: fixed;
    top: 100px; /* ADJUST THIS VALUE - Distance from top of viewport */
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    width: 100%;
    max-width: 500px;
}

/* Different positioning options - use one of these: */

/* Close to top */
.payment-section.near-top {
    top: 20px;
}

/* A bit lower */
.payment-section.medium-top {
    top: 80px;
}

/* Much lower */
.payment-section.lower-top {
    top: 150px;
}

/* Center of screen */
.payment-section.center-screen {
    top: 50%;
    transform: translate(-50%, -50%);
}

/* Alternative: If you want it to be part of document flow but at very top */
.payment-section-static {
    background: #ffffff;
    border-radius: 0 0 12px 12px;
    padding: 2rem;
    max-width: 500px;
    margin: 0 auto 2rem; /* No top margin, bottom margin for spacing */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    border-top: none;
    position: relative;
}

.payment-section h2 {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.5rem;
}

.order-summary {
    margin-bottom: 1.5rem;
}

.order-summary h3 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #374151;
    font-weight: 600;
}

.summary-items {
    margin: 1rem 0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    font-size: 0.9rem;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    font-weight: 700;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    font-size: 1.1rem;
}

.payment-methods {
    margin-top: 1.5rem;
}

.payment-methods h3 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #374151;
    font-weight: 600;
}

.card-details input, .payment-section select {
    width: 100%;
    padding: 0.75rem;
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.card-details input:focus, .payment-section select:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.stripe-pay-button,
.pay-now-btn {
    width: 100%;
    padding: 1rem;
    border: none;
    border-radius: 8px;
    background: #3b82f6;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.stripe-pay-button:hover,
.pay-now-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

/* Add padding to body to prevent content from hiding behind fixed payment section */
body.payment-active {
    padding-top: 200px; /* Adjust based on payment section height */
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .payment-section {
        max-width: 100%;
        margin: 0;
        border-radius: 0;
        padding: 1.5rem;
    }
    
    .payment-section-static {
        max-width: 100%;
        margin: 0 0 2rem;
        border-radius: 0;
        padding: 1.5rem;
    }
    
    body.payment-active {
        padding-top: 150px; /* Adjust for mobile */
    }
}

.messaging-section {
  display: flex;
  height: 600px; /* or your desired height */
  background-color: #b0c9d6; /* the blue background behind the white container */
  justify-content: center;
  align-items: center;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.messaging-container {
  display: flex;
  width: 960px;
  height: 480px;
  background-color: white;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  border-radius: 4px;
  overflow: hidden;
}

/* Left sidebar */
.conversation-list {
  width: 320px;
  border-right: 1px solid #ddd;
  display: flex;
  flex-direction: column;
}

.conversation-header {
  padding: 12px;
  border-bottom: 1px solid #ddd;
  font-weight: 600;
  font-size: 18px;
}

.search-conversations input {
  width: 100%;
  padding: 6px 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 2px;
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
  padding: 10px 12px;
  cursor: pointer;
  border-left: 4px solid transparent;
  transition: background-color 0.2s, border-color 0.2s;
  align-items: center;
}

.conversation-item.active {
  background-color: #2a7fff;
  color: white;
  border-left-color: #1565d8;
}

.conversation-item:hover {
  background-color: #e5f1ff;
}

.avatar-wrapper {
  position: relative;
  margin-right: 10px;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}

.unread-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: #1de9b6;
  color: #222;
  font-weight: 600;
  font-size: 10px;
  padding: 2px 5px;
  border-radius: 12px;
}

/* Conversation details */
.conversation-details {
  flex: 1;
  font-size: 13px;
}

.conversation-header-row {
  display: flex;
  justify-content: space-between;
  font-weight: 600;
  margin-bottom: 4px;
}

.timestamp {
  color: #ccc;
  font-size: 11px;
}

.last-message {
  color: #666;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Chat area */
.chat-area {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  background: #fefefe;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #ddd;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
}

.header-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  margin-right: 10px;
  object-fit: cover;
}

.header-info h4 {
  margin: 0;
  font-weight: 700;
}

.status {
  font-size: 12px;
  color: #aaa;
  margin-top: 2px;
}

.header-actions button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  margin-left: 8px;
  border-radius: 4px;
  transition: background-color 0.15s;
}

.header-actions button:hover {
  background-color: #e5e5e5;
}

/* Messages container */
.messages-container {
  flex-grow: 1;
  padding: 10px 20px;
  overflow-y: auto;
  background: #f7f9fc;
}

/* Message groups */
.message-group {
  margin-bottom: 16px;
  max-width: 65%;
}

.message-group.current-user {
  margin-left: auto;
  max-width: 65%;
  text-align: right;
}

.message-sender {
  display: flex;
  align-items: center;
  margin-bottom: 6px;
}

.sender-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  margin-right: 6px;
  object-fit: cover;
}

.sender-name {
  font-weight: 600;
  font-size: 13px;
  color: #555;
}

.message-bubble {
  background-color: #ddd;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px;
  line-height: 1.3;
  position: relative;
  display: inline-block;
}

.message-bubble.my-message {
  background-color: #a9ccff;
  color: #0a3c91;
}

.message-content p {
  margin: 0;
}

.message-meta {
  margin-top: 6px;
  display: flex;
  justify-content: flex-end;
  font-size: 10px;
  color: #999;
  align-items: center;
}

.timestamp {
  margin-left: 6px;
}

/* Input area */
.message-input {
  display: flex;
  align-items: center;
  border-top: 1px solid #ddd;
  padding: 8px 16px;
  background: white;
}

.input-actions button {
  background: none;
  border: none;
  margin-right: 8px;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
}

.input-wrapper {
  flex-grow: 1;
}

.message-input input {
  width: 100%;
  padding: 8px 12px;
  font-size: 14px;
  border: 1px solid #ddd;
  border-radius: 20px;
}

.send-button {
  background: none;
  border: none;
  cursor: pointer;
  margin-left: 8px;
  padding: 6px;
  border-radius: 4px;
  transition: color 0.3s;
  color: #aaa;
}

.send-button.active {
  color: #2a7fff;
}
.messaging-section {
  display: flex;
  height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  justify-content: center;
  align-items: center;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  margin: 0;
}

.messaging-container {
  display: flex;
  width: 90%;
  max-width: 1200px;
  height: 85vh;
  background-color: white;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  border-radius: 12px;
  overflow: hidden;
}

/* Left sidebar */
.conversation-list {
  width: 350px;
  border-right: 1px solid #e6e9f0;
  display: flex;
  flex-direction: column;
  background-color: #f8fafc;
}

.conversation-header {
  padding: 16px;
  border-bottom: 1px solid #e6e9f0;
  font-weight: 600;
  font-size: 18px;
  color: #2d3748;
  background-color: white;
}

.search-conversations {
  padding: 12px 16px;
  background-color: white;
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
  background-color: white;
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
  display: flex;
  flex-direction: column;
  background: #f7fafc;
  
}

.chat-header {
  display: flex;
  justify-content: space-between;
  padding: 16px 24px;
  border-bottom: 1px solid #e6e9f0;
  align-items: center;
  background-color: white;
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
  color: #2d3748;
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
  background-color: #edf2f7;
  color: #2d3748;
}
/* Container holding all messages */
.messages-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  overflow-y: auto;
  padding: 1rem;
  height: 60vh;
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
  background: white;
}

.input-actions button {
  background: none;
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
  background-color: #f8fafc;
  transition: all 0.3s;
}

.message-input input:focus {
  outline: none;
  border-color: #a0aec0;
  background-color: white;
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



/* Title styling – moved down by top margin */
.card-section h2 {
  font-size: 1.6rem;
  font-weight: 600;
  margin-top: 3.5rem; /* Pushes it below any previous element like a nav */
  margin-bottom: 0.5rem;
  color: #1f2937;
}

/* Grid of order cards */
.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  width: 100%;
}

/* Individual order card */
.order-card {
  background: #f9f9f9;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  padding: 1rem;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Header row in each order card */
.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.7rem;
}

/* Badge styling */
.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
  border: 1px solid transparent;
}

.status-badge.pending {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
  border-color: rgba(245, 158, 11, 0.2);
}

.status-badge.paid {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border-color: rgba(16, 185, 129, 0.2);
}

.status-badge.delivered {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
  border-color: rgba(59, 130, 246, 0.2);
}

/* Order details */
.order-date,
.order-total {
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.order-items {
  list-style: none;
  padding-left: 0;
  margin: 0.5rem 0;
  font-size: 0.9rem;
}

/* Status message blocks */
.order-status-msg {
  margin-top: 0.8rem;
  padding: 0.5rem;
  border-radius: 6px;
  font-size: 0.9rem;
}

.order-status-msg.pending {
  background-color: #fff7ed;
  color: #b45309;
}

.order-status-msg.waiting {
  background-color: #eff6ff;
  color: #1d4ed8;
}

.order-status-msg.delivered {
  background-color: #ecfdf5;
  color: #047857;
}

/* Buttons */
.pay-now-btn {
  margin-top: 0.5rem;
  padding: 6px 10px;
  background-color: #22c55e;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.pay-now-btn:hover {
  background-color: #16a34a;
}

.delete-order-btn {
  margin-top: 10px;
  background: #e53e3e;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.delete-order-btn:hover {
  background-color: #c53030;
}

/* Empty state styling */
.empty-state {
  text-align: center;
  color: #6b7280;
  margin-top: 2rem;
  width: 100%;
}

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-badge.pending {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-badge.paid {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-badge.delivered {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* Reviews List */
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .review-item {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .review-product {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .star.active {
            color: #f59e0b;
        }

        .rating-number {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        .review-comment {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            font-style: italic;
        }

        .farmer-reply-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #059669;
            font-weight: 500;
        }

        .reply-icon {
            font-size: 0.9rem;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f1f5f9;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Error Alert */
        .error-alert {
            background: #fdfcfcff;
            color: #fa0000ff;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(13, 13, 13, 0.56);
        }

        .close-error {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            margin-left: auto;
        }

 
.feedback-section {
  margin-top: 2.5rem;
  padding: 1rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1.2rem;
}

.feedback-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.feedback-card {
  background: #ffffff;
  border-radius: 8px;
  padding: 1.2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.feedback-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.product-name {
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.stars {
  margin: 0.5rem 0;
}

.star {
  color: #cbd5e0;
  font-size: 1.2rem;
}

.star.active {
  color: #f59e0b;
}

.timestamp {
  display: block;
  font-size: 0.8rem;
  color: #718096;
  margin-bottom: 0.5rem;
}

.farmer-reply {
  background: #059669;
  color: white;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.no-feedback {
  font-style: italic;
  color: #718096;
  padding: 1rem;
}




    </style>