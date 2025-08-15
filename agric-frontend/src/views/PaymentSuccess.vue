<template>
  <div class="payment-success">
    <h1>Payment Successful! 🎉</h1>
    <p>Your order has been confirmed and payment is complete.</p>

    <button @click="goToOrders">Go to Your Orders</button>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const goToOrders = () => {
  router.push({
    name: "ConsumerDashboard",
    query: { section: "orders" }
  });
};
</script>

<style scoped>
.payment-success {
  max-width: 400px;
  margin: 3rem auto;
  text-align: center;
}
button {
  margin-top: 1.5rem;
  background-color: #22c55e;
  color: white;
  padding: 0.6rem 1.2rem;
  border-radius: 6px;
  cursor: pointer;
}
button:hover {
  background-color: #16a34a;
}
</style>
