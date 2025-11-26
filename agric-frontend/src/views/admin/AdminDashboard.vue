<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo-container">
        <div class="logo">
          <i class="fas fa-leaf"></i>
          <span>FASI<span>MARKET</span></span>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li 
            @click="switchSection('overview')" 
            :class="{ active: section === 'overview' }"
            class="nav-item"
          >
            <i class="icon"><i class="fas fa-tachometer-alt"></i></i>
            <span class="nav-text">Dashboard</span>
          </li>
          <li 
            @click="switchSection('users')" 
            :class="{ active: section === 'users' }"
            class="nav-item"
          >
            <i class="icon"><i class="fas fa-users"></i></i>
            <span class="nav-text">Users</span>
          </li>
          <li 
            @click="switchSection('products')" 
            :class="{ active: section === 'products' }"
            class="nav-item"
          >
            <i class="icon"><i class="fas fa-seedling"></i></i>
            <span class="nav-text">Products</span>
          </li>
          <li 
            @click="switchSection('feedback')" 
            :class="{ active: section === 'feedback' }"
            class="nav-item"
          >
            <i class="icon"><i class="fas fa-comments"></i></i>
            <span class="nav-text">Feedback</span>
          </li>
          <li 
            class="nav-item logout" 
            @click="handleLogout"
          >
            <i class="icon"><i class="fas fa-sign-out-alt"></i></i>
            <span class="nav-text">Logout</span>
          </li>
        </ul>
      </nav>
      <div class="sidebar-footer">
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
    <!-- Dashboard Header -->
  <header class="dashboard-header">
  <div class="greeting">
    <h2>👋 Hello, {{ currentUser?.name || 'Admin' }}</h2>
    <p>
      Welcome back to your admin panel 
      <span v-if="currentUser?.role">— Role: {{ currentUser.role }}</span>
    </p>
  </div>

  <div class="user-profile" @click="openProfileModal" title="Click to update your profile">
    <img
      v-if="currentUser?.avatar_url"
      :src="currentUser.avatar_url"
      alt="Admin Avatar"
      class="profile-picture clickable"
    />
    <div v-else class="profile-picture placeholder">👤</div>
  </div>
</header>

