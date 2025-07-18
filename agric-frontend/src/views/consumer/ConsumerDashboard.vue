<template>
  <div class="consumer-app">

    <header class="header">
 <div class="dashboard-header">
    <!-- Settings dropdown container -->
    <div class="settings-wrapper" @click="toggleSettings">
      <img :src="avatar" alt="Avatar" />
      <!-- Or a gear icon here -->
    </div>

    <div v-if="settingsOpen" class="settings-menu">
     <ul>
  <li @click="openProfile">Profile</li>
  <li @click="handleLogout">Logout</li>
</ul>
    </div>
        </div>
<span class="greeting" v-if="!isLoadingProfile">
  Hello, {{ firstName }}
</span>
<span class="greeting" v-else>
  Loading...
</span>

  <div class="user-icon">
    <div class="avatar-circle" v-if="!isLoadingProfile" @click="openProfileModal" title="Click to change profile picture">
      <template v-if="profilePictureUrl">
        <img :src="profilePictureUrl" alt="Profile Picture" class="profile-avatar" />
      </template>
      <template v-else>
        {{ userInitial }}
      </template>
    </div>
    <div class="avatar-circle loading" v-else></div>

    <!-- Profile modal component -->
   <ProfileModal
  v-if="profileModalOpen"
  @close="closeProfile"
  @updated="onProfileUpdated"
