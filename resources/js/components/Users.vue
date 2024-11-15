<template>
  <master-component>
    <ShowListing
      title="Users"
      :columns="['id', 'name', 'email']"
      :data="users.data"
      :currentPage="currentPage"
      :totalPages="totalPages"
      @page-changed="fetchUsers"
    />
  </master-component>
</template>

<script>
import axios from "axios";
import MasterComponent from "./layouts/Master.vue";
import ShowListing from "@/components/ShowListing.vue";

export default {
  name: "Users",
  components: {
    MasterComponent,
    ShowListing,
  },
  data() {
    return {
      users: { data: [] },
      currentPage: 1,
      totalPages: 1,
    };
  },
  mounted() {
    this.fetchUsers(this.currentPage);
  },
  methods: {
    async fetchUsers(page = 1) {
      try {
        const response = await axios.get(`/api/users/${page}`);
        this.users = response.data;
        this.currentPage = response.data.current_page;
        this.totalPages = response.data.last_page;
      } catch (error) {
        console.error("Error fetching users:", error);
      }
    },
  },
};
</script>

  <style scoped>
  .users-page {
    padding: 20px;
  }
  
  .users-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .users-table th, .users-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }
  
  .users-table th {
    background-color: #f4f4f4;
    font-weight: bold;
  }
  </style>
  