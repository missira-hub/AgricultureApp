<template>
  <section class="contact-section">
    <h1 class="section-title">Contact Us</h1>
    <p class="contact-intro">If you have any questions, feel free to reach out!</p>
    <ul class="contact-info-list">
      <li>📞 Phone: +90 532 000 0000</li>
      <li>📧 Email: support@fasi-market.com</li>
      <li>📍 Location: North Cyprus</li>
    </ul>

    <form class="contact-form" @submit.prevent="submitForm">
      <input type="text" placeholder="Your name" v-model="name" required />
      <input type="email" placeholder="Your email" v-model="email" required />
      <textarea placeholder="Your message" v-model="message" required></textarea>
      <button type="submit">Send</button>
    </form>
  </section>
</template>

<script setup>
import { ref } from 'vue'
const name = ref('')
const email = ref('')
const message = ref('')

const submitForm = () => {
  console.log('Contact form submitted:', { name: name.value, email: email.value, message: message.value })
  // Add your submission logic here (API call, notification, etc.)
}
</script>

<style scoped>
.contact-section {
  max-width: 600px;
  margin: 3rem auto;
  padding: 2rem 1rem;
  background: linear-gradient(135deg, #e6f4ea, #ffffff);
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(22, 101, 52, 0.15);
  font-family: 'Poppins', sans-serif;
  color: #1f2937;
}

.section-title {
  text-align: center;
  font-size: 2.25rem;
  font-weight: 700;
  color: #166534;
  margin-bottom: 1rem;
}

.contact-intro {
  text-align: center;
  color: #374151;
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

.contact-info-list {
  list-style: none;
  padding: 0;
  margin-bottom: 2.5rem;
  text-align: center;
  font-size: 1rem;
  color: #256029;
}

.contact-info-list li {
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.contact-form input,
.contact-form textarea {
  padding: 0.8rem 1rem;
  font-size: 1rem;
  border: 2px solid #a7d7a7;
  border-radius: 8px;
  resize: vertical;
  transition: border-color 0.3s ease;
  font-family: 'Poppins', sans-serif;
}

.contact-form input:focus,
.contact-form textarea:focus {
  border-color: #166534;
  outline: none;
  box-shadow: 0 0 8px rgba(22, 101, 52, 0.3);
}

.contact-form textarea {
  min-height: 120px;
}

.contact-form button {
  background-color: #15803d;
  color: white;
  font-weight: 700;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1.1rem;
  transition: background-color 0.3s ease;
}

.contact-form button:hover {
  background-color: #166534;
}
</style>
