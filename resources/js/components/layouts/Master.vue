<!-- resources/js/components/Master.vue -->
<template>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

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
.main-layout {
  display: flex;
  margin-top: 75px;
}

main {
  flex: 1;
  margin-left: 190px;
  /* Match sidebar width */
  padding: 20px;
  min-height: calc(100vh - 75px);
  /* Full height minus header */
}

@media (max-width: 768px) {
  .main-layout {
    flex-direction: column;
  }

  main {
    margin-left: 0;
    padding: 15px;
  }

  .sidebar {
    width: 100%;
    height: auto;
    max-height: 300px;
    /* Adjust for mobile */
    position: relative;
  }
}
</style>
