<!-- src/views/admin/AdminDashboard.vue -->
<template>
  <div class="dashboard">
    <h2>Admin Dashboard</h2>

    <section>
      <h3>All Users</h3>
      <ul>
        <li v-for="user in users" :key="user.id">
          {{ user.name }} - <strong>{{ user.role }}</strong>
          <button v-if="user.role === 'consumer'" @click="promoteToFarmer(user.id)">
            Promote to Farmer
          </button>
        </li>
      </ul>
    </section>

    <section>
      <h3>Pending Feedback</h3>
      <ul>
        <li v-for="fb in feedback" :key="fb.id">
          {{ fb.comment }} - Rating: {{ fb.rating }}
          <button @click="approveFeedback(fb.id)">Approve</button>
        </li>
      </ul>
    </section>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      users: [],
      feedback: []
    };
  },
  async created() {
    const token = localStorage.getItem('token');
    const config = { headers: { Authorization: `Bearer ${token}` } };

    this.users = (await axios.get('http://127.0.0.1:8000/api/admin/users', config)).data;
    this.feedback = (await axios.get('http://127.0.0.1:8000/api/admin/pending-feedback', config)).data;
  },
  methods: {
    async promoteToFarmer(userId) {
      const token = localStorage.getItem('token');
      await axios.post(`http://127.0.0.1:8000/api/admin/promote/${userId}`, {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
      alert('User promoted!');
      location.reload();
    },
    async approveFeedback(id) {
      const token = localStorage.getItem('token');
      await axios.post(`http://127.0.0.1:8000/api/admin/approve-feedback/${id}`, {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
      alert('Feedback approved!');
      location.reload();
    }
  }
};
</script>

<style scoped>
.dashboard {
  padding: 20px;
}
button {
  margin-left: 10px;
}
</style>
