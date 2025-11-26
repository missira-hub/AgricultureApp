<template>
  <div class="manage-users">
    <!-- Header -->
    <div class="header">
      <h2>👥 All Users</h2>
      <p>{{ users.length }} users in total</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading users...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="users.length === 0" class="empty-state">
      <i class="fas fa-users-slash"></i>
      <p>No users found.</p>
    </div>

    <!-- User Cards (Mobile-First) -->
    <div v-else class="users-cards">
      <div v-for="(user, index) in users" :key="user.id" class="user-card">
        <div class="user-info">
          <div class="avatar">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <div class="details">
            <h3>{{ user.name }}</h3>
            <p class="email">{{ user.email }}</p>
            <span :class="['badge', user.role]">{{ user.role }}</span>
          </div>
        </div>
        <div class="meta">
          <small>Joined: {{ new Date(user.created_at).toLocaleDateString() }}</small>
        </div>
      </div>
    </div>

    <!-- Optional: Table View on Desktop -->
    <div class="users-table-wrapper" v-if="!loading && users.length > 0">
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
              <span :class="['badge', user.role]">{{ user.role }}</span>
            </td>
            <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ManageUsers',
  data() {
    return {
      users: [],
      loading: true,
    }
  },
  methods: {
    fetchUsers() {
      const token = localStorage.getItem('token')

      axios
        .get('http://localhost:8000/api/admin/users', {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: 'application/json',
          },
        })
        .then((response) => {
          this.users = response.data.users || []
        })
        .catch((error) => {
          console.error('Error fetching users:', error)
          this.users = [] // Fallback
        })
        .finally(() => {
          this.loading = false
        })
    },
  },
  mounted() {
    this.fetchUsers()
  },
}
</script>

<style scoped>
/* === Base === */
.manage-users {
  padding: 1.5rem;
  background: #f9fbf8;
  min-height: 100vh;
  font-family: 'Segoe UI', 'Poppins', sans-serif;
  color: #2d372f;
}

/* === Header === */
.header {
  margin-bottom: 1.5rem;
}

.header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1b5e20;
  margin: 0 0 0.5rem 0;
}

.header p {
  color: #4a5568;
  font-size: 0.9rem;
  margin: 0;
}

/* === Loading === */
.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #1b5e20;
}

.spinner {
  border: 3px solid #e8f5e8;
  border-top: 3px solid #4caf50;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  animation: spin 1s linear infinite;
  margin-bottom: 0.75rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* === Empty State === */
.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #6b7280;
  background: #f0f8f0;
  border-radius: 12px;
  border: 1px dashed #4caf50;
}

.empty-state i {
  font-size: 3rem;
  color: #a5d6a7;
  margin-bottom: 0.5rem;
}

/* === User Cards === */
.users-cards {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.user-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.06);
  border: 1px solid #e8f5e8;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.user-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(46, 125, 50, 0.12);
}

.user-info {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4caf50, #2e7d32);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.details h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  color: #1b5e20;
  font-weight: 600;
}

.details .email {
  font-size: 0.9rem;
  color: #4a5568;
  margin: 0 0 0.5rem 0;
}

.meta {
  font-size: 0.85rem;
  color: #6b7280;
  border-top: 1px solid #e8f5e8;
  padding-top: 0.75rem;
}

/* === Badges === */
.badge {
  padding: 0.3rem 0.6rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
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

/* === Table View (Desktop Only) === */
.users-table-wrapper {
  display: none;
}

@media (min-width: 768px) {
  /* Show table on desktop */
  .users-table-wrapper {
    display: block;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-top: 1rem;
  }

  .users-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(46, 125, 50, 0.08);
    border: 1px solid #e8f5e8;
  }

  .users-table th {
    background: #f1f8e9;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #2e7d32;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .users-table td {
    padding: 1rem;
    border-bottom: 1px solid #e8f5e8;
    font-size: 0.9rem;
    color: #2d372f;
  }

  .users-table tbody tr:hover {
    background: #f1f8e9;
  }

  /* Hide cards on desktop */
  .users-cards {
    display: none;
  }
}

/* === Responsive (Mobile) === */
@media (max-width: 480px) {
  .manage-users {
    padding: 1rem;
  }

  .header h2 {
    font-size: 1.3rem;
  }

  .details h3 {
    font-size: 1rem;
  }

  .details .email {
    font-size: 0.85rem;
  }

  .meta {
    font-size: 0.8rem;
  }
}
</style>