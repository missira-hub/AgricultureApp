<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">Right<span>Success</span></div>
      <nav>
        <ul>
          <li @click="switchSection('overview')" :class="{ active: section === 'overview' }">
            <i class="icon">📊</i> Dashboard
          </li>
          <li @click="switchSection('users')" :class="{ active: section === 'users' }">
            <i class="icon">👥</i> Customer 360
          </li>
          <li @click="switchSection('products')" :class="{ active: section === 'products' }">
            <i class="icon">⚙️</i> Automation
          </li>
          <li @click="switchSection('orders')" :class="{ active: section === 'orders' }">
            <i class="icon">💬</i> Communication
          </li>
          <li @click="switchSection('feedback')" :class="{ active: section === 'feedback' }">
            <i class="icon">📄</i> Report
          </li>
          <li @click="switchSection('settings')" :class="{ active: section === 'settings' }">
            <i class="icon">⚙️</i> Settings
          </li>
          <li class="logout" @click="handleLogout">
            <i class="icon">🚪</i> Logout
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Bar -->
      <header class="topbar">
        <h2>Dashboard</h2>
        <div class="filter">
          <label for="date">Filter by</label>
          <input type="date" id="date" v-model="filterDate" />
        </div>
      </header>

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
            <div class="stat-card green" @click="switchSection('overview')">
              <div class="stat-icon">💰</div>
              <div>
                <h3>Recurring Revenue</h3>
                <p>₺{{ formatNumber(monthlyRevenue) }}</p>
                <span :class="['stat-change', revenueGrowth >= 0 ? 'up' : 'down']">
                  {{ revenueGrowth >= 0 ? '+' : '' }}{{ revenueGrowth }}%
                </span>
              </div>
            </div>
            <div class="stat-card blue" @click="switchSection('products')">
              <div class="stat-icon">📦</div>
              <div>
                <h3>Account</h3>
                <p>{{ productCount }}</p>
                <span :class="['stat-change', accountGrowth >= 0 ? 'up' : 'down']">
                  {{ accountGrowth >= 0 ? '+' : '' }}{{ accountGrowth }}%
                </span>
              </div>
            </div>
            <div class="stat-card orange" @click="switchSection('users')">
              <div class="stat-icon">👥</div>
              <div>
                <h3>Active Users</h3>
                <p>{{ userCount }}</p>
                <span :class="['stat-change', userGrowth >= 0 ? 'up' : 'down']">
                  {{ userGrowth >= 0 ? '+' : '' }}{{ userGrowth }}%
                </span>
              </div>
            </div>
            <div class="stat-card pink" @click="switchSection('products')">
              <div class="stat-icon">🚀</div>
              <div>
                <h3>Onboarding</h3>
                <p>₺{{ formatNumber(onboardingRevenue) }}</p>
                <span :class="['stat-change', onboardingGrowth >= 0 ? 'up' : 'down']">
                  {{ onboardingGrowth >= 0 ? '+' : '' }}{{ onboardingGrowth }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Health Doughnut Chart -->
          <div class="health-chart">
            <h3>Health</h3>
            <div class="doughnut-wrapper">
              <div
                class="doughnut-chart"
                :style="`background: conic-gradient(
                  #10b981 ${healthStrongPercent}%,
                  #f59e0b ${healthStrongPercent}% 100%
                );`"
              ></div>
              <div class="doughnut-label">
                <span class="label-value">{{ healthStrongPercent }}%</span>
                <span class="label-text">Strong</span>
              </div>
            </div>
            <div class="legend">
              <span class="legend-item strong"></span> Strong
              <span class="legend-item weak"></span> Weak
            </div>
          </div>

          <!-- Customer 360 Section -->
          <div class="customer-360">
            <h3>Customer 360</h3>
            <div class="customer-info">
              <div class="customer-name">
                <span class="tag">C</span> Customer 360
              </div>
              <div class="metrics">
                <div class="metric">
                  <span>Customers</span>
                  <span>{{ totalCustomers }}</span>
                </div>
                <div class="metric">
                  <span>ARR</span>
                  <span>₺{{ formatNumber(annualRecurringRevenue) }}</span>
                </div>
                <div class="metric">
                  <span>MRR</span>
                  <span>₺{{ formatNumber(monthlyRecurringRevenue) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Real Chart.js Chart -->
          <div class="revenue-chart">
            <h3>Monthly Recurring Revenue (MRR)</h3>
            <canvas ref="mrrChartCanvas"></canvas>
          </div>

          <!-- Recent Activity Table -->
          <div class="table-section">
            <h3>Recent Activity</h3>
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Stage</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="activity in recentActivities" :key="activity.id">
                  <td>{{ activity.title || 'Unknown' }}</td>
                  <td>{{ activity.description || 'No stage' }}</td>
                </tr>
                <tr v-if="recentActivities.length === 0">
                  <td colspan="2" style="text-align: center; color: #6b7280;">No activity found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Other Sections -->
        <section v-if="section === 'users'">
          <ManageUsers />
        </section>
        <section v-if="section === 'products'">
          <ManageProducts />
        </section>
        <section v-if="section === 'orders'">
          <ViewOrders />
        </section>
        <section v-if="section === 'feedback'">
          <ViewFeedback />
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, onUnmounted } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import ManageUsers from './ManageUsers.vue'
import ManageProducts from './ManageProducts.vue'
import ViewOrders from './ViewOrders.vue'
import ViewFeedback from './ViewFeedback.vue'

// Register Chart.js components
Chart.register(...registerables)

// Reactive data
const section = ref('overview')
const filterDate = ref('')
const loading = ref(false)

// Dashboard data
const userCount = ref(0)
const productCount = ref(0)
const monthlyRevenue = ref(0)
const averageRating = ref(0)
const recentActivities = ref([])

// Growth metrics
const revenueGrowth = ref(0)
const accountGrowth = ref(0)
const userGrowth = ref(0)
const onboardingRevenue = ref(0)
const onboardingGrowth = ref(0)

// Customer 360
const totalCustomers = ref(0)
const annualRecurringRevenue = ref(0)
const monthlyRecurringRevenue = ref(0)

// Health chart
const healthStrongPercent = ref(0)

// Chart.js
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

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const response = await axios.get(
      'http://127.0.0.1:8000/api/admin/dashboard-stats',
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
    averageRating.value = data.average_rating || 0
    recentActivities.value = Array.isArray(data.recent_activities) ? data.recent_activities : []

    totalCustomers.value = data.total_customers || 0
    annualRecurringRevenue.value = data.annual_recurring_revenue || 0
    monthlyRecurringRevenue.value = data.monthly_recurring_revenue || 0
    healthStrongPercent.value = data.health_strong_percentage || 0
  } catch (err) {
    console.error('Failed to fetch dashboard data:', err)
  } finally {
    loading.value = false
  }
}
const fetchChartData = async () => {
  try {
    const response = await axios.get(
      'http://127.0.0.1:8000/api/admin/mrr-over-time',
      { headers: getAuthHeaders() }
    )

    const data = response.data?.data || []

    if (data.length > 0) {
      // Assume API returns month as string: "January", "February", etc.
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
  // Fallback MRR values (6 months shown, rest will be 0 if needed)
  chartData.value = [32000, 34000, 38000, 40000, 39000, 42000, 0, 0, 0, 0, 0, 0]
}

const renderChart = () => {
  if (mrrChart) {
    mrrChart.destroy()
  }

  // Ensure we have all 12 months
  const fullMonths = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ]

  // Create a map for easy lookup
  const dataMap = new Map()
  fullMonths.forEach((month, index) => {
    dataMap.set(month, 0) // default to 0
  })

  // Fill in available data
  chartLabels.value.forEach((label, index) => {
    const month = label.trim()
    if (dataMap.has(month)) {
      dataMap.set(month, chartData.value[index])
    }
  })

  // Final ordered data
  const finalLabels = fullMonths
  const finalData = fullMonths.map(month => dataMap.get(month))

  const ctx = mrrChartCanvas.value?.getContext('2d')
  if (!ctx) return

  mrrChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: finalLabels,
      datasets: [
        {
          label: 'MRR (₺)',
          data: finalData,
          backgroundColor: '#3b82f6',
          borderColor: '#2563eb',
          borderWidth: 1,
          borderRadius: 3,
          borderSkipped: false
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        },
        tooltip: {  // ✅ Fixed: Added missing comma before tooltip
          callbacks: {
            label: (context) => `₺${context.parsed.y.toLocaleString('tr-TR')}`
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            autoSkip: false // Show all month labels
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            borderDash: [3, 3],
            color: '#e5e7eb'
          },
          ticks: {
            callback: (value) => `₺${value.toLocaleString('tr-TR')}`
          }
        }
      }
    }
  })
}

onMounted(() => {
  fetchDashboardData()
  fetchChartData()
})



const handleLogout = async () => {
  try {
    await axios.post('http://127.0.0.1:8000/api/logout', {}, { headers: getAuthHeaders() })
  } catch (e) {
    // Ignore
  } finally {
    localStorage.clear()
    window.location.href = '/login'
  }
}
</script>

<style scoped>
/* === Base & Layout Fixes for Full-Screen === */
html,
body {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow: hidden;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dashboard-container {
  display: flex;
  height: 100vh;
  background: #f9fafb;
}

/* Sidebar */
.sidebar {
  width: 240px;
  background: #fff;
  border-right: 1px solid #eee;
  display: flex;
  flex-direction: column;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
  height: 100vh;
  overflow-y: auto;
}

.sidebar .logo {
  font-weight: bold;
  font-size: 1.4rem;
  color: #333;
  margin: 1.5rem 0;
  text-align: center;
}
.sidebar .logo span {
  color: #3b82f6;
}

.sidebar nav ul {
  list-style: none;
  padding: 0;
}
.sidebar nav li {
  padding: 0.8rem 1rem;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.sidebar nav li.active,
.sidebar nav li:hover {
  background: #f3f4f6;
}
.sidebar nav li.logout {
  margin-top: auto;
  color: #f87171;
}

/* Main Content */
.main-content {
  flex: 1;
  background: #f9fafb;
  padding: 1rem 2rem;
  overflow-y: auto;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.topbar h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
}
.topbar .filter {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}
.topbar .filter label {
  color: #6b7280;
}

/* Stats Cards */
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.stat-card {
  background: #fff;
  border-radius: 10px;
  padding: 1rem;
  display: flex;
  gap: 0.8rem;
  align-items: center;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.stat-card .stat-icon {
  font-size: 1.8rem;
}
.stat-card.green .stat-icon { color: #10b981; }
.stat-card.blue .stat-icon { color: #3b82f6; }
.stat-card.orange .stat-icon { color: #f59e0b; }
.stat-card.pink .stat-icon { color: #ec4899; }
.stat-card h3 {
  margin: 0;
  font-size: 0.9rem;
  color: #6b7280;
}
.stat-card p {
  margin: 0;
  font-size: 1.2rem;
  font-weight: bold;
  color: #111827;
}
.stat-change {
  font-size: 0.85rem;
  font-weight: 500;
}
.stat-change.up {
  color: #10b981;
}
.stat-change.down {
  color: #ef4444;
}

/* Health Doughnut Chart */
.health-chart {
  background: #fff;
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}
.health-chart h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
  color: #1f2937;
}
.doughnut-wrapper {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
}
.doughnut-chart {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.doughnut-label {
  position: absolute;
  color: #1f2937;
  text-align: center;
}
.label-value {
  font-size: 1.5rem;
  font-weight: bold;
  display: block;
}
.label-text {
  font-size: 0.85rem;
  color: #6b7280;
}
.legend {
  display: flex;
  justify-content: center;
  gap: 1rem;
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 0.25rem;
}
.legend-item {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 4px;
}
.legend-item.strong {
  background: #10b981;
}
.legend-item.weak {
  background: #f59e0b;
}

/* Customer 360 Section */
.customer-360 {
  background: #fff;
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}
.customer-360 h3 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  color: #1f2937;
}
.customer-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.customer-name {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #111827;
}
.tag {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: #3b82f6;
  color: white;
  border-radius: 50%;
  font-size: 0.9rem;
  font-weight: bold;
}
.metrics {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  text-align: center;
  font-size: 0.9rem;
  color: #6b7280;
}
.metric span:nth-child(1) {
  color: #6b7280;
  font-size: 0.85rem;
}
.metric span:nth-child(2) {
  font-weight: bold;
  color: #111827;
  margin-top: 0.25rem;
}

/* Revenue Chart */
.revenue-chart {
  background: #fff;
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1.5rem;
  height: 300px;
}
.revenue-chart h3 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  color: #1f2937;
}
.revenue-chart canvas {
  width: 100% !important;
  height: 100% !important;
}

/* Table Section */
.table-section {
  background: #fff;
  border-radius: 10px;
  padding: 1rem;
}
.table-section table {
  width: 100%;
  border-collapse: collapse;
}
.table-section th,
.table-section td {
  padding: 0.8rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}
.table-section th {
  color: #6b7280;
  font-weight: 600;
  font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    height: auto;
    flex-direction: row;
    padding: 8px;
    overflow-x: auto;
    white-space: nowrap;
  }

  .sidebar nav ul {
    display: flex;
    gap: 8px;
  }

  .sidebar nav li {
    white-space: nowrap;
    min-width: 120px;
    padding: 10px 14px;
    font-size: 14px;
  }

  .main-content {
    padding: 1rem;
  }

  .topbar {
    flex-wrap: wrap;
    gap: 12px;
  }

  .stats-row,
  .metrics {
    grid-template-columns: 1fr;
  }

  .health-chart,
  .customer-360,
  .revenue-chart {
    padding: 0.8rem;
  }
}

.revenue-chart {
  background: #fff;
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1.5rem;
  height: 300px;
  overflow-x: auto;
  overflow-y: hidden;
  position: relative;
}

.revenue-chart canvas {
  width: 100%;
  min-width: 900px; /* Enough for 12 bars */
  height: 100% !important;
}
</style>