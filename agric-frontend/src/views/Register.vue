<template>
  <form @submit.prevent="register">
    <input v-model="name" type="text" placeholder="Name" />
    <input v-model="email" type="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <input v-model="role" type="text" placeholder="Role (farmer/consumer)" />
    <button type="submit">Register</button>
  </form>
</template>

<script>
import axios from '@/axios';

export default {
  data() {
    return {
      name: '',
      email: '',
      password: '',
      role: '',
    };
  },
  methods: {
    async register() {
      try {
        const res = await axios.post('/register', {
          name: this.name,
          email: this.email,
          password: this.password,
          role: this.role,
        });

        localStorage.setItem('token', res.data.token);
        alert('Registration successful!');
        this.$router.push('/dashboard');
      } catch (err) {
        alert('Registration failed');
        console.error(err);
      }
    },
  },
};
</script>
