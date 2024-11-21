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
      <button type="submit" class="login-button">Login</button>
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
    };
  },
  methods: {
    async login() {
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
          this.error = 'Login failed. Please check your credentials.';
        }
      } catch (error) {
        this.error = 'An error occurred. Please try again.';
      }
    },
  },
};
</script>

<style scoped>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background-color: #f4f6f9;
}

.login-container {
  max-width: 400px;
  margin: 100px auto;
  padding: 2rem;
  text-align: center;
  background-color: #ffffff;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  border-radius: 12px;
  transition: transform 0.3s, box-shadow 0.3s;
}

.header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.5rem;
}

.logo {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  margin-bottom: 1rem;
}

.brand {
  font-size: 1.8rem;
  font-weight: bold;
  color: #007bff;
}

.input-group {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  margin-bottom: 1.25rem;
}

label {
  font-weight: bold;
  color: #555;
  margin-bottom: 0.5rem;
}

input[type="email"],
input[type="password"] {
  width: 94%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  transition: border-color 0.3s, box-shadow 0.3s;
}

input[type="email"]:focus,
input[type="password"]:focus {
  border-color: #007bff;
  box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
  outline: none;
}

.login-button {
  width: 100%;
  padding: 0.75rem;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s;
}

.login-button:hover {
  background-color: #0056b3;
  transform: translateY(-2px);
}

.error {
  color: #ff3333;
  margin-top: 1rem;
  font-weight: bold;
}
</style>
