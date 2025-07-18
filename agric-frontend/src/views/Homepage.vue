<template>
<div class="homepage font-poppins text-gray-800">
    <!-- Navbar -->
    <header class="navbar">
      <div class="navbar-container">
        <div class="logo-wrapper">
  <div class="background-blur"></div>
  <div class="logo">
    <img src="@/assets/logo3.png" alt="Logo" />
    <div>
      <h1 class="logo-title">FASI MARKET</h1>
      <p class="logo-subtitle">Agriculture Farming</p>
    </div>
  </div>
</div>

        <!-- Navigation Links -->
        <nav class="nav-links">
          <a href="#home" @click.prevent="scrollToSection('home')">Home</a>
          <a href="#about" @click.prevent="scrollToSection('about')">About</a>
          <a href="#services" @click.prevent="scrollToSection('services')">Services</a>
          <a href="#projects" @click.prevent="scrollToSection('projects')">Projects</a>
          <a href="#news" @click.prevent="scrollToSection('news')">News</a>
          <a href="#shop" @click.prevent="scrollToSection('shop')">Shop</a>
          <a href="#contact" @click.prevent="scrollToSection('contact')">Contact</a>
        </nav>

        <!-- Contact Info -->
        <div class="top-contact">
          <div class="contact-block">
            <img src="@/assets/phone-icon.png" alt="Phone" />
            <div>
              <p class="small-title">Call anytime</p>
              <p class="bold-info">+90 532 000 0000</p>
            </div>
          </div>
          <div class="contact-block">
            <img src="@/assets/email-icon.png" alt="Email" />
            <div>
              <p class="small-title">Send email</p>
              <p class="bold-info">support@fasi-market.com</p>
            </div>
          </div>
          <div class="contact-block">
            <img src="@/assets/location-icon.png" alt="Location" />
            <div>
              <p class="small-title">Our Location</p>
              <p class="bold-info">North Cyprus</p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
    
      <div class="hero-overlay">
        <div class="hero-content text-center">
          <h1 class="hero-slogan">
            Green life is<br />
            Harvesting Health<br />
            and Growing Trust
          </h1>

          <div class="hero-buttons">
            <a href="/login" class="login-btn">Login</a>
            <button class="discover-btn">Discover More</button>
            <a href="/register" class="register-btn">Register</a>
          </div>
        </div>
      </div>

<!-- Decorative Wave at Bottom -->
<div class="wave-bottom">
  <svg viewBox="0 0 1440 100" preserveAspectRatio="none">
    <path
      d="M0,50 
         C120,90 240,10 360,50 
         C480,90 600,10 720,50 
         C840,90 960,10 1080,50 
         C1200,90 1320,10 1440,50 
         L1440,100 L0,100 Z"
      fill="#f5f5f5"
    />
  </svg>
</div>

    </section>


<!-- MAIN CONTENT WITH WHITE BACKGROUND -->
    <div class="main-content">

  <section class="offer-section font-poppins">
    <h2 class="section-title">What We Offer</h2>
    <div class="offer-grid">
      <div
        class="offer-card"
        v-for="(item, index) in offers"
        :key="index"
      >
        <!-- Blurred image background -->
        <div
          class="offer-blur-bg"
          :style="{ backgroundImage: `url(${item.image})` }"
        ></div>

        <!-- Text overlay -->
        <div class="overlay">
          <h3 class="offer-title">{{ item.title }}</h3>
        </div>
      </div>
    </div>
  </section>


<section id="about" class="about-section font-poppins">
  <div class="about-wrapper">
    <!-- Left Image -->
    <div class="about-image">
      <img :src="image" alt="Farmer in barn" />
      <img :src="image2" alt="Farmer with crops" class="second-image" />
    </div>

    <!-- Right Content -->
    <div class="about-content">
      <h2 class="about-heading">Agriculture & Organic Product Farm</h2>
      <p class="about-description">
