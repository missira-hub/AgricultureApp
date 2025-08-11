<template>
  <div>
    <h2 class="title">👥 Manage Users</h2>

    <table class="user-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(user, index) in users" :key="user.id">
          <td>{{ index + 1 }}</td>
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.role }}</td>
          <td>
            <button
              v-if="user.role === 'consumer'"
              @click="upgradeToFarmer(user.id)"
            >
              Upgrade to Farmer
            </button>
            <button
              v-if="user.role === 'farmer'"
              @click="downgradeToConsumer(user.id)"
            >
              Downgrade to Consumer
            </button>
            <button class="delete" @click="deleteUser(user.id)">🗑️ Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ManageUsers',
  data() {
    return {
      users: []
    }
  },
  async created() {
    await this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('http://127.0.0.1:8000/api/admin/users', {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json'
          }
        });
        this.users = response.data;
      } catch (error) {
        console.error('Failed to fetch users:', error);
      }
    },
    async upgradeToFarmer(userId) {
      try {
        const token = localStorage.getItem('token');
        await axios.put(`http://127.0.0.1:8000/api/admin/users/${userId}/upgrade`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        await this.fetchUsers(); // Refresh list
      } catch (error) {
        console.error('Upgrade failed:', error);
      }
    },
    async downgradeToConsumer(userId) {
      try {
        const token = localStorage.getItem('token');
        await axios.put(`http://127.0.0.1:8000/api/admin/users/${userId}/downgrade`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        await this.fetchUsers();
      } catch (error) {
        console.error('Downgrade failed:', error);
      }
    },
    async deleteUser(userId) {
      if (!confirm('Are you sure you want to delete this user?')) return;
      try {
        const token = localStorage.getItem('token');
        await axios.delete(`http://127.0.0.1:8000/api/admin/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        await this.fetchUsers();
      } catch (error) {
        console.error('Delete failed:', error);
      }
    }
  }
}
</script>

<style scoped>
.title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}
.user-table {
  width: 100%;
  border-collapse: collapse;
}
.user-table th,
.user-table td {
  border: 1px solid #ddd;
  padding: 0.75rem;
  text-align: left;
}
.user-table th {
  background-color: #f1f5f9;
}
button {
  padding: 0.4rem 0.6rem;
  margin-right: 0.4rem;
  border: none;
  background-color: #3b82f6;
  color: white;
  border-radius: 4px;
  cursor: pointer;
}
button.delete {
  background-color: #ef4444;
}
</style>
