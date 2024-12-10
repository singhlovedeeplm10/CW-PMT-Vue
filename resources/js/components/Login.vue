<template>
  <div class="login-container">
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
.login-container {
  max-width: 400px;
  margin: auto;
}

.header {
  text-align: center;
}

.logo {
  width: 100px;
}

.brand {
  font-size: 24px;
  font-weight: bold;
  margin-top: 10px;
}

.login-form {
  margin-top: 20px;
}

.input-group {
  margin-bottom: 15px;
}

.input-group label {
  display: block;
  font-weight: bold;
  margin-bottom: 5px;
}

.input-group input {
  width: 100%;
  padding: 8px;
  font-size: 16px;
}

.login-button {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  font-size: 16px;
  cursor: pointer;
  position: relative;
}

.login-button:disabled {
  background-color: #6c757d;
}

.error {
  color: red;
  margin-top: 10px;
  text-align: center;
}

.loader {
  border: 3px solid #f3f3f3;
  border-radius: 50%;
  border-top: 3px solid #007bff;
  width: 15px;
  height: 15px;
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