<!-- Scrollable Main Content -->
  <ProfileModal
    v-if="profileModalOpen"
    @close="closeProfileModal"
    @updated="onProfileUpdated"
  />



      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading dashboard data...</p>
      </div>

      <!-- Dashboard Content -->
      <div v-else class="content-area">
        <!-- Overview Section -->
        <section v-if="section === 'overview'" class="overview">
          <!-- Stats Cards -->
          <div class="stats-row">
            <div class="stat-card revenue" @click="switchSection('overview')">
              <div class="card-icon">
                <i class="fas fa-coins"></i>
              </div>
              <div class="card-content">
                <h3>Recurring Revenue</h3>
                <p class="value">₺{{ formatNumber(monthlyRevenue) }}</p>
                <div class="trend-indicator" :class="revenueGrowth >= 0 ? 'up' : 'down'">
                  <i class="fas" :class="revenueGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                </div>
              </div>
            </div>
            
            <div class="stat-card products" @click="switchSection('products')">
              <div class="card-icon">
                <i class="fas fa-box-open"></i>
              </div>
              <div class="card-content">
                <h3>Products</h3>
                <p class="value">{{ productCount }}</p>
                <div class="trend-indicator" :class="accountGrowth >= 0 ? 'up' : 'down'">
                  <i class="fas" :class="accountGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                </div>
              </div>
            </div>
            
            <div class="stat-card users" @click="switchSection('users')">
              <div class="card-icon">
                <i class="fas fa-user-friends"></i>
              </div>
              <div class="card-content">
                <h3>Active Users</h3>
                <p class="value">{{ userCount }}</p>
                <div class="trend-indicator" :class="userGrowth >= 0 ? 'up' : 'down'">
                  <i class="fas" :class="userGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                </div>
              </div>
            </div>
            
            <div class="stat-card onboarding" @click="switchSection('products')">
              <div class="card-icon">
                <i class="fas fa-rocket"></i>
              </div>
              <div class="card-content">
                <h3>Onboarding Revenue</h3>
                <p class="value">₺{{ formatNumber(onboardingRevenue) }}</p>
                <div class="trend-indicator" :class="onboardingGrowth >= 0 ? 'up' : 'down'">
                  <i class="fas" :class="onboardingGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Row -->
          <div class="charts-row">
            <!-- Revenue Chart -->
            <div class="chart-container">
              <div class="chart-header">
                <h3>Monthly Revenue</h3>
                <div class="chart-actions">
                  <button class="chart-action-btn">
                    <i class="fas fa-download"></i>
                  </button>
                </div>
              </div>
              <div class="chart-wrapper">
                <canvas ref="mrrChartCanvas"></canvas>
              </div>
            </div>

            <!-- Health Doughnut Chart -->
            <div class="chart-container health-chart">
              <div class="chart-header">
                <h3>Farm Health Status</h3>
              </div>
              <div class="doughnut-wrapper">
                <div
                  class="doughnut-chart"
                  :style="`--value: ${healthStrongPercent}%`"
                ></div>
                <div class="doughnut-label">
                  <span class="label-value">{{ healthStrongPercent }}%</span>
                  <span class="label-text">Healthy Farms</span>
                </div>
              </div>
              <div class="legend">
                <div class="legend-item">
                  <span class="indicator strong"></span>
                  <span>Strong</span>
                </div>
                <div class="legend-item">
                  <span class="indicator weak"></span>
                  <span>Needs Attention</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity Table -->
          <div class="table-section">
            <div class="table-header">
              <h3>Recent Activity</h3>
              <button class="view-all-btn">
                View All <i class="fas fa-chevron-right"></i>
              </button>
            </div>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Stage</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="activity in recentActivities" :key="activity.id">
                    <td>
                      <div class="user-info">
                        <div class="user-avatar">
                          <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-details">
                          <p class="user-name">{{ activity.title || 'Unknown' }}</p>
                          <p class="user-role">Farmer</p>
                        </div>
                      </div>
                    </td>
                    <td>{{ activity.description || 'No stage' }}</td>
                    <td>
                      <span class="status-badge completed">Completed</span>
                    </td>
                    <td>
                      <button class="action-btn">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="recentActivities.length === 0">
                    <td colspan="4" class="no-data">
                      <i class="fas fa-inbox"></i>
                      <p>No activity found</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- Dynamic Sections -->
        <section v-if="section === 'users'" class="section-container">
          <ManageUsers />
        </section>
        <section v-if="section === 'products'" class="section-container">
          <ManageProducts />
        </section>
        <section v-if="section === 'feedback'" class="section-container">
          <ViewFeedback />
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, onUnmounted, watch } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import ManageUsers from './ManageUsers.vue'
import ManageProducts from './ManageProducts.vue'
import ViewOrders from './ViewOrders.vue'
import ViewFeedback from './ViewFeedback.vue'

const notifications = ref([])
const unreadCount = ref(0)
// Register Chart.js components
Chart.register(...registerables)

// Reactive state
const section = ref('overview')
const filterDate = ref('') // Will hold YYYY-MM format
const loading = ref(false)

// Dashboard data
const userCount = ref(0)
const productCount = ref(0)
const monthlyRevenue = ref(0)
const revenueGrowth = ref(0)
const accountGrowth = ref(0)
const userGrowth = ref(0)
const onboardingRevenue = ref(0)
const onboardingGrowth = ref(0)
const recentActivities = ref([])
const healthStrongPercent = ref(75)

// Chart
const mrrChartCanvas = ref(null)
let mrrChart = null
const chartLabels = ref([])
const chartData = ref([])

// Methods
const switchSection = (newSection) => {
  section.value = newSection
  nextTick(() => {
    document.querySelector('.main-content')?.scrollTo({ top: 0, behavior: 'smooth' })
  })
}

const getAuthHeaders = () => {
  const token = localStorage.getItem('token')
  return {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json'
  }
}

const formatNumber = (num) => {
  return Number(num || 0).toLocaleString('tr-TR')
}

// Format date to YYYY-MM for filtering
const getMonthFromFilter = () => {
  if (filterDate.value) {
    return filterDate.value // Already in YYYY-MM format
  }
  // Default to current month
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
}

