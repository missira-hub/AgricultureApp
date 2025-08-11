<template>
  <div class="admin-feedback">
    <h2>Feedback Moderation</h2>
    <table class="feedback-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Product</th>
          <th>User</th>
          <th>Rating</th>
          <th>Comment</th>
          <th>Created</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="feedback in feedbacks" :key="feedback.id">
          <td>{{ feedback.id }}</td>
          <td>{{ feedback.product?.name }}</td>
          <td>{{ feedback.user?.name }}</td>
          <td>{{ feedback.rating }} ⭐</td>
          <td>{{ feedback.comment }}</td>
          <td>{{ new Date(feedback.created_at).toLocaleDateString() }}</td>
          <td>
            <button @click="deleteFeedback(feedback.id)">🗑️</button>
          </td>
        </tr>
      </tbody>
    </table>
    <button @click="loadFeedbacks">Reload</button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      feedbacks: [],
    };
  },
  methods: {
    loadFeedbacks() {
      axios.get('/api/admin/feedbacks')
        .then(res => {
          this.feedbacks = res.data.data;
        });
    },
    deleteFeedback(id) {
      if (!confirm('Delete this feedback?')) return;
      axios.delete(`/api/admin/feedbacks/${id}`)
        .then(() => {
          this.feedbacks = this.feedbacks.filter(f => f.id !== id);
          alert('Deleted successfully');
        });
    },
  },
  mounted() {
    this.loadFeedbacks();
  }
};
</script>

<style scoped>
.feedback-table {
  width: 100%;
  border-collapse: collapse;
}
.feedback-table th, .feedback-table td {
  border: 1px solid #ccc;
  padding: 8px;
}
</style>
