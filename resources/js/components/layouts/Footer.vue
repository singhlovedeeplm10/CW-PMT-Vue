<template>
  <footer class="footer">
    <h2 class="footer-title">{{ currentYear }} Copyright © All rights reserved Contriwhiz Technologies Pvt Ltd.</h2>
  </footer>
</template>

<script>
export default {
  name: 'FooterComponent',
  data() {
    return {
      // Get the current year
      currentYear: new Date().getFullYear(),
    };
  },
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
  font-size: 15px;
  text-align: center;
  color: #f1c40f;
  margin: 0;
}
</style>
