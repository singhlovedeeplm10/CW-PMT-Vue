<template>
  <div class="notice-container">
    <div
      v-for="(notice, index) in notices"
      :key="index"
      class="card mb-4 p-4 flex items-start"
    >
      <div class="avatar-container"></div>
      <div class="content-container">
        <h5 class="title">{{ notice.title }}</h5>
        <p class="description" v-html="formatNoticeDescription(notice.description)"></p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ViewNotices",

  data() {
    return {
      notices: [],
    };
  },
  mounted() {
    this.getNotices();
  },
  methods: {
    async getNotices() {
      try {
        const response = await axios.get("/api/notices");
        this.notices = response.data;
      } catch (error) {
        console.error("Error fetching notices:", error);
      }
    },
    formatNoticeDescription(description) {
      if (!description) return "";

      // Create a temporary DOM element to manipulate the HTML
      const tempElement = document.createElement("div");
      tempElement.innerHTML = description;

      // Find all images and apply predefined styles
      const images = tempElement.querySelectorAll("img");
      images.forEach((img) => {
        img.style.maxWidth = "200px";
        img.style.height = "250px";
        img.style.borderRadius = "8px";
      });

      return tempElement.innerHTML;
    },
  },
};
</script>

  
  
  <style scoped>
  .notice-container{
    padding: 20px;
  }
  .card {
    width: 100%;
    /* display: flex;
    align-items: center; */
    gap: 20px;
    background-color: #fff; /* Light background */
    color: #333; /* Dark text for readability */
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }
  
  .avatar-container {
    flex-shrink: 0;
  }
  
  .avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f9a826; /* Accent color */
  }
  
  .content-container {
    flex: 1;
  }
  
  .title {
    font-size: 1.25rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
    color: #333;
  }
  
  .description {
    font-size: 1rem;
    line-height: 1.5;
    color: #555;
  }
  
  .mb-4 {
    margin-bottom: 1rem;
  }
  </style>
  