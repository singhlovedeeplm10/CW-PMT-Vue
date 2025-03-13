<!-- resources/js/components/Footer.vue -->
<template>
  <footer class="footer">
    <h2 class="footer-title">© 2025 Contriwhiz Technologies.All rights reserved.</h2>
  </footer>
</template>

<script>
export default {
  name: 'FooterComponent',
};
</script>

<style scoped>
.footer {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 60px;
  background-color: #24292e;
  color: #fff;
  font-family: 'Arial', sans-serif;
  
}

.footer-title {
  font-size: 1.5rem;
  text-align: center;
  color: #f1c40f;
  margin: 0;
}

.footer-title:hover {
  color: #3498db;
  transition: color 0.3s ease;
}
</style>
