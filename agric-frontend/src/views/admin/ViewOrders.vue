<template>
  <div class="admin-orders">
    <h2>Order Management</h2>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Orders Table -->
    <table class="order-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Buyer</th>
          <th>Total</th>
          <th>Status</th>
          <th>Items</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders" :key="order.id">
          <td>{{ order.id }}</td>
          <td>{{ order.user?.name || 'Unknown' }}</td>
          <td>{{ formatPrice(order.total_price) }} ₺</td>
          <td>
            <select v-model="order.status" @change="updateOrderStatus(order)" class="status-select">
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </td>
          <td>
            <ul class="items-list">
              <li v-for="item in order.order_items" :key="item.id">
                {{ item.product?.name || 'Deleted Product' }} × {{ item.quantity }}
              </li>
            </ul>
          </td>
          <td>
            <button @click="confirmDelete(order)" class="delete-btn">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Reload Button -->
    <button @click="fetchOrders" :disabled="loading" class="reload-btn">
      {{ loading ? 'Loading...' : 'Reload Orders' }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// State
const orders = ref([]);
const error = ref('');
const loading = ref(false);

// Fetch Orders
const fetchOrders = async () => {
  error.value = '';
  loading.value = true;
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      error.value = 'Not authenticated. Please log in.';
      return;
    }

    const res = await axios.get('/api/admin/orders', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    // Laravel pagination: extract data from 'data' array
    orders.value = Array.isArray(res.data.data) ? res.data.data : [];
  } catch (err) {
    console.error('Failed to fetch orders:', err);
    if (err.response?.status === 401) {
      error.value = 'Session expired. Please log in again.';
      localStorage.removeItem('token');
    } else if (err.response?.status === 403) {
      error.value = 'Access denied. Admins only.';
    } else {
      error.value = 'Failed to load orders. Please try again later.';
    }
  } finally {
    loading.value = false;
  }
};

// Update Order Status
const updateOrderStatus = async (order) => {
  try {
    const token = localStorage.getItem('token');
    await axios.put(
      `/api/admin/orders/${order.id}`,
      { status: order.status },
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    alert(`Order #${order.id} status updated to "${order.status}".`);
  } catch (err) {
    console.error('Failed to update order status:', err);
    alert('Failed to update status. Please try again.');
    // Re-fetch to sync
    fetchOrders();
  }
};

// Confirm and Delete Order
const confirmDelete = (order) => {
  // Prevent deletion if status is 'paid' or beyond
  if (['paid', 'shipped', 'delivered'].includes(order.status)) {
    alert('Cannot delete: This order has already been processed (paid or shipped).');
    return;
  }

  const confirmed = window.confirm(
    `Are you sure you want to delete order #${order.id}? This action cannot be undone.`
  );

  if (confirmed) {
    deleteOrder(order.id);
  }
};

// Delete Order
const deleteOrder = async (id) => {
  try {
    const token = localStorage.getItem('token');
    await axios.delete(`/api/admin/orders/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    // Remove from UI
    orders.value = orders.value.filter((order) => order.id !== id);
    alert('Order deleted successfully.');
  } catch (err) {
    console.error('Failed to delete order:', err);
    if (err.response?.status === 404) {
      alert('Order not found.');
    } else if (err.response?.status === 403) {
      alert('Permission denied.');
    } else {
      alert('Delete failed. Please try again.');
    }
    // Sync with backend
    fetchOrders();
  }
};

// Format price safely
const formatPrice = (price) => {
  const num = parseFloat(price);
  return isNaN(num) ? '0.00' : num.toFixed(2);
};

// Load orders on mount
onMounted(() => {
  fetchOrders();
});
</script>

<style scoped>
.admin-orders {
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2 {
  color: #1f2937;
  margin-bottom: 20px;
}

.error-message {
  padding: 12px;
  background-color: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 6px;
  color: #b91c1c;
  margin-bottom: 1rem;
}

.order-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  overflow: hidden;
}

.order-table th {
  background-color: #f3f4f6;
  color: #374151;
  font-weight: 600;
  padding: 12px;
  text-align: left;
}

.order-table td {
  padding: 12px;
  border-top: 1px solid #e5e7eb;
}

.items-list {
  margin: 0;
  padding-left: 16px;
  font-size: 14px;
  list-style-type: disc;
}

.status-select {
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  font-size: 14px;
  min-width: 100px;
}

.delete-btn {
  padding: 6px 12px;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.delete-btn:hover {
  background-color: #dc2626;
}

.reload-btn {
  padding: 10px 20px;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
}

.reload-btn:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}
</style>