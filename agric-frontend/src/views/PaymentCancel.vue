<template>
  <div class="payment-success">
    <h1>🎉 Payment Successful!</h1>
    <p>Thank you for your purchase. Your order has been confirmed.</p>
  </div>
</template>

<script setup>
// You can fetch session/order ID from route query if needed
import { useRoute } from 'vue-router';

const route = useRoute();
const sessionId = route.query.session_id;
</script>

<style scoped>
.payment-success {
  padding: 2rem;
  text-align: center;
}
</style>
