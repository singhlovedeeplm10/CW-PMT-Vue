<template>
  <div class="login-container">
    <div class="login-card">
      <div class="header">
        <img src="img/CWlogo.jpeg" alt="Logo" class="logo" />
        <h2 class="brand">CONTRIWHIZ</h2>
      </div>
      <form @submit.prevent="login" class="login-form">
        <div class="input-group">
          <label>Email:</label>
          <input type="email" v-model="email" required />
        </div>
        <div class="input-group">
          <label>Password:</label>
          <input type="password" v-model="password" required />
        </div>
        <button type="submit" class="login-button" :disabled="loading">
          <span v-if="loading" class="loader"></span>
          <span v-else>Login</span>
        </button>
      </form>
      <p v-if="error" class="error">{{ error }}</p>
    </div>
  </div>
</template>


<script>
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      password: '',
      error: null,
      loading: false, // State for loader
    };
  },
  methods: {
    async login() {
      this.loading = true; // Start loader
      this.error = null;

      try {
        const response = await axios.post('/api/login', {
          email: this.email,
          password: this.password,
        });

        if (response.data.success) {
          // Store the token in local storage
          localStorage.setItem('authToken', response.data.token);

          // Store the last login date in local storage
          localStorage.setItem('lastLoginDate', response.data.lastLoginDate);

          // Redirect to the Dashboard
          this.$router.push({ name: 'Dashboard' });
        } else {
          this.error = response.data.message; // Display the message from the API
        }
      } catch (error) {
        this.error = 'Your account is inactive. Please contact support.';
      } finally {
        this.loading = false; // Stop loader
      }
    },
  },
};
</script>

<style scoped>
/* Background and container styling */
.login-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  font-family: 'Roboto', sans-serif;
}

.login-card {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  padding: 30px;
  width: 100%;
  max-width: 400px;
  text-align: center;
}

/* Header section */
.header {
  margin-bottom: 20px;
}

.logo {
  width: 80px;
  margin-bottom: 10px;
}

.brand {
  font-size: 28px;
  font-weight: bold;
  color: #007bff;
  margin: 0;
}

/* Form styling */
.login-form {
  margin-top: 20px;
}

.input-group {
  margin-bottom: 20px;
  text-align: left;
}

.input-group label {
  font-size: 14px;
  font-weight: 500;
  color: #6c757d;
}

.input-group input {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ddd;
  border-radius: 5px;
  outline: none;
  transition: border-color 0.3s;
}

.input-group input:focus {
  border-color: #007bff;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Button styling */
.login-button {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  font-weight: bold;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.login-button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

.login-button:hover:not(:disabled) {
  background-color: #0056b3;
  transform: scale(1.02);
}

/* Error message */
.error {
  color: #e74c3c;
  font-size: 14px;
  margin-top: 10px;
}

/* Loader */
.loader {
  border: 3px solid #f3f3f3;
  border-radius: 50%;
  border-top: 3px solid #007bff;
  width: 20px;
  height: 20px;
  animation: spin 1s linear infinite;
  display: inline-block;
  vertical-align: middle;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>

