<template>
    <div class="listing-page">
      <h1>{{ title }}</h1>
      <table class="listing-table">
        <thead>
          <tr>
            <th v-for="column in columns" :key="column">
              {{ column }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in data" :key="item.id">
            <td v-for="column in columns" :key="column">
              {{ item[column] }}
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Pagination Component -->
      <pagination
        :totalPages="totalPages"
        :currentPage="currentPage"
        @page-changed="onPageChanged"
      />
    </div>
  </template>
  
  <script>
import Pagination from "@/components/forms/Pagination.vue";
  
  export default {
    name: "ShowListing",
    components: { Pagination },
    props: {
      title: {
        type: String,
        default: "Listing",
      },
      columns: {
        type: Array,
        required: true,
      },
      data: {
        type: Array,
        required: true,
      },
      currentPage: {
        type: Number,
        default: 1,
      },
      totalPages: {
        type: Number,
        default: 1,
      },
    },
    emits: ["page-changed"],
    methods: {
      onPageChanged(page) {
        this.$emit("page-changed", page);
      },
    },
  };
  </script>
  
  <style scoped>
  .listing-page {
    padding: 20px;
  }
  
  .listing-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .listing-table th,
  .listing-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }
  
  .listing-table th {
    background-color: #f4f4f4;
    font-weight: bold;
  }
  </style>
  