FASI Market is a modern agricultural marketplace that empowers local farmers by connecting them directly with consumers. Our platform eliminates middlemen, promotes fair trade, and ensures access to fresh, organic, and locally-grown products. Whether you're a farmer looking to sell your harvest or a consumer seeking quality food straight from the source, FASI Market provides a trusted space for transparent, one-on-one transactions.

Grow. Connect. Thrive — with FASI Market.      </p>

      <div class="about-stat">
        <span class="stat-value">25M</span>
        <span class="stat-label">Growth Tons<br />of Harvest</span>
      </div>

      <div class="about-features">
        <div class="feature-box yellow-box">
          <img :src="icon1" alt="icon" />
          <div>
            <h3>100% Guaranteed Organic Product</h3>
            <p>Always best farm buying the steering of moment.</p>
          </div>
        </div>

        <div class="feature-box green-box">
          <img :src="icon2" alt="icon" />
          <div>
            <h3>Top-Quality Healthy Foods Production</h3>
            <p>Majority have suffered alteration in some form by injected humour.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="services-section font-poppins">
  <div class="services-container">
    <h2 class="services-title">What We Do</h2>

    <div class="service-cards">
      <!-- Card 1: Direct Farmer Access -->
      <div class="service-card large-card">
        <img :src="service1" alt="Direct Farmer Access" />
        <div class="card-content">
          <h3>Direct Connections Between Farmers & Consumers</h3>
          <p>
            FASI Market bridges the gap between local farmers and buyers, enabling personal, fair, and transparent transactions without middlemen.
          </p>
          
        </div>
      </div>

      <!-- Card 2: Eco-Friendly Distribution -->
      <div class="service-card">
        <img :src="service2" alt="Eco-Friendly Distribution" />
        <div class="card-date">
          <span class="day">26</span>
          <span class="month">Aug, 2023</span>
        </div>
        <div class="card-content">
          <h4>Green & Local Product Delivery</h4>
          <p class="meta">Fasi-Market • 25 April, 2023</p>
          <p>
            We support sustainable transport and local logistics, ensuring your food travels responsibly from farm to table.
          </p>
          
        </div>
      </div>

      <!-- Card 3: Certified Organic Products -->
      <div class="service-card">
        <img :src="service3" alt="Certified Organic Products" />
        <div class="card-date green-bg">
          <span class="day">08</span>
          <span class="month">Dec, 2023</span>
        </div>
        <div class="card-content">
          <h4>Fresh & Verified Organic Produce</h4>
          <p class="meta">Fasi-Market • 25 April, 2023</p>
          <p>
            All products sold are vetted to meet organic farming standards — clean, chemical-free, and grown with care.
          </p>
          
        </div>
      </div>
    </div>
  </div>
</section>


<!-- PROJECTS SECTION -->
<section id="projects" class="projects-section font-poppins">
  <div class="projects-container">
    <h2 class="projects-title">Our Projects</h2>
    <p class="projects-intro">
      At Fasi-Market, we actively invest in community-driven agricultural innovation and education:
    </p>

    <!-- Horizontally scrollable project cards -->
    <div class="scroll-wrapper">
      <div class="project-cards horizontal-scroll">
        <!-- Project 1 -->
        <div class="project-card">
          <img :src="project1" alt="Organic Farm Training" />
          <div class="card-body">
            <h3>Organic Farm Training</h3>
            <p>Empowering farmers with modern organic techniques to grow healthy, sustainable crops.</p>
          </div>
        </div>

        <!-- Project 2 -->
        <div class="project-card">
          <img :src="project2" alt="Women in Agriculture" />
          <div class="card-body">
            <h3>Women in Agriculture</h3>
            <p>Mentoring and equipping female farmers with tools, resources, and direct market access.</p>
          </div>
        </div>

        <!-- Project 3 -->
        <div class="project-card">
          <img :src="project3" alt="School Garden Projects" />
          <div class="card-body">
            <h3>School Garden Projects</h3>
            <p>Teaching students the value of sustainability and nutrition through hands-on gardening.</p>
          </div>
        </div>

        <!-- Project 4 -->
        <div class="project-card">
          <img :src="project4" alt="Tech for Farming" />
          <div class="card-body">
            <h3>Tech for Farming</h3>
            <p>Providing mobile tools and apps to help farmers manage pricing, inventory, and logistics.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<!-- Shop Section -->
 <section id="shop" class="section ask-section">
    <div class="shop-container">
      <h1 class="shop-title">Shop Fresh Products</h1>
      <p class="shop-intro">Buy direct from farmers — fresh, organic, and fairly priced.</p>

      <div class="shop-grid">
        <div class="product-card">
          <h3>🥦 Vegetables</h3>
          <p>Pesticide-free greens and root crops.</p>
        </div>
        <div class="product-card">
          <h3>🍎 Fruits</h3>
          <p>Seasonal fruits from trusted farms.</p>
        </div>
        <div class="product-card">
          <h3>🧀 Dairy</h3>
          <p>Fresh milk, yogurt, and cheese.</p>
        </div>
        <div class="product-card">
          <h3>🌿 Fertilizers</h3>
          <p>Organic and natural soil boosters.</p>
        </div>
      </div>
    </div>
    
  </section>

    <!-- Eco Message Banner (Minimal) -->
