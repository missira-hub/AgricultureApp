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
            <i class="icon">👥</i> Customer 
          </li>
          <li @click="switchSection('products')" :class="{ active: section === 'products' }">
            <i class="icon">📦</i> Products
          </li>
          <li @click="switchSection('orders')" :class="{ active: section === 'orders' }">
            <i class="icon">💬</i> Orders
          </li>
          <li @click="switchSection('feedback')" :class="{ active: section === 'feedback' }">
            <i class="icon">📄</i> Feedback
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

const conversationSearchQuery = ref('')

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
/* === Base & Layout === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f0f8f0 0%, #e8f5e8 100%);
        }

        .dashboard-container {
            display: flex;
            height: 100vh;
            background: linear-gradient(135deg, #f0f8f0 0%, #e8f5e8 100%);
        }

        /* === Sidebar === */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1a4d3a 0%, #2d5a47 100%);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(26, 77, 58, 0.15);
            flex-shrink: 0;
            height: 100vh;
            overflow-y: auto;
            color: white;
            position: relative;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="60" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            pointer-events: none;
        }

        .sidebar .logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            margin: 2rem 0 3rem 0;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .sidebar .logo span {
            color: #7dd87f;
            text-shadow: 0 0 10px rgba(125, 216, 127, 0.3);
        }

        .sidebar nav {
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0 1rem;
        }

        .sidebar nav li {
            padding: 1rem 1.25rem;
            cursor: pointer;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .sidebar nav li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, rgba(125, 216, 127, 0.2) 0%, rgba(125, 216, 127, 0.1) 100%);
            transition: width 0.3s ease;
        }

        .sidebar nav li.active,
        .sidebar nav li:hover {
            background: rgba(125, 216, 127, 0.15);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(125, 216, 127, 0.2);
        }

        .sidebar nav li.active::before,
        .sidebar nav li:hover::before {
            width: 4px;
        }

        .sidebar nav li.logout {
            margin-top: auto;
            color: #fca5a5;
            margin-bottom: 2rem;
        }

        .sidebar nav li.logout:hover {
            background: rgba(252, 165, 165, 0.15);
            color: #fca5a5;
            box-shadow: 0 4px 15px rgba(252, 165, 165, 0.2);
        }

        .icon {
            font-size: 1.2rem;
            min-width: 24px;
            text-align: center;
        }

        /* === Main Content === */
        .main-content {
            flex: 1;
            background: transparent;
            padding: 2rem;
            overflow-y: auto;
        }

        /* === Top Bar === */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            padding: 1.5rem 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(26, 77, 58, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .topbar h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a4d3a;
            margin: 0;
        }

        .topbar .filter {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .topbar .filter label {
            color: #5a6c57;
            font-weight: 500;
        }

        .topbar .filter input {
            padding: 0.75rem 1rem;
            border: 2px solid #e8f5e8;
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .topbar .filter input:focus {
            outline: none;
            border-color: #7dd87f;
            box-shadow: 0 0 0 3px rgba(125, 216, 127, 0.1);
        }

        /* === Stats Cards === */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            gap: 1.25rem;
            align-items: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 32px rgba(26, 77, 58, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(26, 77, 58, 0.15);
        }

        .stat-card.green::before { background: linear-gradient(90deg, #4ade80, #22c55e); }
        .stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
        .stat-card.orange::before { background: linear-gradient(90deg, #f97316, #ea580c); }
        .stat-card.purple::before { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }

        .stat-icon {
            font-size: 2.5rem;
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .stat-card.green .stat-icon { background: linear-gradient(135deg, #4ade80, #22c55e); }
        .stat-card.blue .stat-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stat-card.orange .stat-icon { background: linear-gradient(135deg, #f97316, #ea580c); }
        .stat-card.purple .stat-icon { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

        .stat-content h3 {
            margin: 0;
            font-size: 1rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .stat-content p {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            color: #1a4d3a;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            display: inline-block;
        }

        .stat-change.up {
            color: #059669;
            background: rgba(5, 150, 105, 0.1);
        }

        .stat-change.down {
            color: #dc2626;
            background: rgba(220, 38, 38, 0.1);
        }

        /* === Health Chart === */
        .health-chart {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 8px 32px rgba(26, 77, 58, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .health-chart h3 {
            margin: 0;
            font-size: 1.25rem;
            color: #1a4d3a;
            font-weight: 700;
        }

        .doughnut-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .doughnut-chart {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(#4ade80 0% 75%, #fbbf24 75% 100%);
            position: relative;
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
        }

        .doughnut-label {
            position: absolute;
            color: #1a4d3a;
            text-align: center;
            z-index: 1;
        }

        .label-value {
            font-size: 1.75rem;
            font-weight: 800;
            display: block;
            color: #1a4d3a;
        }

        .label-text {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
        }

        .legend-item {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .legend-item.strong { background: #4ade80; }
        .legend-item.weak { background: #fbbf24; }

        /* === Customer Section === */
        .customer {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(26, 77, 58, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .customer h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.25rem;
            color: #1a4d3a;
            font-weight: 700;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .customer-name {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            color: #1a4d3a;
            font-size: 1.1rem;
        }

        .tag {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: white;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(74, 222, 128, 0.3);
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            text-align: center;
        }

        .metric {
            padding: 1rem;
            background: rgba(26, 77, 58, 0.05);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .metric:hover {
            background: rgba(26, 77, 58, 0.1);
            transform: translateY(-2px);
        }

        .metric span:nth-child(1) {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            display: block;
            margin-bottom: 0.5rem;
        }

        .metric span:nth-child(2) {
            font-weight: 800;
            color: #1a4d3a;
            font-size: 1.25rem;
            display: block;
        }

        /* === Revenue Chart === */
        .revenue-chart {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            height: 350px;
            box-shadow: 0 8px 32px rgba(26, 77, 58, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .revenue-chart h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.25rem;
            color: #1a4d3a;
            font-weight: 700;
        }

        .chart-placeholder {
            width: 100%;
            height: calc(100% - 3rem);
            background: linear-gradient(135deg, #f0f8f0 0%, #e8f5e8 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-weight: 500;
            border: 2px dashed rgba(26, 77, 58, 0.2);
        }

        /* === Table Section === */
        .table-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(26, 77, 58, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .table-section h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.25rem;
            color: #1a4d3a;
            font-weight: 700;
        }

        .table-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-section th,
        .table-section td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(26, 77, 58, 0.1);
        }

        .table-section th {
            color: #6b7280;
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(26, 77, 58, 0.05);
        }

        .table-section td {
            color: #1a4d3a;
            font-weight: 500;
        }

        .table-section tr:hover {
            background: rgba(26, 77, 58, 0.03);
        }

        /* === Loading State === */
        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 300px;
            color: #1a4d3a;
        }

        .spinner {
            border: 4px solid rgba(125, 216, 127, 0.2);
            border-radius: 50%;
            border-top: 4px solid #7dd87f;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* === Responsive Design === */
        @media (max-width: 1024px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .metrics {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                flex-direction: row;
                padding: 1rem;
                overflow-x: auto;
            }

            .sidebar .logo {
                margin: 0 1rem 0 0;
                font-size: 1.25rem;
            }

            .sidebar nav ul {
                display: flex;
                gap: 0.5rem;
                padding: 0;
            }

            .sidebar nav li {
                white-space: nowrap;
                min-width: 120px;
                margin-bottom: 0;
            }

            .main-content {
                padding: 1rem;
            }

            .topbar {
                flex-wrap: wrap;
                gap: 1rem;
                padding: 1rem;
            }

            .stats-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .revenue-chart {
                height: 250px;
                padding: 1rem;
            }
        }
</style>