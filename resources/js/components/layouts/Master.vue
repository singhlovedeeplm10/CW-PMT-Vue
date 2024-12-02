<!-- resources/js/components/Master.vue -->
<template>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

  <div>
    <HeaderComponent />
    <div class="main-layout">
      <SidebarComponent />
      <main>
        <slot></slot>
      </main>
    </div>
    <FooterComponent />
  </div>
</template>

<script>
import HeaderComponent from './Header.vue';
import SidebarComponent from './Sidebar.vue';
import FooterComponent from './Footer.vue';

export default {
  name: 'MasterComponent',
  components: {
    HeaderComponent,
    SidebarComponent,
    FooterComponent,
  },
};
</script>

<style scoped>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f4f6f9;
}
.main-layout {
  display: flex;
}
main {
  flex: 1;
  /* padding: 20px; */
}
</style>
