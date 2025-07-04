<template>
  <form @submit.prevent="login">
    <input v-model="email" type="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <button type="submit">Login</button>
  </form>
</template>

<script>
import axios from '@/axios'; // use the instance

export default {
  data() {
    return {
      email: '',
      password: '',
    };
  },
  methods: {
    async login() {
      try {
        const res = await axios.post('/login', {
          email: this.email,
          password: this.password,
        });

        localStorage.setItem('token', res.data.token);
        alert('Login successful!');
        this.$router.push('/dashboard'); // or wherever
      } catch (err) {
        alert('Login failed');
        console.error(err);
      }
    },
  },
};
</script>