/>
  </div>

  </header>

    <!-- Search -->
    <div class="search-bar">
      <div class="search-input-wrapper">
        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input type="text" placeholder="Search here..." />
      </div>
    </div>

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

      <!-- Messages Section with Enhanced UI -->
      <section v-if="section === 'messages' && !loading" class="consumer-dashboard-messages">
        <header class="messages-header">
          <h2>Your Conversations</h2>
          <button @click="startNewThread" class="btn-new-message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
        </header>

  <div class="email-client flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="w-72 bg-white border-r border-gray-200 flex flex-col">
      <!-- Header -->
      <div class="p-4 border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">Messages</h1>
      </div>

      <!-- Folders -->
      <div class="flex-1 overflow-y-auto">
        <div class="p-4">
          <div class="space-y-2">
            <div 
              v-for="folder in folders" 
              :key="folder.id"
              @click="selectFolder(folder)"
              :class="[
                'flex items-center justify-between p-2 rounded-lg cursor-pointer transition-colors',
                selectedFolder?.id === folder.id ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50'
              ]"
            >
              <div class="flex items-center space-x-3">
                <div :class="[
                  'w-2 h-2 rounded-full',
                  folder.id === 'inbox' ? 'bg-blue-500' : 'bg-gray-400'
                ]"></div>
                <span class="font-medium">{{ folder.name }}</span>
              </div>
              <span class="text-sm text-gray-500">{{ folder.count }}</span>
            </div>
          </div>
        </div>

        <!-- Labels -->
        <div class="p-4 border-t border-gray-200">
          <h3 class="text-sm font-medium text-gray-500 mb-3">Labels</h3>
          <div class="space-y-2">
            <div 
              v-for="label in labels" 
              :key="label.id"
              @click="selectLabel(label)"
              :class="[
                'flex items-center space-x-3 p-2 rounded-lg cursor-pointer transition-colors',
                selectedLabel?.id === label.id ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'
              ]"
            >
              <div :class="['w-3 h-3 rounded-full', label.color]"></div>
              <span class="text-sm">{{ label.name }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Email List -->
    <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
      <!-- Search Header -->
      <div class="p-4 border-b border-gray-200">
        <div class="relative">
          <input 
            type="text" 
            v-model="searchQuery"
            placeholder="Search emails..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
          <div class="absolute left-3 top-2.5 text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Email List -->
      <div class="flex-1 overflow-y-auto">
        <div 
          v-for="email in filteredEmails" 
          :key="email.id"
          @click="selectEmail(email)"
          :class="[
            'p-4 border-b border-gray-100 cursor-pointer transition-colors',
            selectedEmail?.id === email.id ? 'bg-blue-50 border-blue-200' : 'hover:bg-gray-50',
            !email.read ? 'bg-white' : 'bg-gray-50'
          ]"
        >
          <div class="flex items-start space-x-3">
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm',
              email.avatar_color
            ]">
              {{ email.sender.charAt(0).toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <h3 :class="[
                  'font-medium truncate',
                  !email.read ? 'text-gray-900' : 'text-gray-700'
                ]">
                  {{ email.sender }}
                </h3>
                <span class="text-xs text-gray-500">{{ formatTime(email.time) }}</span>
              </div>
              <p :class="[
                'text-sm truncate mt-1',
                !email.read ? 'text-gray-900' : 'text-gray-600'
              ]">
                {{ email.subject }}
              </p>
              <p class="text-xs text-gray-500 truncate mt-1">
                {{ email.preview }}
              </p>
              <div class="flex items-center mt-2 space-x-2">
                <span v-if="email.urgent" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  Urgent
                </span>
                <span v-if="email.category" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ email.category }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Email Detail -->
    <div class="flex-1 flex flex-col">
      <div v-if="selectedEmail" class="flex-1 flex flex-col">
        <!-- Email Header -->
        <div class="p-6 border-b border-gray-200 bg-white">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
              <div :class="[
                'w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold',
                selectedEmail.avatar_color
              ]">
                {{ selectedEmail.sender.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ selectedEmail.sender }}</h2>
                <p class="text-sm text-gray-500">{{ selectedEmail.email }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button 
                @click="toggleStar(selectedEmail)"
                :class="[
                  'p-2 rounded-lg transition-colors',
                  selectedEmail.starred ? 'text-yellow-500 hover:bg-yellow-50' : 'text-gray-400 hover:bg-gray-50'
                ]"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
              </button>
              <button class="p-2 rounded-lg text-gray-400 hover:bg-gray-50 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                </svg>
              </button>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">{{ selectedEmail.subject }}</h1>
            <span class="text-sm text-gray-500">{{ formatTime(selectedEmail.time) }}</span>
          </div>
        </div>

        <!-- Email Content -->
        <div class="flex-1 p-6 bg-white overflow-y-auto">
          <div class="prose max-w-none">
            <p class="text-gray-700 leading-relaxed">{{ selectedEmail.content }}</p>
          </div>
        </div>

        <!-- Email Actions -->
        <div class="p-4 border-t border-gray-200 bg-gray-50">
          <div class="flex space-x-3">
            <button 
              @click="replyToEmail"
              class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium"
            >
              Reply
            </button>
            <button 
              @click="forwardEmail"
              class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
            >
              Forward
            </button>
            <button 
              @click="deleteEmail"
              class="px-4 py-2 text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition-colors font-medium"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex-1 flex items-center justify-center bg-white">
        <div class="text-center">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No email selected</h3>
          <p class="text-gray-500">Select an email to view its contents</p>
        </div>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="w-80 bg-white border-l border-gray-200 p-4">
      <div v-if="selectedEmail" class="space-y-6">
        <!-- Contact Info -->
        <div class="text-center">
          <div :class="[
            'w-16 h-16 rounded-full flex items-center justify-center text-white font-semibold text-xl mx-auto mb-3',
            selectedEmail.avatar_color
          ]">
            {{ selectedEmail.sender.charAt(0).toUpperCase() }}
          </div>
          <h3 class="font-semibold text-gray-900">{{ selectedEmail.sender }}</h3>
          <p class="text-sm text-gray-500">{{ selectedEmail.email }}</p>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-3">
          <h4 class="font-medium text-gray-900">Quick Actions</h4>
          <div class="space-y-2">
            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
              Mark as Important
            </button>
            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
              Add to Calendar
            </button>
            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
              Create Task
            </button>
          </div>
        </div>

        <!-- Email Details -->
        <div class="space-y-3">
          <h4 class="font-medium text-gray-900">Email Details</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Status:</span>
              <span class="text-gray-900">{{ selectedEmail.read ? 'Read' : 'Unread' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Priority:</span>
              <span class="text-gray-900">{{ selectedEmail.urgent ? 'High' : 'Normal' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Category:</span>
              <span class="text-gray-900">{{ selectedEmail.category || 'None' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


      
<section v-if="section === 'market' && !loading" class="card-section">
  <h2>Market</h2>
  <div class="product-grid">
    <!-- Remove the extra wrapper div and apply v-for directly to product-card -->
    <div
      v-for="product in products"
      :key="product.id"
      class="product-card"
      v-if="products.length"
    >
      <h3>{{ product.name }}</h3>
      <p>{{ product.description }}</p>
      <p><strong>₺{{ product.price }}</strong></p>
      <p><strong>Stock:</strong> {{ product.quantity }}</p>
      <p v-if="product.user"><strong>Farmer:</strong> {{ product.user.name }}</p>
      
      <input
        type="number"
        v-model.number="quantities[product.id]"
        min="1"
        :max="product.quantity"
        placeholder="Quantity"
      />
      <button @click="addToCart(product)">Add to Cart</button>
    </div>
    
    <!-- Empty state when no products -->
    <div v-if="!products.length" class="empty-state">
      <p>No products available.</p>
    </div>
  </div>
</section>
<section v-if="section === 'cart' && !loading" class="cart-page">
  <div class="cart-header">
    <h2>Your Cart</h2>
    <button 
      v-if="cart.length > 0" 
      @click="clearCart" 
      class="clear-cart-btn"
    >
      Clear Cart
    </button>
  </div>

  <div v-if="cart.length === 0" class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <circle cx="9" cy="21" r="1"></circle>
      <circle cx="20" cy="21" r="1"></circle>
      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
    </svg>
    <p>Your cart is empty.</p>
    <button @click="switchSection('market')" class="continue-shopping-btn">
      Continue Shopping
    </button>
  </div>

  <div v-else class="cart-items">
    <div v-for="item in cart" :key="item.product_id" class="cart-item">
      <div class="item-info">
        <h3>{{ item.name }}</h3>
        <p class="item-description">{{ item.description }}</p>
        <p class="unit-price">Unit Price: <strong>₺{{ item.unit_price }}</strong></p>
      </div>

      <div class="item-controls">
        <div class="quantity-controls">
          <label>Quantity:</label>
          <div class="quantity-input-group">
            <button 
              @click="updateCartQuantity(item.product_id, item.quantity - 1)"
              class="quantity-btn decrease"
              :disabled="item.quantity <= 1"
            >
              -
            </button>
            <input 
              type="number" 
              :value="item.quantity"
              @change="updateCartQuantity(item.product_id, parseInt($event.target.value))"
              min="1"
              class="quantity-input"
            />
            <button 
              @click="updateCartQuantity(item.product_id, item.quantity + 1)"
              class="quantity-btn increase"
            >
              +
            </button>
          </div>
        </div>

        <div class="item-total">
          <p class="total-price">Total: <strong>₺{{ item.total_price }}</strong></p>
        </div>

        <button 
          @click="removeFromCart(item.product_id)" 
          class="remove-btn"
          title="Remove from cart"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3,6 5,6 21,6"></polyline>
            <path d="m19,6v14a2,2 0,0 1,-2,2H7a2,2 0,0 1,-2-2V6m3,0V4a2,2 0,0 1,2-2h4a2,2 0,0 1,2,2v2"></path>
            <line x1="10" y1="11" x2="10" y2="17"></line>
            <line x1="14" y1="11" x2="14" y2="17"></line>
          </svg>
          Remove
        </button>
      </div>
    </div>

    <div class="cart-summary">
      <div class="summary-details">
        <p class="item-count">Items: {{ cart.length }}</p>
        <p class="total-value">
          <strong>Total Cart Value: ₺{{ totalCartValue }}</strong>
        </p>
      </div>
      <div class="cart-actions">
        <button @click="switchSection('market')" class="continue-shopping-btn">
          Continue Shopping
        </button>
        <button @click="checkout" class="checkout-btn">
          Proceed to Payment
        </button>
      </div>
    </div>
  </div>
</section>


      <!-- Orders Section -->
      <section v-if="section === 'orders' && !loading" class="card-section">
        <h2>Your Orders</h2>
        <div v-if="orders.length" class="orders-grid">
          <div class="card" v-for="order in orders" :key="order.id">
            <div class="order-header">
              <p><strong>Order #{{ order.id }}</strong></p>
              <span class="status-badge" :class="order.status.toLowerCase()">{{ order.status }}</span>
            </div>
            <p class="order-date">{{ new Date(order.created_at).toLocaleString() }}</p>
            <p class="order-total"><strong>Total:</strong> {{ order.total }} ₺</p>
            <ul v-if="order.items && order.items.length" class="order-items">
              <li v-for="(item, index) in order.items" :key="index">
                {{ item.product.name }} – {{ item.quantity }} x {{ item.price }} ₺
              </li>
            </ul>
          </div>
        </div>
        <div v-else class="empty-state">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
          </svg>
          <p>No orders found.</p>
        </div>
      </section>

      <!-- Feedback Section -->
      <section v-if="section === 'feedback' && !loading" class="card-section">
        <h2>Feedback</h2>
        <div class="feedback-content">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
          </svg>
          <p>Leave a review or read what others say about your experience.</p>
        </div>
      </section>
    </main>
  </div>
</template>



<script setup>

import avatar from '@/assets/avatar.png'
import ProfileModal from '@/components/ProfileModal.vue'

import { ref, onMounted, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

// Set base URL
axios.defaults.baseURL = 'http://127.0.0.1:8000'


// Router
const router = useRouter()

// Section tracking (optional)
const section = ref('market')

// UI states
const loading = ref(false)
const globalError = ref(null)
const messageError = ref(null)
const isLoadingProfile = ref(true)
const settingsOpen = ref(false)
const profileModalOpen = ref(false)

// Auth headers
function setAuthToken() {
  const token = localStorage.getItem('auth_token') || localStorage.getItem('token')
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  } else {
    delete axios.defaults.headers.common['Authorization']
  }
}
setAuthToken()

// User profile state
const userProfile = ref({
  name: '',
  email: '',
  avatar_url: null
})

// Fetch user profile
const fetchUserProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) return

    const response = await axios.get('http://127.0.0.1:8000/api/user', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })

    // ✅ Update the userProfile reactive variable
    userProfile.value = response.data
  } catch (error) {
    console.error('Failed to fetch profile', error)
  }
}


onMounted(fetchUserProfile)

// UI Actions
const toggleSettings = () => {
  settingsOpen.value = !settingsOpen.value
}
const openProfile = () => {
  settingsOpen.value = false
  profileModalOpen.value = true
}
const closeProfile = () => {
  profileModalOpen.value = false
}
const handleLogout = async () => {
  try {
    await axios.post('/api/logout')
    localStorage.removeItem('token')
    router.push('/login')
  } catch (error) {
    alert('Logout failed. Please try again.')
  }
}

// Handle updated profile from modal
const onProfileUpdated = (newAvatarUrl, newName) => {
  if (newAvatarUrl) userProfile.value.avatar_url = newAvatarUrl
  if (newName) userProfile.value.name = newName
  profileModalOpen.value = false
}

// Derived display name
const firstName = computed(() => {
  if (!userProfile.value.name) return 'User'
  return userProfile.value.name.split(' ')[0]
})

// Avatar fallback initial
const userInitial = computed(() => {
  if (!userProfile.value.name) return 'U'
  return userProfile.value.name.charAt(0).toUpperCase()
})

// Error handler utility
const handleApiError = (error, context = 'Operation') => {
  console.error(`${context} error:`, error)
  let errorMessage = `${context} failed.`

  if (error.response) {
    const status = error.response.status
    const data = error.response.data
    switch (status) {
      case 400: errorMessage = data.message || 'Bad request.'; break
      case 401:
        errorMessage = 'Unauthorized. Please log in again.'
        localStorage.removeItem('auth_token')
        localStorage.removeItem('token')
        router.push('/login')
        return
      case 403: errorMessage = 'Forbidden.'; break
      case 404: errorMessage = 'Not found.'; break
      case 422: errorMessage = data.message || 'Validation error.'; break
      case 500: errorMessage = 'Server error.'; break
      default: errorMessage = data.message || `Error (${status}).`
    }
  } else if (error.request) {
    errorMessage = 'Network error.'
  }

  return errorMessage
}

// --- Products & Cart ---
const products = ref([])
const cart = ref([])
const orders = ref([])
const quantities = ref({})
const fetchProducts = async () => {
  loading.value = true
  try {
    setAuthToken()
    const res = await axios.get('/api/products')

    // ✅ FIX HERE
    products.value = Array.isArray(res.data.data)
      ? res.data.data
      : []

  } catch (error) {
    const errorMsg = handleApiError(error, 'Failed to fetch products')
    globalError.value = errorMsg

    // ✅ fallback
    products.value = []
  } finally {
    loading.value = false
  }
}

const totalCartValue = computed(() => {
  if (!cart.value || cart.value.length === 0) return 0;
  const total = cart.value.reduce((total, item) => {
    const itemTotal = item.total_price || (item.unit_price * item.quantity) || 0;
    return total + itemTotal;
  }, 0);
  return parseFloat(total.toFixed(2));
});


const fetchCart = async () => {
  loading.value = true
  globalError.value = null

  try {
    setAuthToken()
    const res = await axios.get('/api/consumer/cart')
    cart.value = res.data.cart || []
  } catch (error) {
    globalError.value = handleApiError(error, 'Failed to fetch cart')
    cart.value = []
  } finally {
    loading.value = false
  }
}

const addToCart = async (product) => {
  const quantity = quantities.value[product.id] || 1

  try {
    setAuthToken()
   await axios.post('/api/consumer/cart', {
  product_id: product.id,
  quantity
})

    alert('Added to cart successfully!')
    quantities.value[product.id] = 1
  } catch (error) {
    globalError.value = handleApiError(error, 'Failed to add to cart')
  }
}


// Remove item from cart by productId
const removeFromCart = async (productId) => {
  const cartItem = cart.value.find(item => item.product_id === productId);
  if (!cartItem) {
    alert('Item not found in cart');
    return;
  }

  try {
    setAuthToken();
    await axios.delete(`/api/consumer/cart/${cartItem.id}`);
    await fetchCart();
    alert('Item removed from cart successfully!');
  } catch (error) {
    globalError.value = handleApiError(error, 'Failed to remove item from cart');
  }
};

// Update cart quantity by productId and newQuantity
const updateCartQuantity = async (productId, newQuantity) => {
  if (newQuantity < 1) {
    await removeFromCart(productId);
    return;
  }

  const cartItem = cart.value.find(item => item.product_id === productId);
  if (!cartItem) {
    alert('Item not found in cart');
    return;
  }

  try {
    setAuthToken();
    await axios.put(`/api/consumer/cart/${cartItem.id}`, { quantity: newQuantity });
    await fetchCart();
  } catch (error) {
    globalError.value = handleApiError(error, 'Failed to update cart quantity');
    await fetchCart();
  }
};

// Optimistic UI update for quantity change (with rollback)
const updateCartQuantityOptimistic = async (productId, newQuantity) => {
  if (newQuantity < 1) {
    await removeFromCart(productId);
    return;
  }

  const itemIndex = cart.value.findIndex(item => item.product_id === productId);
  if (itemIndex === -1) return;

  const cartItem = cart.value[itemIndex];
  const originalQuantity = cartItem.quantity;
  const originalTotalPrice = cartItem.total_price;

  // Optimistically update UI
  cart.value[itemIndex].quantity = newQuantity;
  cart.value[itemIndex].total_price = cart.value[itemIndex].unit_price * newQuantity;

  try {
    setAuthToken();
    await axios.put(`/api/consumer/cart/${cartItem.id}`, { quantity: newQuantity });
  } catch (error) {
    // Rollback on failure
    cart.value[itemIndex].quantity = originalQuantity;
    cart.value[itemIndex].total_price = originalTotalPrice;

    globalError.value = handleApiError(error, 'Failed to update cart quantity');
  }
};

// Helper to increment or decrement quantity
const changeCartQuantityBy = async (productId, incrementBy) => {
  const cartItem = cart.value.find(item => item.product_id === productId);
  if (!cartItem) {
    alert('Item not found in cart');
    return;
  }

  const newQuantity = cartItem.quantity + incrementBy;

  if (newQuantity < 1) {
    await removeFromCart(productId);
  } else {
    await updateCartQuantity(productId, newQuantity);
  }
};
const clearCart = async () => {
  if (!confirm('Are you sure you want to clear your entire cart?')) {
    return;
  }

  try {
    setAuthToken();
    await axios.delete('/api/consumer/cart/clear');
    cart.value = [];
    alert('Cart cleared successfully!');
  } catch (error) {
    globalError.value = handleApiError(error, 'Failed to clear cart');
    await fetchCart(); // refresh cart in case of error
  }
}

const fetchOrders = async () => {
  loading.value = true
  try {
    setAuthToken()
    const res = await axios.get('/api/orders')
    orders.value = res.data || []
  } catch (error) {
    globalError.value = handleApiError(error, 'Failed to fetch orders')
    orders.value = []
  } finally {
    loading.value = false
  }
}


const checkout = () => {
  alert('Redirecting to payment...')
}

const switchSection = async (target) => {
  section.value = target
  globalError.value = null

  switch (target) {
    case 'market':
      await fetchProducts()
      break
    case 'cart':
      await fetchCart()
      break
    case 'orders':
      await fetchOrders()
      break
    case 'messages':
      await fetchConversations()
      break
  }
}

// --- Lifecycle ---
onMounted(async () => {
  const token = localStorage.getItem('token') || localStorage.getItem('token')
  if (!token) {
    alert('Please log in.')
    router.push('/login')
    return
  }

  setAuthToken()

  const storedUserId = localStorage.getItem('user_id')
  if (storedUserId) {
    userId.value = parseInt(storedUserId)
    await fetchUserProfile()
  }

  await fetchProducts()
  await fetchCart()
  await fetchOrders()
})
</script>



<style scoped>
.consumer-app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background: #f8fafc;
  color: #1e293b;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* --- Header --- */
.header {
  background: linear-gradient(135deg, #4ade80, #22c55e);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  font-weight: 600;
  color: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 20;
}

.greeting {
  flex: 1;
  text-align: center;
  font-size: 1.1rem;
  letter-spacing: 0.5px;
}

.menu-icon, .user-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  transition: background-color 0.2s;
}

.menu-icon:hover, .user-icon:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.avatar-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: white;
  color: #22c55e;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.9rem;
}

/* --- Search Bar --- */
.search-bar {
  padding: 0.8rem 1.5rem;
  background: white; 
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 10;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 40px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  outline: none;
  font-size: 0.95rem;
  transition: all 0.2s;
  background-color: #f8fafc;
}

.search-bar input:focus {
  border-color: #a5b4fc;
  box-shadow: 0 0 0 3px rgba(165, 180, 252, 0.2);
  background-color: white;
}

/* --- Bottom Navigation --- */
.bottom-nav {
  display: flex;
  justify-content: space-around;
  background: white;
  padding: 0.8rem 0;
  border-top: 1px solid #f1f5f9;
  position: fixed;
  bottom: 0;
  width: 100%;
  z-index: 20;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  color: #64748b;
  font-size: 0.7rem;
  gap: 4px;
}

.nav-item svg {
  width: 24px;
  height: 24px;
  stroke-width: 1.8;
}

.nav-item.active {
  color: #4f46e5;
  background-color: #eef2ff;
}

.nav-item.active svg {
  stroke: #4f46e5;
}

.nav-item span {
  font-size: 0.7rem;
  font-weight: 500;
}

/* --- Main Content --- */
.main-view {
  padding: 1.5rem;
  padding-bottom: 5.5rem;
  flex: 1;
  overflow-y: auto;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

/* --- Section Headers --- */
.card-section h2,
.messages-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.5rem;
  position: relative;
  padding-bottom: 0.5rem;
}

.card-section h2::after,
.messages-header h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 50px;
  height: 3px;
  background: linear-gradient(90deg, #4ade80, #3b82f6);
  border-radius: 3px;
}

.card-section {
  padding: 2rem;
  background: #f9f9f9;
}

.product-grid {
  display: flex;
  flex-wrap: wrap;              
  gap: 1rem;                    
  justify-content: flex-start;  
}

.product-card {
  flex: 0 0 260px;              
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 1rem;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  box-sizing: border-box;
}

.product-card input,
.product-card button {
  width: 100%;
  margin-top: 0.5rem;
}

.product-card button {
  background-color: #22c55e;
  color: white;
  padding: 0.5rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.product-card button:hover {
  background-color: #16a34a;
}

.empty-state {
  width: 100%;
  text-align: center;
  padding: 2rem;
  color: #666;
}

.cart-page {
  padding: 2rem;
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.clear-cart-btn {
  background-color: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}

.clear-cart-btn:hover {
  background-color: #dc2626;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.cart-item {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.item-info {
  flex: 1;
}

.item-info h3 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
}

.item-description {
  color: #6b7280;
  margin: 0.25rem 0;
}

.unit-price {
  color: #374151;
  margin: 0.25rem 0;
}

.item-controls {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: flex-end;
}

.quantity-controls {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.quantity-input-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quantity-btn {
  background-color: #f3f4f6;
  border: 1px solid #d1d5db;
  width: 32px;
  height: 32px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.quantity-btn:hover:not(:disabled) {
  background-color: #e5e7eb;
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity-input {
  width: 60px;
  text-align: center;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  padding: 0.25rem;
}

.total-price {
  color: #059669;
  font-size: 1.1rem;
  margin: 0;
}

.remove-btn {
  background-color: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.remove-btn:hover {
  background-color: #dc2626;
}

.cart-summary {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 1.5rem;
  margin-top: 1rem;
}

.summary-details {
  margin-bottom: 1rem;
}

.item-count {
  color: #6b7280;
  margin: 0 0 0.5rem 0;
}

.total-value {
  color: #059669;
  font-size: 1.25rem;
  margin: 0;
}

.cart-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.continue-shopping-btn {
  background-color: #6b7280;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
}

.continue-shopping-btn:hover {
  background-color: #4b5563;
}

.checkout-btn {
  background-color: #059669;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.checkout-btn:hover {
  background-color: #047857;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

.empty-state svg {
  margin-bottom: 1rem;
  color: #d1d5db;
}

/* Responsive Design */
@media (max-width: 768px) {
  .cart-item {
    flex-direction: column;
    align-items: stretch;
  }
  
  .item-controls {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
  
  .cart-actions {
    flex-direction: column;
  }
}

/* --- Orders Section --- */
.orders-grid {
  display: grid;
  gap: 1.2rem;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.order-header p {
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.status-badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.6rem;
  border-radius: 12px;
  font-weight: 500;
}

.status-badge.completed {
  background-color: #dcfce7;
  color: #166534;
}

.status-badge.pending {
  background-color: #fef9c3;
  color: #854d0e;
}

.status-badge.cancelled {
  background-color: #fee2e2;
  color: #991b1b;
}

.order-date {
  font-size: 0.8rem;
  color: #64748b;
  margin-bottom: 0.5rem;
}

.order-total {
  font-weight: 500;
  color: #1e293b;
  margin-bottom: 0.8rem;
}

.order-items {
  margin-top: 0.8rem;
  padding-left: 1rem;
  font-size: 0.85rem;
  color: #475569;
}

.order-items li {
  list-style: disc;
  margin-bottom: 0.3rem;
}

/* --- Messages Section --- */
.consumer-dashboard-messages {
  height: calc(100vh - 180px);
  display: flex;
  flex-direction: column;
  background: white;
  border-radius: 14px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  overflow: hidden;
  border: 1px solid #f1f5f9;
}

.messages-header {
  padding: 1.2rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.btn-new-message {
  background: linear-gradient(135deg, #4ade80, #3b82f6);
  color: white;
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-new-message:hover {
  transform: scale(1.05);
}

.messages-container {
  display: flex;
  flex: 1;
  height: 100%;
}

.conversations-list {
  width: 280px;
  border-right: 1px solid #f1f5f9;
  overflow-y: auto;
  background: #f8fafc;
}

.conversations-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.conversations-list li {
  display: flex;
  align-items: center;
  padding: 0.8rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
  border-bottom: 1px solid #f1f5f9;
}

.conversations-list li:hover {
  background-color: #f1f5f9;
}

.conversations-list li.active {
  background-color: white;
  border-right: 3px solid #4f46e5;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: white;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  font-size: 0.9rem;
}

.conversation-info {
  flex: 1;
  min-width: 0;
}

.conversation-info strong {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.last-message-preview {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0.2rem 0 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.unread-count {
  background: #ef4444;
  color: white;
  font-size: 0.7rem;
  padding: 0.15rem 0.4rem;
  border-radius: 12px;
  min-width: 20px;
  text-align: center;
  font-weight: 500;
}

.messages-pane {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
}

.conversation-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
}

.conversation-user {
  display: flex;
  align-items: center;
  gap: 12px;
}

.conversation-header h3 {
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
  color: #1e293b;
}

.online-status {
  font-size: 0.75rem;
  color: #10b981;
  display: flex;
  align-items: center;
  gap: 4px;
}

.online-status::before {
  content: '';
  display: block;
  width: 8px;
  height: 8px;
  background-color: #10b981;
  border-radius: 50%;
}

.messages-list {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  background-color: #f8fafc;
}

.message-item {
  max-width: 75%;
  padding: 0.8rem 1rem;
  border-radius: 18px;
  word-wrap: break-word;
  position: relative;
  line-height: 1.4;
  font-size: 0.95rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.message-item.sent {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  align-self: flex-end;
  border-bottom-right-radius: 4px;
}

.message-item.received {
  background: white;
  color: #1e293b;
  align-self: flex-start;
  border-bottom-left-radius: 4px;
  border: 1px solid #e2e8f0;
}

.message-time {
  display: block;
  margin-top: 0.3rem;
  font-size: 0.7rem;
  opacity: 0.8;
}

.message-item.sent .message-time {
  color: rgba(255, 255, 255, 0.8);
  text-align: right;
}

.message-item.received .message-time {
  color: #64748b;
}

.message-input-form {
  padding: 1rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  background: white;
}

.input-wrapper {
  display: flex;
  align-items: flex-end;
  gap: 0.8rem;
}

.message-input-form textarea {
  flex: 1;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.8rem 1rem;
  resize: none;
  outline: none;
  font-size: 0.95rem;
  min-height: 44px;
  max-height: 120px;
  line-height: 1.4;
  background-color: #f8fafc;
  transition: all 0.2s;
}

.message-input-form textarea:focus {
  border-color: #a5b4fc;
  box-shadow: 0 0 0 3px rgba(165, 180, 252, 0.2);
  background-color: white;
}

.send-button {
  background: linear-gradient(135deg, #4ade80, #3b82f6);
  color: white;
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.send-button:hover {
  transform: translateY(-1px);
}

.send-button:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
  transform: none;
}

.sending-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.message-error {
  margin-top: 0.8rem;
  padding: 0.6rem 0.8rem;
  background-color: #fee2e2;
  color: #b91c1c;
  border-radius: 8px;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 6px;
}

.message-error svg {
  flex-shrink: 0;
}

.retry-btn {
  margin-left: auto;
  background: none;
  border: none;
  color: #b91c1c;
  font-weight: 500;
  cursor: pointer;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
}

.retry-btn:hover {
  background-color: rgba(185, 28, 28, 0.1);
}

.no-conversation-selected {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #64748b;
  padding: 2rem;
  text-align: center;
}

.no-conversation-selected svg {
  margin-bottom: 1rem;
  color: #cbd5e1;
}

.no-conversation-selected p {
  font-size: 1rem;
  max-width: 300px;
  line-height: 1.5;
}

/* --- Loading Spinner --- */
.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  gap: 1rem;
}

.loading-spinner p {
  color: #64748b;
  font-size: 0.95rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-radius: 50%;
  border-top-color: #4f46e5;
  animation: spin 1s linear infinite;
}

/* --- Error Alert --- */
.error-alert {
  padding: 0.8rem 1rem;
  background-color: #fee2e2;
  color: #b91c1c;
  border-radius: 8px;
  margin: 1rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  animation: slideIn 0.3s ease-out;
}

.error-icon {
  flex-shrink: 0;
}

.close-error {
  margin-left: auto;
  background: none;
  border: none;
  color: #b91c1c;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0 0.2rem;
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* --- Responsive Adjustments --- */
@media (max-width: 768px) {
  .messages-container {
    flex-direction: column;
  }
  
  .conversations-list {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #f1f5f9;
    max-height: 200px;
  }
  
  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  }
  
  .main-view {
    padding: 1rem;
    padding-bottom: 5.5rem;
  }
}

@media (max-width: 480px) {
  .header {
    padding: 0.8rem 1rem;
  }
  
  .greeting {
    font-size: 1rem;
  }
  
  .search-bar {
    padding: 0.6rem 1rem;
  }
  
  .product-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.8rem;
  }
  
  .product-card {
    padding: 1rem;
  }
}
.dashboard-header {
  position: relative;
  padding: 1rem;
  display: flex;
  align-items: center;
}
.settings-wrapper img {
  width: 32px;       /* small size */
  height: 32px;
  border-radius: 50%; /* make it circular */
  object-fit: cover;  /* keep aspect ratio and crop if needed */
  cursor: pointer;    /* indicate clickable */
  border: 1.5px solid #ccc; /* optional subtle border */
  transition: transform 0.2s ease;
}

.settings-wrapper img:hover {
  transform: scale(1.1);
}


.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  /* Optional shadow or border */
}

.settings-menu {
  position: absolute;
  top: 60px; /* just below the header */
  left: 1rem;
  background: black;
  border: 1px solid #ddd;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border-radius: 6px;
  width: 150px;
  z-index: 1000;
}

.settings-menu ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.settings-menu li {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #eee;
}

.settings-menu li:hover {
  background-color: #f4f4f4;
}

.settings-menu li:last-child {
  border-bottom: none;
}


</style>