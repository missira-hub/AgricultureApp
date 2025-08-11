<template>
  <div class="admin-orders">
    <h2>Order Management</h2>
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
          <td>{{ order.user?.name }}</td>
          <td>{{ order.total_price }} ₺</td>
          <td>
            <select v-model="order.status" @change="updateOrderStatus(order)">
              <option value="pending">Pending</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </td>
          <td>
            <ul>
              <li v-for="item in order.order_items" :key="item.id">
                {{ item.product.name }} x {{ item.quantity }}
              </li>
            </ul>
          </td>
          <td>
            <button @click="deleteOrder(order.id)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
    <button @click="fetchOrders">Reload</button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      orders: [],
    };
  },
  methods: {
    fetchOrders() {
      axios.get('/api/admin/orders')
        .then(res => {
          this.orders = res.data.data;
        });
    },
    updateOrderStatus(order) {
      axios.put(`/api/admin/orders/${order.id}`, { status: order.status })
        .then(() => alert('Order status updated'))
        .catch(() => alert('Update failed'));
    },
    deleteOrder(id) {
      if (!confirm('Delete this order?')) return;
      axios.delete(`/api/admin/orders/${id}`)
        .then(() => {
          this.orders = this.orders.filter(o => o.id !== id);
          alert('Order deleted');
        });
    }
  },
  mounted() {
    this.fetchOrders();
  }
};
</script>

<style scoped>
.order-table {
  width: 100%;
  border-collapse: collapse;
}
.order-table th, .order-table td {
  border: 1px solid #ccc;
  padding: 8px;
}
</style>