<section class="eco-banner" :style="{ backgroundImage: `url(${ecoBanner})` }">
  <div class="eco-box">
    <h2 class="eco-title">Eco-Friendly from the Ground Up 🌱</h2>
    <p class="eco-sub">Sustainable practices for a better tomorrow.</p>
  </div>
</section>

<!-- Compact Contact Section -->
<section id="contact" class="section contact-section">
  <h2 class="section-title">Send Us a Message</h2>
  <form class="contact-form">
    <div class="form-group">
      <input type="text" placeholder="Your name" />
      <input type="email" placeholder="Email address" />
    </div>
    <textarea placeholder="Your message"></textarea>
    <button type="submit">Send</button>
  </form>
</section>

  </div>
    </div>
</template>

<script setup>
import agriculture from '@/assets/agricultural products.jpg';
import organic from '@/assets/organic.jpg';
import dairy from '@/assets/dairy-prod.png';
import vegetable from '@/assets/vegetable2.jpg';
import fruitBasket from '@/assets/fruits.png';
import fertilizers from '@/assets/fertilizers.jpg';

const offers = [
  { image: agriculture, title: 'Agriculture Products', description: 'Fresh and quality products from trusted farmers.' },
  { image: organic, title: 'Organic Products', description: 'Certified organic food grown sustainably.' },
  { image: dairy, title: 'Dairy Products', description: 'Milk, cheese, and other fresh dairy goods.' },
  { image: vegetable, title: 'Fresh Vegetables', description: 'Locally grown and pesticide-free vegetables.' },
  { image: fruitBasket, title: 'Seasonal Fruits', description: 'Delicious fruits harvested at peak ripeness.' },
  { image: fertilizers, title: 'Natural Fertilizers', description: 'Eco-friendly compost and soil boosters for farmers.' },
];

import image from '@/assets/image.jpg';
import image2 from '@/assets/image2.jpg'
import icon1 from '@/assets/icon1.png';
import icon2 from '@/assets/icon2.png';
import service1 from '@/assets/service1.jpg';
import service2 from '@/assets/service2.jpg';
import service3 from '@/assets/service3.jpg';
import project1 from '@/assets/project-organic.jpg';
import project2 from '@/assets/project-women.jpg';
import project3 from '@/assets/project-school.jpg';
import project4 from '@/assets/project-tech.jpg';

import ecoBanner from '@/assets/eco-banner.jpg';
const scrollToSection = (id) => {
  const el = document.getElementById(id);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<style scoped>

/* 👇 ADD THIS SECTION TO YOUR CSS */
.main-content {
  background-color: white;
  position: relative;
  z-index: 1;
}




/* Nav styles inside the band */
.nav-links {
  display: flex;
  gap: 3rem;
  font-weight: 600;
}

.nav-links a {
  color: white;
  text-decoration: none;
  transition: color 0.3s;
}

.nav-links a:hover {
  color: #a3f7a3;
}

/* Navbar */
.navbar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 50;
  background-color: rgba(0, 0, 0, 0); /* Dark transparent overlay */
  padding: 1rem 2rem;
  color: white;
}