// Fetch dashboard stats for selected month
const fetchDashboardData = async () => {
  loading.value = true
  const month = getMonthFromFilter()

  try {
    const response = await axios.get(
      `http://127.0.0.1:8000/api/admin/dashboard-stats?month=${month}`,
      { headers: getAuthHeaders() }
    )
    const data = response.data || {}

    userCount.value = data.user_count || 0
    productCount.value = data.product_count || 0
    monthlyRevenue.value = data.monthly_revenue || 0
    revenueGrowth.value = data.revenue_growth_percentage || 0
    accountGrowth.value = data.account_growth_percentage || 0
    userGrowth.value = data.user_growth_percentage || 0
    onboardingRevenue.value = data.onboarding_revenue || 0
    onboardingGrowth.value = data.onboarding_growth_percentage || 0
    recentActivities.value = Array.isArray(data.recent_activities) ? data.recent_activities : []
    healthStrongPercent.value = data.health_strong_percentage || 75
  } catch (err) {
    console.error('Failed to fetch dashboard data:', err)
    alert('Could not load dashboard data.')
  } finally {
    loading.value = false
  }
}

// Fetch MRR chart data (can also filter by month if needed)
const fetchChartData = async () => {
  try {
    const response = await axios.get(
      'http://127.0.0.1:8000/api/admin/mrr-over-time',
      { headers: getAuthHeaders() }
    )

    const data = response.data?.data || []
    if (data.length > 0) {
      chartLabels.value = data.map(item => item.month)
      chartData.value = data.map(item => item.value)
    } else {
      fallbackChartData()
    }
  } catch (err) {
    console.error('Failed to load chart data:', err)
    fallbackChartData()
  }

  await nextTick()
  renderChart()
}

const fallbackChartData = () => {
  chartLabels.value = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ]
  chartData.value = [32000, 34000, 38000, 40000, 39000, 42000, 0, 0, 0, 0, 0, 0]
}

const renderChart = () => {
  if (mrrChart) mrrChart.destroy()

  const fullMonths = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ]

  const dataMap = new Map(fullMonths.map(month => [month, 0]))
  chartLabels.value.forEach((label, i) => {
    const month = label.trim()
    if (dataMap.has(month)) dataMap.set(month, chartData.value[i])
  })

  const finalLabels = fullMonths
  const finalData = fullMonths.map(m => dataMap.get(m))

  const ctx = mrrChartCanvas.value?.getContext('2d')
  if (!ctx) return

  // Create gradient for chart
  const gradient = ctx.createLinearGradient(0, 0, 0, 300)
  gradient.addColorStop(0, 'rgba(76, 175, 80, 0.8)')
  gradient.addColorStop(1, 'rgba(76, 175, 80, 0.2)')

  mrrChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: finalLabels,
      datasets: [{
        label: 'MRR (₺)',
        data: finalData,
        backgroundColor: gradient,
        borderColor: '#4caf50',
        borderWidth: 2,
        borderRadius: 6,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { 
          display: true, 
          position: 'top',
          labels: {
            usePointStyle: true,
            padding: 20,
            font: {
              family: "'Inter', sans-serif",
              size: 12
            }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          titleColor: '#2d3748',
          bodyColor: '#4a5568',
          borderColor: '#e2e8f0',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            label: (ctx) => `₺${ctx.parsed.y.toLocaleString('tr-TR')}`
          }
        }
      },
      scales: {
        x: {
          grid: { 
            display: false,
            drawBorder: false
          },
          ticks: { 
            autoSkip: false,
            font: {
              family: "'Inter', sans-serif",
              size: 11
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: { 
            borderDash: [3, 3], 
            color: '#e8f5e8',
            drawBorder: false
          },
          ticks: {
            callback: (value) => `₺${value.toLocaleString('tr-TR')}`,
            font: {
              family: "'Inter', sans-serif",
              size: 11
            },
            padding: 10
          }
        }
      }
    }
  })
}



const showNotifications = ref(false)

// Close dropdown when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    const bell = document.querySelector('.notification-badge')
    const dropdown = document.querySelector('.notifications-dropdown')
    if (!bell?.contains(e.target) && !dropdown?.contains(e.target)) {
      showNotifications.value = false
    }
  })
})




const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      console.error('No token found');
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
  } catch (error) {
    console.error('Failed to fetch user profile:', error);
    alert('Could not load your profile. Please log in again.');
    localStorage.removeItem('token');
    window.location.href = '/login';
  }
};

import ProfileModal from '@/components/ProfileModal.vue'


const currentUser = ref(null)


