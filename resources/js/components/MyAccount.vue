<template>
    <master-component>
      <div class="myaccount-container">
        <!-- Header Section -->
        <div class="header" v-if="user">
          <div class="profile-image">
            <img :src="'/storage/' + user.user_image" alt="Profile Picture" />
          </div>
          <div class="profile-info">
            <h1>{{ user.name }}</h1>
            <p>{{ user.role }}</p>
          </div>
        </div>
  
        <!-- User Details Section -->
        <div class="details-container" v-if="user">
          <!-- Basic Information -->
          <div class="details-section" data-aos="fade-up">
            <h2>Basic Information</h2>
            <div class="info-row">
              <div class="info-item">
                <strong>Date of Birth:</strong>
                <span>{{ user.dob }}</span>
              </div>
              <div class="info-item">
                <strong>Status:</strong>
                <span>{{ user.status === '1' ? 'Active' : 'Inactive' }}</span>
              </div>
              <div class="info-item">
                <strong>Employee Code:</strong>
                <span>{{ user.employee_code }}</span>
              </div>
            </div>
          </div>
  
          <!-- Contact Information -->
          <div class="details-section" data-aos="fade-up" data-aos-delay="400">
            <h2>Contact Information</h2>
            <div class="info-row">
              <div class="info-item">
                <strong>Mobile Number:</strong>
                <span>{{ user.mobile }}</span>
              </div>
              <div class="info-item">
                <strong>Address:</strong>
                <span>{{ user.address }}</span>
              </div>
              <div class="info-item">
                <strong>Email:</strong>
                <span>{{ user.email }}</span>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Loader Section with Spinner -->
<div v-else class="loader">
  <div class="spinner"></div>
  Loading user information...
</div>

      </div>
    </master-component>
  </template>
  
  <script>
  import MasterComponent from "./layouts/Master.vue";
  import axios from "axios";
  
  export default {
    name: "MyAccount",
    components: {
      MasterComponent,
    },
    data() {
      return {
        user: null, // User data object
      };
    },
    mounted() {
      this.fetchUserData();
    },
    methods: {
      async fetchUserData() {
        try {
          const response = await axios.get("/api/user-profile", {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          });
          this.user = response.data;
        } catch (error) {
          console.error("Error fetching user data:", error);
        }
      },
    },
  };
  </script>
  
  
  <style scoped>
  /* Loader Styles */
.loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  color: #555;
  font-size: 1.2rem;
  font-weight: bold;
  background-color: #f7f7f7;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top: 4px solid #293e60;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

  /* Global Container */
  .myaccount-container {
    font-family: "Roboto", sans-serif;
    color: #333;
    background: #f7f7f7;
    min-height: 100vh;
  }
  
  /* Header Section */
  .header {
    background: #293e60;
    color: #fff;
    display: flex;
    align-items: center;
    padding: 2rem 3rem;
    gap: 2rem;
    border-bottom: 3px solid #ddd;
  }
  
  .profile-image img {
    border-radius: 50%;
    border: 4px solid #fff;
    width: 120px;
    height: 120px;
    object-fit: cover;
    transition: transform 0.3s ease;
  }
  
  .profile-image img:hover {
    transform: scale(1.05);
  }
  
  .profile-info h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
    text-transform: capitalize;
  }
  
  .profile-info p {
    font-size: 1rem;
    font-style: italic;
    margin: 0.5rem 0;
  }
  
  /* Details Container */
  .details-container {
    padding: 3rem 5%;
  }
  
  /* Details Section */
  .details-section {
    background: #fff;
    padding: 2rem;
    margin-bottom: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }
  
  .details-section h2 {
    font-size: 1.6rem;
    color: #293e60;
    font-weight: bold;
    margin-bottom: 1rem;
    border-bottom: 2px solid #ddd;
    padding-bottom: 0.5rem;
  }
  
  .info-row {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
  }
  
  .info-item {
    flex: 1 1 48%;
    margin-bottom: 1rem;
  }
  
  .info-item strong {
    color: #444;
    font-weight: bold;
  }
  
  .info-item span {
    color: #777;
    font-weight: 400;
    display: block;
    margin-top: 0.5rem;
  }
  
  @media (max-width: 768px) {
    .info-row {
      flex-direction: column;
    }
    .info-item {
      flex: 1 1 100%;
    }
  }
  </style>
  