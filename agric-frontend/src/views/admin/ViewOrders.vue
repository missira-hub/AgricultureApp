<template>
  <div class="view-orders">
    <h2>📦 Manage Orders</h2>

    <table class="orders-table" v-if="orders.length">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>User</th>
          <th>Total Price</th>
          <th>Status</th>
          <th>Placed At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders" :key="order.id">
          <td>{{ order.id }}</td>
          <td>{{ order.user?.name || 'Unknown' }}</td>
          <td>{{ order.total_price | currency }}</td>
          <td>{{ order.status }}</td>
          <td>{{ new Date(order.created_at).toLocaleString() }}</td>
          <td>
            <button @click="viewOrder(order.id)" class="btn-view">View</button>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else>No orders found.</p>

    <!-- Order details modal -->
    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal">
        <h3>Order Details (ID: {{ selectedOrder.id }})</h3>

        <p><strong>User:</strong> {{ selectedOrder.user?.name || 'Unknown' }}</p>
        <p><strong>Status:</strong> {{ selectedOrder.status }}</p>
        <p><strong>Placed At:</strong> {{ new Date(selectedOrder.created_at).toLocaleString() }}</p>
        <p><strong>Total Price:</strong> {{ selectedOrder.total_price | currency }}</p>

        <h4>Items</h4>
        <ul>
          <li v-for="item in selectedOrder.orderItems" :key="item.id">
            {{ item.product?.name || 'Unknown product' }} — Quantity: {{ item.quantity }}, Price: {{ item.price | currency }}
          </li>
        </ul>

        <button @click="closeModal" class="btn-close">Close</button>
      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="pagination.total > pagination.per_page">
      <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">Prev</button>
      <span>Page {{ pagination.current_page }} / {{ pagination.last_page }}</span>
      <button :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">Next</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ViewOrders',
  data() {
    return {
      orders: [],
      pagination: {},
      showModal: false,
      selectedOrder: {},
    }
  },
  filters: {
    currency(value) {
      if (typeof value !== "number") return value
      return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value)
    }
  },
  methods: {
    fetchOrders(page = 1) {
      axios.get(`/api/admin/orders?page=${page}`)
        .then(res => {
          this.orders = res.data.data
          this.pagination = {
            current_page: res.data.current_page,
            last_page: res.data.last_page,
            per_page: res.data.per_page,
            total: res.data.total,
          }
        })
        .catch(err => {
          console.error('Error fetching orders:', err)
        })
    },
    viewOrder(id) {
      axios.get(`/api/admin/orders/${id}`)
        .then(res => {
          this.selectedOrder = res.data
          this.showModal = true
        })
        .catch(err => {
          console.error('Error fetching order details:', err)
        })
    },
    closeModal() {
      this.showModal = false
      this.selectedOrder = {}
    },
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.fetchOrders(page)
      }
    }
  },
  mounted() {
    this.fetchOrders()
  }
}
</script>

<style scoped>
.view-orders {
  padding: 1rem;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
}

.orders-table th,
.orders-table td {
  padding: 0.75rem;
  border-bottom: 1px solid #ccc;
  text-align: left;
}

.btn-view {
  background-color: #2563eb;
  border: none;
  color: white;
  padding: 0.4rem 0.7rem;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}
.btn-view:hover {
  background-color: #1d4ed8;
}

/* Modal styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right:0;
  bottom:0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 0 10px rgba(0,0,0,0.25);
}

.btn-close {
  background-color: #ef4444;
  border: none;
  color: white;
  padding: 0.4rem 0.7rem;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 1rem;
  float: right;
}
.btn-close:hover {
  background-color: #b91c1c;
}

/* Pagination */
.pagination {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.pagination button {
  background-color: #2563eb;
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
}
.pagination button[disabled] {
  background-color: #a5b4fc;
  cursor: not-allowed;
}
</style>