.navbar-container {
  display: flex;
  justify-content: space-between; /* or flex-start depending on layout */
  align-items: center;
  padding: 0 1rem;
}

html, body {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
#app {
  margin: 0;
  padding: 0;
}

.homepage {
  color: grey;
  margin: 0;
  padding: 0;
  width: 100vw;
  min-height: 100vh;
  overflow-x: hidden;
}

.nav-links {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
  font-weight: 600;
}

.nav-links router-link {
  color: black;
  text-decoration: none;
  font-size: 1rem;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  transition: background-color 0.3s, color 0.3s;
}

.nav-links router-link:hover {
  background-color: #15803d;
  color: white;
}
.logo {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 0.5rem;
  background: transparent; /* ensure no background on container */
}

.logo img {
  background-color: transparent !important; /* make sure no background */
  border: none !important;                 /* remove any border */
  box-shadow: none !important;             /* remove shadow */
  outline: none !important;                /* remove outline */
  padding: 0;
  margin: 0;
  max-height: 80px;
  display: block;
}


.logo-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
}

.logo-subtitle {
  font-size: 0.75rem;
  color:rgb(255, 252, 252);
  margin: 0;
}
.logo-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.background-blur {
  position: absolute;
  inset: 0;
  backdrop-filter: blur(10px); /* applies blur */
  -webkit-backdrop-filter: blur(10px);
  z-index: 0;
  border-radius: 10px; /* optional rounded corners */
}

.logo {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}


/* Contact Info */
.top-contact {
  display: flex;
  gap: 1.25rem;
  flex-wrap: wrap;
  align-items: center;
  margin-left: auto; /* pushes it toward the right */
  margin-right: 1rem; /* optional, adds spacing before login/register */
}


