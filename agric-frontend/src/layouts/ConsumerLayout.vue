<template>
  <div class="layout">
    <Sidebar />
    <main class="content">
      <router-view />
    </main>
  </div>
</template>

<script>
import Sidebar from '@/components/Sidebar.vue';
export default {
  components: { Sidebar }
};
</script>

<style scoped>
.layout {
  display: flex;
}
.content {
  flex: 1;
  padding: 2rem;
  background: #f5f5f5;
  height: 100vh;
  overflow-y: auto;
}
</style>
