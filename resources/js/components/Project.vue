<!-- Project.vue -->
<template>
    <master-component>
        <div class="project-page">
            <h1>Projects Page</h1>
            <p>This is the Projects page content.</p>
        </div>
    </master-component>
</template>
  
  <script>
  import MasterComponent from './layouts/Master.vue';
  export default {
    name: "Project",
    components: {
    MasterComponent,
  },
  };
  </script>
  
  <style scoped>
  /*  */
  </style>
  