.contact-block {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.contact-block img {
  width: 18px;
  height: 18px;
}

.small-title {
  font-size: 0.7rem;
  color: #64748b;
  margin: 0;
}

.bold-info {
  font-weight: 600;
  font-size: 0.85rem;
  margin: 0;
}
body {
  background-color:rgba(247, 249, 247, 0.48); /* a soft off-white/gray-green color */
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.hero-overlay {
  position: relative;
  z-index: 1;
  background-color: rgba(0, 0, 0, 0);
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: flex-start; /* shift content down */
  padding: 12rem 5rem 2rem 2rem; /* add top padding to push content down */
  text-align: center;
  flex-direction: column;
}

.hero-slogan {
  font-size: 2.5rem;
  font-weight: 800;
  font-family: 'Dancing Script', cursive;


  text-transform: uppercase;
  letter-spacing: 2px;
  line-height: 1.2;

  background: rgb(255, 255, 255);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}



.hero-content {
  color: white;
  max-width: 800px;
  padding: 0;
  display: flex;
  flex-direction: column;
  align-items: center; /* center horizontally */
  font-family: 'Dancing Script', cursive;

}

.hero-buttons {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.discover-btn,
.login-btn,
.register-btn {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 700;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  text-decoration: none;
  color: white;
  display: inline-block;
  transition: background-color 0.3s ease;
  background-color: rgb(223, 187, 9); /* Same yellow for all */
}

.discover-btn:hover,
.login-btn:hover,
.register-btn:hover {
  background-color: rgb(223, 187, 9); /* Same yellow for all */
}


.hero-section {
 position: relative; /* so the ::before absolute is relative to this section */
  overflow: hidden;   /* optional: clip any overflow */
  width: 100vw;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  height: 120vh;
  overflow: hidden;
  margin: 0;
  padding: 0;
}

/* 👇 ADJUST THIS EXISTING PART IN YOUR STYLES */
.hero-section::before {
  content: "";
  position: fixed; /* ← changed from fixed */
  width: 100vw;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: url('@/assets/background-image.jpg');
  background-size: cover;
  background-position: center;
  filter: contrast(135%) brightness(30%) saturate(95%);
  z-index: -1;
  transform: scale(1);
}


.hero-content {
  color: white;
  max-width: 800px;
  font-family: 'Dancing Script', cursive;

}

.hero-desc {
  font-size: 1.125rem;
  margin-bottom: 2rem;
}



.about-features {
  list-style: none;
  padding-left: 0;
  margin: 0;
}

.about-features li {
  margin-bottom: 0.5rem;
  font-size: 1rem;
  color: #475569;
  padding-left: 0;
}
.nav-links {
  display: flex;
  gap: 2.5rem;
  font-weight: 1500;
  font-size: 17px;
  margin-left: auto;        /* PUSH nav-links as far right as possible */
  font-family: 'Dancing Script', cursive;

}

.nav-links a {
  color: white;
  text-decoration: none;
  transition: color 0.3s;
  white-space: nowrap;
    font-family: 'Dancing Script', cursive;

}

.nav-links a:hover {
  color:rgb(7, 194, 7);
}
.auth-buttons {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-left: auto;
}

.wave-bottom {
  position: absolute;
  bottom: -1px;
  width: 100%;
  height: 120px;
  overflow: hidden;
  line-height: 0;
  z-index: 2;
}

.wave-bottom svg {
  display: block;
  width: 100%;
  height: 100%;
}


/* Keep italic styling for hero text */
.welcome-text,
.hero-title,
.hero-desc {
  font-style: italic;
  width: 1000px;
  font-size: 1.6rem;
  font-family: 'Dancing Script', cursive;


}
/* Sections */
.section {
  padding: 4rem 1rem;
  max-width: 1280px;
  margin: auto;
}


/* Ask Questions */
.ask-section {
  padding: 3rem 1rem;
  max-width: 960px;
  margin: auto;
}

.section-title {
  text-align: center;
  font-size: 1.75rem;
  margin-bottom: 2rem;
  font-weight: bold;
  color: #1e293b;
  font-family: 'Dancing Script', cursive;

}
/* Lift all main section titles upward */
.section-title,
#about h1,
.services-title,
.projects-title,
.shop-title,
.news-section .section-title {
  margin-top: -1.5rem;
  margin-bottom: 1.5rem;
  font-family: 'Dancing Script', cursive;

}

/* Eco Banner */
.eco-banner {
  position: relative;
  height: 40vh;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: white; /* ensures text is readable on the image */
  padding: 2rem 1rem;
  z-index: 1;
  font-family: 'Dancing Script', cursive;

}

.eco-banner::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.45); /* dark overlay for contrast */
  z-index: 0;
}

.eco-box {
  position: relative;
  z-index: 1;
  max-width: 800px;
  margin: auto;
}

.eco-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  font-family: 'Dancing Script', cursive;

}

.eco-sub {
  font-size: 1.125rem;
  font-weight: 400;
  font-family: 'Dancing Script', cursive;

}

/* Contact Form */
.contact-section {
  padding: 3rem 1rem;
  max-width: 640px;
  margin: auto;
  text-align: center;
  
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: #1e293b;
  font-family: 'Dancing Script', cursive;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  font-family: 'Dancing Script', cursive;
}

