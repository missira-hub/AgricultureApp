<template>
  <div class="view-feedback">
    <h2>⭐ Manage Feedback</h2>

    <table class="feedback-table" v-if="Array.isArray(feedback) && feedback.length">
      <thead>
        <tr>
          <th>User</th>
          <th>Product</th>
          <th>Rating</th>
          <th>Comment</th>
          <th>Posted At</th>
          <th>Approved</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in feedback" :key="item.id">
          <td>{{ item.user?.name || 'Unknown' }}</td>
          <td>{{ item.product?.name || 'Unknown' }}</td>
          <td>{{ item.rating }} / 5</td>
          <td>{{ item.comment }}</td>
          <td>{{ new Date(item.created_at).toLocaleString() }}</td>
          <td>
            <span v-if="item.approved">✅ Yes</span>
            <span v-else>❌ No</span>
          </td>
          <td>
            <button 
              v-if="!item.approved" 
              @click="approveFeedback(item.id)" 
              class="btn-approve"
            >
              ✔ Approve
            </button>
            <button @click="deleteFeedback(item.id)" class="btn-delete">
              🗑 Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else>No feedback found.</p>

    <!-- Pagination -->
    <div class="pagination" v-if="pagination.total > pagination.per_page">
      <button 
        :disabled="pagination.current_page === 1" 
        @click="changePage(pagination.current_page - 1)"
      >Prev</button>

      <span>Page {{ pagination.current_page }} / {{ pagination.last_page }}</span>

      <button 
        :disabled="pagination.current_page === pagination.last_page" 
        @click="changePage(pagination.current_page + 1)"
      >Next</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ViewFeedback',
  data() {
    return {
      feedback: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0,
      },
      loading: false,
    }
  },
  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('token')
      return {
        Authorization: `Bearer ${token}`,
      }
    },

    async fetchFeedback(page = 1) {
      this.loading = true
      try {
        const res = await axios.get(`/api/admin/feedback?page=${page}`, {
          headers: this.getAuthHeaders(),
        })

        // Defensive fallback if response structure varies
        const data = res.data || {}
        this.feedback = Array.isArray(data.data) ? data.data : []
        this.pagination = {
          current_page: data.current_page || 1,
          last_page: data.last_page || 1,
          per_page: data.per_page || 20,
          total: data.total || 0,
        }
      } catch (err) {
        console.error('Failed to load feedback:', err)
        alert('Failed to load feedback.')
        this.feedback = []
      } finally {
        this.loading = false
      }
    },

    async deleteFeedback(id) {
      if (!confirm('Are you sure you want to delete this feedback?')) return
      try {
        await axios.delete(`/api/admin/feedback/${id}`, {
          headers: this.getAuthHeaders(),
        })
        alert('Feedback deleted successfully.')
        // Refresh current page after delete
        this.fetchFeedback(this.pagination.current_page)
      } catch (err) {
        console.error('Failed to delete feedback:', err)
        alert('Failed to delete feedback.')
      }
    },

    async approveFeedback(id) {
      try {
        await axios.post(`/api/admin/feedback/${id}/approve`, null, {
          headers: this.getAuthHeaders(),
        })
        alert('Feedback approved successfully.')
        // Refresh current page after approve
        this.fetchFeedback(this.pagination.current_page)
      } catch (err) {
        console.error('Failed to approve feedback:', err)
        alert('Failed to approve feedback.')
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.fetchFeedback(page)
      }
    },
  },
  mounted() {
    this.fetchFeedback()
  },
}
</script>

<style scoped>
.view-feedback {
  padding: 1rem;
}

.feedback-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
}

.feedback-table th,
.feedback-table td {
  padding: 0.75rem;
  border-bottom: 1px solid #ccc;
  text-align: left;
}

.btn-delete {
  background-color: #dc2626;
  border: none;
  color: white;
  padding: 0.4rem 0.7rem;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  margin-left: 0.5rem;
}
.btn-delete:hover {
  background-color: #b91c1c;
}

.btn-approve {
  background-color: #16a34a;
  border: none;
  color: white;
  padding: 0.4rem 0.7rem;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}
.btn-approve:hover {
  background-color: #15803d;
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
