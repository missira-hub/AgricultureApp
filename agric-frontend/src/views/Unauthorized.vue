<template>
  <div class="unauthorized-page">
    <div class="container">
      <h1>🚫 Access Denied</h1>
      <p>You do not have permission to view this page.</p>
      <router-link to="/">← Go back to Home</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Unauthorized',
}
</script>

<style scoped>
.unauthorized-page {
  display: flex;
  height: 80vh;
  justify-content: center;
  align-items: center;
  background: #fef2f2;
  color: #b91c1c;
  font-family: Arial, sans-serif;
  text-align: center;
}

.container {
  border: 2px solid #b91c1c;
  padding: 2rem 3rem;
  border-radius: 12px;
  background: white;
  box-shadow: 0 0 10px rgba(185, 28, 28, 0.3);
}

h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

p {
  font-size: 1.25rem;
  margin-bottom: 2rem;
}

a {
  color: #b91c1c;
  text-decoration: none;
  font-weight: bold;
  font-size: 1rem;
}

a:hover {
  text-decoration: underline;
}
</style>