.form-group {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.form-group input {
  flex: 1 1 240px;
}

.contact-form input,
.contact-form textarea {
  padding: 0.65rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.875rem;
  width: 100%;
  font-family: 'Dancing Script', cursive;
}

.contact-form textarea {
  min-height: 100px;
  resize: vertical;
}

.contact-form button {
  align-self: center;
  background-color: #15803d;
  color: white;
  padding: 0.6rem 1.5rem;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.contact-form button:hover {
  background-color: #166534;
}


/* What We Offer Section */
.offer-section {
  padding: 80px 20px;
  background-color: #f8f9fa;
  text-align: center;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 60px;
  position: relative;
  font-family: 'Dancing Script', cursive;

}


.section-title::before {
  content: "Our Services";
  position: absolute;
  top: -55px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 0.9rem;
  color: #f39c12;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-family: 'Dancing Script', cursive;

}

/* Card Grid Layout */
.offer-grid {
  display: grid;
 grid-template-columns: repeat(3, 1fr);
  gap: 2px;
  max-width: 1200px;
  margin: 0 auto;
  top: -55px;

}

/* Card Styles */
.offer-card {
  height: 360px;
  background-size: cover;
  background-position: center;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  position: relative;
  display: flex;
  align-items: flex-end;
  transition: transform 0.3s ease;
  cursor: pointer;
}

/* Blurred background layer */
.offer-blur-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  filter: contrast(175%) brightness(20%) saturate(95%);
  transform: scale(1.05); /* Slight scale to avoid edge clipping */
  z-index: 0;
}

/* Text overlay layer */
.overlay {
  position: relative;
  z-index: 1;
  width: 100%;
  padding: 1rem;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.6), transparent);
}

.offer-title {
  color: #fff;
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  text-align: left;
  font-family: 'Dancing Script', cursive;

}

/* Responsive */
@media (max-width: 900px) {
  .offer-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .offer-grid {
    grid-template-columns: 1fr;
  }
  .offer-card {
    height: 300px;
  }
}

/* About Section Styling */
.about-section {
  padding: 60px 20px;
  background-color: #f9fafb;
}

.about-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
  align-items: center;
  justify-content: center;
  max-width: 1200px;
  margin: auto;
}

.about-image img {
 width: 100%;
  max-width: 500px;
  border-radius: 20px;
  object-fit: cover;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  gap: 2rem;
  filter: contrast(175%) brightness(20%) saturate(95%);
}


.about-image img {
  width: 100%;
  border-radius: 12px;
  object-fit: cover;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  filter: contrast(175%) brightness(20%) saturate(95%);
}

.second-image {
  margin-top: 2rem;
  width: 80%;
  filter: contrast(175%) brightness(20%) saturate(95%);
}


.about-content {
   margin-top: -130px; /* adjust as needed */

  flex: 1 1 500px;
  padding: 20px;
  font-family: 'Dancing Script', cursive;

}

.about-heading {
  font-size: 2.25rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #1a202c;
  font-family: 'Dancing Script', cursive;

}

.about-description {
  font-size: 1rem;
  color: #4a5568;
  line-height: 1.8;
  margin-bottom: 2rem;
  font-family: 'Dancing Script', cursive;

}

.about-stat {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  font-family: 'Dancing Script', cursive;

}

.stat-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #38a169;
}

.stat-label {
  font-size: 0.9rem;
  color: #718096;
}

.about-features {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  font-family: 'Dancing Script', cursive;

}

.feature-box {
  display: flex;
  align-items: flex-start;
  background-color: #ffffff;
  border-radius: 15px;
  padding: 1rem;
  gap: 1rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.feature-box img {
  width: 40px;
  height: 40px;
}

.feature-box h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.feature-box p {
  font-size: 0.9rem;
  color: #4a5568;
}

.yellow-box {
  border-left: 6px solid #f6ad55;
}

.green-box {
  border-left: 6px solid #48bb78;
}

/* Responsive */
@media (max-width: 768px) {
  .about-wrapper {
    flex-direction: column;
  }

  .about-content {
    padding: 0;
  }
}

.services-section {
  background-color: #fff;
  padding: 4rem 2rem;
}

.services-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 2.5rem;
  text-align: center;
  color: #1a1a1a;
    font-family: 'Dancing Script', cursive;

}

.service-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
  justify-content: center;
}

.service-card {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  max-width: 300px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
  position: relative;
  transition: transform 0.3s ease;
}

.service-card:hover {
  transform: translateY(-5px);
}

.service-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.large-card {
  flex: 1;
  width: 100%;
  display: flex;
  flex-direction: column;
}

.large-card .card-content {
  padding: 2.5rem;
}

.large-card h3 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #1f2937;
}

