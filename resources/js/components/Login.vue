<template>
  <div class="login-container">
    <div class="login-card">
      <div class="header">
        <img src="img/Tablogo.svg" alt="Logo" class="logo" />
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
  name: "Login",
  data() {
    return {
      email: '',
      password: '',
      error: null,
      loading: false,
    };
  },
  created() {
    if (localStorage.getItem('authToken')) {
      this.$router.push({ name: 'Dashboard' });
    }
  },
  methods: {
    async login() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/login', {
          email: this.email,
          password: this.password,
        });

        if (response.data.success) {
          // === 1. Store token and expiration time ===
          const now = Date.now();
          const expirationInHours = 12;
          const expiresAt = now + expirationInHours * 60 * 60 * 1000;

          localStorage.setItem('authToken', response.data.token);
          localStorage.setItem('expiresAt', expiresAt);


          // === 2. Set timeout to auto-logout ===
          const expiresIn = expiresAt - now;
          setTimeout(() => {
            localStorage.removeItem('authToken');
            localStorage.removeItem('expiresAt');
            alert('Session expired. Please log in again.');
            this.$router.push('/login');
          }, expiresIn);

          // === 3. Redirect user ===
          const redirectTo = this.$route.query.redirect || 'Dashboard';
          this.$router.push({ name: redirectTo });
        } else {
          this.error = response.data.message;
        }
      } catch (error) {
        this.error = error.response?.data?.message ||
          'Your account is inactive. Please contact support.';
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
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

.header {
  margin-bottom: 20px;
  border: none;
}

.logo {
  width: 60%;
  text-align: center;
  margin: auto;
  border-radius: 0;
}

.brand {
  font-size: 28px;
  font-weight: bold;
  color: #007bff;
  margin: 0;
}

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

.error {
  color: #e74c3c;
  font-size: 14px;
  margin-top: 10px;
}

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
