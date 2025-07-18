<template>
  <div class="notice-container">
    <!-- Notice Cards -->
    <div v-for="(notice, index) in notices" :key="index"
      class="card mb-4 p-4 flex items-start shadow-lg transition-transform hover:scale-105"
      :class="{ 'birthday-card': notice.user_image }">
      <!-- Happy Birthday Banner -->
      <div v-if="notice.user_image" class="birthday-banner">
        🎉 Happy Birthday! 🎈
      </div>

      <!-- User Image for Birthday Notices -->
      <div class="flex items-center space-x-4"
        :class="{ 'birthday-layout': notice.user_image, 'default-layout': !notice.user_image }">
        <!-- User Image for Birthday Notices -->
        <img v-if="notice.user_image" :src="`/uploads/${notice.user_image}`" alt="User Image" class="birthday-avatar" />

        <div class="content-container">
          <h5 class="text-2xl font-bold">
            <span v-html="notice.title"></span>
          </h5>
          <p class="description" v-html="formatNoticeDescription(notice.description)"></p>
        </div>
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
.birthday-layout {
  display: flex;
  align-items: flex-start;
  flex-direction: row;
  gap: 20px;
}

.default-layout {
  display: flex;
  align-items: flex-start;
  gap: 20px;
}

.birthday-avatar {
  width: 96px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #ff9800;
  box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
  transition: transform 0.3s ease-in-out;
}

.birthday-card {
  background-color: #ffe082;
  color: #d84315;
  border: 2px solid #ff9800;
  box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
  transition: all 0.3s ease-in-out;
  position: relative;
  padding-top: 50px;
}

.birthday-banner {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(45deg, #ff4081, #ff9800);
  color: white;
  font-weight: bold;
  font-size: 14px;
  padding: 5px 15px;
  border-radius: 20px;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}

.birthday-card h5 {
  color: #c2185b;
  font-weight: 700;
}

.birthday-card .description {
  color: #7b1fa2;
  font-style: italic;
}

.card {
  border-radius: 15px;
  padding: 15px;
  display: flex;
  align-items: flex-start;
}

.shadow-lg {
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.notice-container {
  padding: 20px;
}

.card {
  width: 100%;
  gap: 20px;
  background-color: #fff;
  color: #333;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.avatar-container {
  flex-shrink: 0;
}

.avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #f9a826;
}

.content-container {
  flex: 1;
  margin-left: 0;
  max-width: 100%;
}

.content-container h5 {
  font-weight: bold;
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
