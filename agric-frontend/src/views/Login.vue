<template>
  <div class="login-page">
    <div class="background-blur"></div>
    <div class="overlay">
      <div class="login-card">
        <h2>Welcome Back 👋</h2>
        <p class="subtitle">Login to your account</p>
        <form @submit.prevent="login">
          <input v-model="email" type="email" placeholder="Email address" required />
          <input v-model="password" type="password" placeholder="Password" required />
          <button type="submit">Login</button>
          <p class="switch-link">
            Don’t have an account?
            <router-link to="/register">Register here</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      password: ''
    };
  },
  methods: {
    async login() {
      try {
        const res = await axios.post('http://127.0.0.1:8000/api/login', {
          email: this.email,
          password: this.password,
        });

        localStorage.setItem('token', res.data.token);
        localStorage.setItem('role', res.data.role);

        const role = res.data.role;
        if (role === 'admin') {
          this.$router.push('/admin/dashboard');
        } else if (role === 'farmer') {
          this.$router.push('/farmer/dashboard');
        } else {
          this.$router.push('/consumer/dashboard');
        }

      } catch (error) {
        if (error.response?.data?.message) {
          alert(`Login failed: ${error.response.data.message}`);
        } else {
          alert('Login failed: An unknown error occurred.');
        }
      }
    }
  }
};
</script>

<style scoped>
.login-page {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  z-index: 0;
}

/* Background image with blur */
.background-blur {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: url('@/assets/hero.png') no-repeat center center;
  background-size: cover;
  filter: blur(4px);
  transform: scale(1.05); /* Slightly scale up to avoid edges showing */
  z-index: -2;
}

.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  padding: 2.5rem 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  color: #1e1e1e;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.96);
  text-align: center;
  z-index: 2;
}

.login-card h2 {
  margin-bottom: 0.5rem;
  font-size: 1.8rem;
}

.subtitle {
  font-size: 0.95rem;
  color: #555;
  margin-bottom: 1.5rem;
}

.login-card form {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  width: 100%;
  gap: 1rem;
}

input,
button {
  width: 100%;
  box-sizing: border-box;
}

input {
  padding: 12px;
  background: #f2f2f2;
  border: 1px solid #ccc;
  border-radius: 6px;
  color: #333;
  font-size: 1rem;
}

button {
  padding: 12px;
  background: rgb(13, 165, 122);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s ease;
}

button:hover {
  background: #36996b;
}

.switch-link {
  margin-top: 1rem;
  font-size: 0.9rem;
}

.switch-link a {
  color: rgb(13, 165, 122);
  text-decoration: none;
}
</style>