.service-card .card-content {
  padding: 1rem 1.2rem 1.5rem;
}

.service-card h4 {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0.8rem 0;
  color: #1f2937;
}

.meta {
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 1rem;
}


.card-date {
  position: absolute;
  top: 12px;
  left: 12px;
  background-color: #facc15;
  padding: 0.5rem 0.8rem;
  border-radius: 6px;
  text-align: center;
  color: #000;
  font-weight: 700;
}

.card-date.green-bg {
  background-color: #bbf7d0;
}

.card-date .day {
  font-size: 1.25rem;
  line-height: 1.2;
}

.card-date .month {
  font-size: 0.75rem;
}
.projects-section {
  background-color: #f0fdf4;
  padding: 4rem 1rem;
  text-align: center;
}

.projects-title {
  font-size: 2.5rem;
  font-weight: 700;
  color:rgb(9, 9, 9);
  margin-bottom: 1rem;
}

.projects-intro {
  font-size: 1rem;
  color: #4a5568;
  max-width: 700px;
  margin: 0 auto 3rem;
}

.project-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 0-2rem;
  justify-items: center;
}
.scroll-wrapper {
  overflow-x: auto;
  overflow-y: hidden;
  padding-bottom: 1rem;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-height: auto;
}

.scroll-wrapper::-webkit-scrollbar {
  height: 10px;
}

.scroll-wrapper::-webkit-scrollbar-thumb {
  background-color: #888;
  border-radius: 10px;
}

.scroll-wrapper::-webkit-scrollbar-thumb:hover {
  background-color: #555;
}

.scroll-wrapper::-webkit-scrollbar-track {
  background: #eee;
  border-radius: 10px;
}


.project-card {
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
  max-width: 350px;
  text-align: left;
}

.project-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.card-body {
  padding: 1.25rem;
}

.card-body h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.card-body p {
  font-size: 0.95rem;
  color: #4a5568;
}

/* Main News Section Container */
.news-section {
  text-align: center;
  padding: 3rem 1.5rem;
  background: #f0fdf4;
  font-family: 'Poppins', sans-serif;
}

/* Title */
.news-title {
  font-size: 2.2rem;
  font-weight: 800;
  margin-bottom: 1rem;
  font-family: 'Dancing Script', cursive;
  background: linear-gradient(to right, #16a34a, #4ade80);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-align: center;
}

/* Optional Subtitle */
.news-subtitle {
  font-size: 1.05rem;
  color: #475569;
  margin-bottom: 2.5rem;
  max-width: 700px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
  font-style: italic;
}

.shop-section {
  background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
  padding: 3rem 1.5rem;
  color: #1f2937; /* gray-800 */
  font-family: 'Poppins', sans-serif;
}

.shop-container {
  max-width: 96rem; /* 6xl ~ 960px */
  margin: 0 auto;
}

.shop-title {
  font-size: 2rem;
  font-weight: 700;
  color:rgb(8, 8, 8); /* green-800 */
  margin-bottom: 1.5rem;
  text-align: center;
}

.shop-intro {
  text-align: center;
  margin-bottom: 2rem;
  font-size: 1rem;
  color: #374151; /* gray-700 */
}

/* Grid for products */
.shop-grid {
  display: grid;
  gap: 1.5rem;
  grid-template-columns: repeat(1, 1fr);
}

@media (min-width: 640px) {
  .shop-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Remove or comment out the 3-column layout */
@media (min-width: 768px) {
  .shop-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}


.product-card {
  background-color: white;
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(22, 101, 52, 0.1);
  text-align: center;
  transition: box-shadow 0.3s ease;
  cursor: default;
}

.product-card:hover {
  box-shadow: 0 8px 20px rgba(22, 101, 52, 0.15);
}

.product-card h3 {
  font-weight: 600;
  color:rgb(9, 9, 9); /* green-600 */
  margin-bottom: 0.5rem;
  font-size: 1.125rem;
}

.product-card p {
  font-size: 0.9rem;
  color: #4b5563; /* gray-600 */
  margin: 0;
}


</style>
