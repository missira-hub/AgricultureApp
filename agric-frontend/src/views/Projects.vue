<template>
  <section class="projects-section">
    <h1 class="projects-title">Our Projects</h1>
    <p class="projects-intro">
      At Fasi-Market, we actively invest in community-based agricultural initiatives and innovations:
    </p>
    <ul class="projects-list">
      <li>
        <span class="checkmark">✅</span>
        <p><strong>Organic Farm Training</strong> – Empowering farmers with knowledge on organic practices.</p>
      </li>
      <li>
        <span class="checkmark">✅</span>
        <p><strong>Women in Agriculture</strong> – Supporting female farmers through mentorship and market access.</p>
      </li>
      <li>
        <span class="checkmark">✅</span>
        <p><strong>School Garden Projects</strong> – Educating youth on sustainability and healthy eating.</p>
      </li>
      <li>
        <span class="checkmark">✅</span>
        <p><strong>Tech for Farming</strong> – Introducing mobile tools and apps to help farmers manage inventory and pricing.</p>
      </li>
    </ul>
  </section>
</template>

<style scoped>
.projects-section {
  max-width: 720px;
  margin: 4rem auto;
  padding: 2rem;
  background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(22, 101, 52, 0.1);
  text-align: center;
  color: #1e3a16;
  font-family: 'Poppins', sans-serif;
}

.projects-title {
  font-size: 2.25rem;
  font-weight: 700;
  color: #166534;
  margin-bottom: 1.5rem;
  text-shadow: 0 1px 2px rgba(22, 101, 52, 0.3);
}

.projects-intro {
  font-size: 1.1rem;
  color: #4b5563;
  margin-bottom: 2.5rem;
  line-height: 1.6;
}

.projects-list {
  list-style: none;
  padding: 0;
  margin: 0 auto;
  max-width: 480px;
  text-align: left;
}

.projects-list li {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.75rem;
  background: #f9fafb;
  padding: 1rem 1.25rem;
  border-radius: 10px;
  box-shadow: 0 3px 8px rgba(22, 101, 52, 0.05);
  transition: box-shadow 0.3s ease;
}

.projects-list li:hover {
  box-shadow: 0 6px 15px rgba(22, 101, 52, 0.15);
}

.checkmark {
  font-size: 1.8rem;
  color: #22c55e; /* bright green */
  user-select: none;
  margin-top: 0.2rem;
  text-shadow: 0 1px 3px rgba(34, 197, 94, 0.4);
}

.projects-list p {
  font-size: 1rem;
  color: #374151;
  margin: 0;
}

.projects-list strong {
  color: #15803d;
  font-weight: 600;
}
</style>
