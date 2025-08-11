<template>
  <div class="admin-users">
    <h2>👥 All Users</h2>
    <div v-if="loading">Loading users...</div>
    <div v-else>
      <table class="users-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) in users" :key="user.id">
            <td>{{ index + 1 }}</td>
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span :class="user.role">{{ user.role }}</span>
            </td>
            <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminUsers',
  data() {
    return {
      users: [],
      loading: true,
    };
  },
  methods: {
    fetchUsers() {
      const token = localStorage.getItem('token');

      axios.get('http://localhost:8000/api/admin/users', {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json'
        }
      })
      .then(response => {
        this.users = response.data.users;
      })
      .catch(error => {
        console.error('Error fetching users:', error);
      })
      .finally(() => {
        this.loading = false;
      });
    }
  },
  mounted() {
    this.fetchUsers();
  }
};
</script>

<style scoped>
.admin-users {
  padding: 20px;
  background: #f8fafc;
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
}

.admin-users h2 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 20px;
  color: #1e293b;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0px 2px 10px rgba(0,0,0,0.05);
}

.users-table th {
  background: #f1f5f9;
  padding: 12px;
  text-align: left;
  font-size: 14px;
  font-weight: 600;
  color: #334155;
}

.users-table td {
  padding: 12px;
  border-top: 1px solid #e2e8f0;
  font-size: 14px;
  color: #475569;
}

.users-table tbody tr:nth-child(even) {
  background-color: #f9fafb;
}

.users-table tbody tr:hover {
  background-color: #eef2ff;
  transition: background 0.2s ease-in-out;
}

/* Role badges */
.admin, .farmer, .consumer {
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}

.admin {
  background: #fee2e2;
  color: #b91c1c;
}

.farmer {
  background: #dcfce7;
  color: #15803d;
}

.consumer {
  background: #dbeafe;
  color: #1d4ed8;
}
</style>
