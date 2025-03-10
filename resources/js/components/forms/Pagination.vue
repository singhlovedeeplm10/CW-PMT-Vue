<template>
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
        </li>
        <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: page === currentPage }">
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
        </li>
      </ul>
    </nav>
  </template>
  
  <script>
  export default {
    props: {
      totalPages: {
        type: Number,
        required: true
      },
      currentPage: {
        type: Number,
        required: true
      }
    },
    methods: {
      changePage(page) {
        if (page >= 1 && page <= this.totalPages) {
          this.$emit('page-changed', page);
        }
      }
    }
  };
  </script>
  
  <style scoped>
  .pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
  }

  .page-item .page-link {
    border-radius: 8px;
    padding: 10px 15px;
    border: none;
    transition: all 0.3s ease;
    font-weight: bold;
    color: #007bff;
    background: #f8f9fa;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
  }

  .page-item .page-link:hover {
    background: #007bff;
    color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  .page-item.active .page-link {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.4);
  }

  .page-item.disabled .page-link {
    cursor: not-allowed;
    opacity: 0.6;
    background: #e9ecef;
    color: black;
  }
</style>