// Fetch admin data (with Sanctum auth)
onMounted(async () => {
  try {
    const response = await axios.get('/api/admin/me', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    currentUser.value = response.data
  } catch (error) {
    console.error('Failed to fetch admin data:', error)
  }
})
onMounted(() => {
  // Set default to current month
  const now = new Date()
  filterDate.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`

  fetchDashboardData()
  fetchChartData()
})
const showProfileMenu = ref(false)

const toggleProfileMenu = (e) => {
  e.stopPropagation()
  showProfileMenu.value = !showProfileMenu.value
}

const goToProfile = () => {
  alert('Go to profile page')
  // router.push('/profile') if using Vue Router
}

// `handleLogout` already exists — reuse it

document.addEventListener('click', () => {
  showProfileMenu.value = false
  showNotifications.value = false
})

const fetchNotifications = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/admin/notifications/unread-count', {
      headers: getAuthHeaders()
    })
    unreadCount.value = res.data.count || 0
  } catch (err) {
    console.error('Failed to fetch notification count')
  }
}

onMounted(() => {
  fetchNotifications()
  // Optional: Poll every 30s
  setInterval(fetchNotifications, 30000)
})


// Profile modal
const profileModalOpen = ref(false)
const openProfileModal = () => profileModalOpen.value = true
const closeProfileModal = () => profileModalOpen.value = false
const onProfileUpdated = (newUserData) => {
  authStore.setUser(newUserData);
  userId.value = newUserData.id; // Sync ID
  avatarUrl.value = newUserData.avatar_url || '/default-avatar.png'; // ← Add this
};


const handleLogout = async () => {
  try {
    await axios.post('http://127.0.0.1:8000/api/logout', {}, { headers: getAuthHeaders() })
  } catch (e) {
    // Ignore error
  } finally {
    localStorage.clear()
    window.location.href = '/login'
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

/* === Base Styles === */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  background-color: #f8faf7;
  color: #2d3748;
  line-height: 1.6;
}

.dashboard-container {
  display: flex;
  min-height: 100vh;
  background: #f8faf7;
}

/* === Sidebar Styles === */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
  color: white;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 20px rgba(46, 125, 50, 0.15);
  flex-shrink: 0;
  height: 100vh;
  position: sticky;
  top: 0;
  overflow-y: auto;
}

.logo-container {
  padding: 1.5rem 1.25rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 700;
  font-size: 1.5rem;
  color: white;
}

.logo i {
  font-size: 1.8rem;
  color: #a5d6a7;
}

.logo span span {
  color: #a5d6a7;
  font-weight: 300;
}

.sidebar-nav {
  flex: 1;
  padding: 1.5rem 0;
}

.sidebar-nav ul {
  list-style: none;
  padding: 0 0.75rem;
}

.nav-item {
  padding: 0.9rem 1rem;
  cursor: pointer;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #c8e6c9;
  transition: all 0.3s ease;
  margin-bottom: 0.4rem;
  font-weight: 500;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  transform: translateX(4px);
}

.nav-item.active {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
}

.nav-item.logout {
  margin-top: 1rem;
  color: #ffcdd2;
}

.nav-item.logout:hover {
  background: rgba(255, 87, 87, 0.1);
  color: #ef5350;
}

.icon {
  font-size: 1.1rem;
  min-width: 24px;
  text-align: center;
}

.nav-text {
  font-size: 0.95rem;
}

.sidebar-footer {
  padding: 1rem 1.25rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.weather-widget {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #c8e6c9;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.weather-widget i {
  font-size: 1.1rem;
}


/* === Stats Cards === */
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 18px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(46, 125, 50, 0.12);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  border-radius: 0 16px 16px 0;
}

.stat-card.revenue::before { background: linear-gradient(to bottom, #4caf50, #2e7d32); }
.stat-card.products::before { background: linear-gradient(to bottom, #ff9800, #f57c00); }
.stat-card.users::before { background: linear-gradient(to bottom, #2196f3, #1976d2); }
.stat-card.onboarding::before { background: linear-gradient(to bottom, #9c27b0, #7b1fa2); }

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-card.revenue .card-icon { background: linear-gradient(135deg, #4caf50, #2e7d32); }
.stat-card.products .card-icon { background: linear-gradient(135deg, #ff9800, #f57c00); }
.stat-card.users .card-icon { background: linear-gradient(135deg, #2196f3, #1976d2); }
.stat-card.onboarding .card-icon { background: linear-gradient(135deg, #9c27b0, #7b1fa2); }

.card-content h3 {
  font-size: 0.9rem;
  color: #718096;
  font-weight: 600;
  margin: 0 0 0.4rem 0;
}

.card-content .value {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 0.4rem;
}

.trend-indicator {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 20px;
  width: fit-content;
}

.trend-indicator.up {
  background: #e8f5e8;
  color: #2e7d32;
}

.trend-indicator.down {
  background: #ffebee;
  color: #c62828;
}

/* === Charts Row === */
.charts-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-container {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 18px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.chart-header h3 {
  font-size: 1.1rem;
  color: #2d3748;
  font-weight: 600;
}

.chart-actions {
  display: flex;
  gap: 0.5rem;
}

.chart-action-btn {
  background: #f1f8e9;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4caf50;
  cursor: pointer;
  transition: all 0.2s ease;
}

.chart-action-btn:hover {
  background: #4caf50;
  color: white;
}

.chart-wrapper {
  height: 250px;
  position: relative;
}

/* Health Chart Specific Styles */
.health-chart {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.doughnut-wrapper {
  width: 140px;
  height: 140px;
  margin: 0.5rem auto 1.5rem;
  position: relative;
}

.doughnut-chart {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: conic-gradient(#4caf50 0% var(--value), #ffcc80 var(--value) 100%);
  box-shadow: 0 4px 10px rgba(76, 175, 80, 0.2);
}

.doughnut-chart::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80px;
  height: 80px;
  background: white;
  border-radius: 50%;
  box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
}

.doughnut-label {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  z-index: 1;
}

.label-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1b5e20;
  display: block;
  line-height: 1.2;
}

.label-text {
  font-size: 0.8rem;
  color: #718096;
  font-weight: 500;
}

.legend {
  display: flex;
  justify-content: center;
  gap: 1.2rem;
  font-size: 0.85rem;
  color: #718096;
  margin-top: 0.5rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.indicator {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.indicator.strong {
  background: #4caf50;
}

.indicator.weak {
  background: #ffcc80;
}

/* === Table Section === */
.table-section {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 18px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.2rem;
}

.table-header h3 {
  font-size: 1.1rem;
  color: #2d3748;
  font-weight: 600;
}

.view-all-btn {
  background: none;
  border: none;
  color: #4caf50;
  font-size: 0.9rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.view-all-btn:hover {
  color: #2e7d32;
  gap: 0.6rem;
}

.table-container {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #e8f5e8;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e8f5e8;
}
.dashboard-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 85px;
  background: linear-gradient(180deg, #2e7d32 0%, #1b5e20 100%);
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
  border-radius: 100%;
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
  background: rgba(248, 249, 249, 0.9);
  backdrop-filter: blur(10px);
  overflow-y: auto;
  color: white;

}

th {
  color: #2e7d32;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #f1f8e9;
  white-space: nowrap;
}

td {
  color: #2d3748;
  font-weight: 500;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  font-size: 1.8rem;
  color: #c8e6c9;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.user-role {
  font-size: 0.8rem;
  color: #718096;
  margin: 0;
}

.status-badge {
  padding: 0.3rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  display: inline-block;
}

.status-badge.completed {
  background: #e8f5e8;
  color: #2e7d32;
}

.action-btn {
  background: #f8faf7;
  border: 1px solid #e8f5e8;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #718096;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: #4caf50;
  color: white;
  border-color: #4caf50;
}

.no-data {
  text-align: center;
  padding: 2rem;
  color: #a0aec0;
}

.no-data i {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  opacity: 0.5;
}

.no-data p {
  margin: 0;
  font-size: 0.9rem;
}

/* === Loading State === */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  color: #1b5e20;
}

.spinner {
  border: 4px solid rgba(76, 175, 80, 0.2);
  border-radius: 50%;
  border-top: 4px solid #4caf50;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* === Section Container === */
.section-container {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 6px 18px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
}

/* === Responsive Design === */
@media (max-width: 1024px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .charts-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    padding: 0.5rem 0;
  }
  
  .logo-container {
    padding: 1rem;
  }
  
  .sidebar-nav ul {
    display: flex;
    overflow-x: auto;
    padding: 0.5rem;
    gap: 0.5rem;
  }
  
  .nav-item {
    min-width: 120px;
    font-size: 0.9rem;
    padding: 0.75rem 1rem;
    margin-bottom: 0;
  }
  
  .sidebar-footer {
    display: none;
  }

  .stats-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .chart-wrapper {
    height: 200px;
  }
}

@media (max-width: 480px) {
  .topbar-actions {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .user-profile {
    align-self: flex-end;
  }
}
</style>