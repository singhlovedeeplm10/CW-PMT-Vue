<template>
  <header class="header">
    <nav class="nav">
      <div class="header-left">
        <router-link to="/dashboard" class="sidebar-link header-logo">
          <img src="img/CWlogo.jpeg" alt="Contriwhiz Logo" class="logo-image" />
          <h1 class="logo-title">Contriwhiz</h1>
        </router-link>
      </div>
      <div class="header-right">
        <!-- User Profile with Hover Dropdown -->
        <div class="profile-container">
          <img :src="userImage || '/img/CWlogo.jpeg'" alt="Profile Image" class="profile-image" />
          <div class="profile-dropdown">
            <a href="javascript:void(0)" @click="goToAccount" class="dropdown-item">My Account</a>
            <a href="javascript:void(0)" @click="logout" class="dropdown-item" :disabled="isLoggingOut">
              <span v-if="!isLoggingOut">Logout</span>
              <span v-if="isLoggingOut" class="spinner-border spinner-border-sm"></span>
            </a>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

export default {
  name: "HeaderComponent",
  computed: {
    ...mapGetters(["getUserDetails"]),
    userImage() {
      return this.getUserDetails.image; // Get user image from Vuex store
    },
  },
  setup() {
    const router = useRouter();
    const isLoggingOut = ref(false);

    const logout = async () => {
      if (isLoggingOut.value) return;
      isLoggingOut.value = true;

      try {
        await axios.post("/api/logout", {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        localStorage.removeItem("authToken");
        router.push("/");
      } catch (error) {
        console.error("Logout failed:", error);
        alert("An error occurred during logout.");
      } finally {
        isLoggingOut.value = false;
      }
    };

    const goToAccount = () => {
      router.push("/myaccount");
    };

    return {
      logout,
      goToAccount,
      isLoggingOut,
    };
  },
  methods: {
    ...mapActions(["fetchUserDetails"]),
  },
  mounted() {
    this.fetchUserDetails(); // Fetch user details on component mount
  },
};
</script>



<style scoped>
/* General Reset */
body {
  margin: 0;
  font-family: 'Roboto', Arial, sans-serif;
  background-color: #f4f6f9;
}

/* Header Container */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #24292e; /* Modern dark shade */
  padding: 10px 20px;
  color: #ffffff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  top: 0;
  z-index: 1000;
}

/* Navigation Bar */
.nav {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

/* Logo Section */
.header-left {
  display: flex;
  align-items: center;
}

.header-logo {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.logo-image {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  margin-right: 10px;
  transition: transform 0.3s ease;
}

.logo-title {
  font-size: 1.8rem;
  font-weight: bold;
  color: #f1c40f; /* Golden accent color */
  transition: color 0.3s ease;
}

.header-logo:hover .logo-image {
  transform: scale(1.1);
}

.header-logo:hover .logo-title {
  color: #3498db; /* Light blue accent on hover */
}

/* User Profile Section */
.header-right {
  position: relative;
}

.profile-container {
  position: relative;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.profile-image {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  transition: border 0.3s ease, box-shadow 0.3s ease;
  border: 2px solid #ffffff;
  object-fit: cover;
}

.profile-container:hover .profile-image {
  border: 2px solid #3498db;
  box-shadow: 0 0 8px rgba(52, 152, 219, 0.8);
}

/* Dropdown Menu */
.profile-dropdown {
  position: absolute; /* Positions the dropdown relative to the container */
  top: 100%; /* Ensures the dropdown appears below the profile image */
  right: 0; /* Aligns the dropdown to the right of the container */
  z-index: 1000; /* High enough to ensure it appears above other elements */
  background: white; /* Optional: Dropdown background */
  border: 1px solid #ccc; /* Optional: Border for the dropdown */
  border-radius: 4px; /* Optional: Rounded corners */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Adds shadow for better visibility */
  display: none; /* Initially hidden */
}

.profile-container:hover .profile-dropdown {
  display: block;
}

.dropdown-item {
  display: block;
  padding: 8px 12px;
  color: black;
  text-decoration: none;
  font-size: 0.9rem;
  transition: background 0.3s ease, color 0.3s ease;
  border-radius: 4px;
  white-space: nowrap; 
}

.dropdown-item:hover {
  background-color: #3498db;
  color: #ffffff;
}
</style>