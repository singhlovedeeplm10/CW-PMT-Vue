<template>
    <div class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
      <div class="modal-content">
        <div class="modal-header custom-modal-header">
          <h5 class="modal-title">Edit Employee</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="updateEmployee">
          <div class="form-group">
            <label for="name" class="form-label">Name</label>
            <input
              id="name"
              type="text"
              v-model="formData.name"
              class="form-control custom-input"
              placeholder="Enter employee's name"
            />
          </div>
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              type="email"
              v-model="formData.email"
              class="form-control custom-input"
              placeholder="Enter employee's email"
            />
          </div>
          <div class="form-group">
            <label for="status" class="form-label">Status</label>
            <select
              id="status"
              v-model="formData.status"
              class="form-control custom-input"
            >
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="modal-footer custom-modal-footer">
            <button type="submit" class="btn custom-btn-submit">Save Changes</button>
            <button
              type="button"
              class="btn custom-btn-close"
              @click="$emit('close')"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      user: {
        type: Object,
        required: true,
      },
    },
    data() {
      return {
        formData: { ...this.user }, // Clone user object to avoid mutating the prop
      };
    },
    methods: {
      async updateEmployee() {
        try {
          await axios.put(`/api/users/${this.user.id}`, this.formData);
          this.$emit("employee-updated");
          this.$emit("close");
        } catch (error) {
          console.error("Error updating employee:", error);
        }
      },
    },
  };
  </script>  
  
  <style scoped>
/* Modal Overlay */
.modal-overlay {
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Modal Content */
.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  margin: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, opacity 0.3s ease;
}

/* Modal Header */
.custom-modal-header {
  background-color: #4e73df;
  color: white;
  padding: 15px;
  text-align: center;
  font-size: 1.25rem;
  border-bottom: 1px solid #ddd;
}

.custom-modal-header .btn-close {
  border: none;
  background: transparent;
  color: white;
  font-size: 1.25rem;
  transition: background 0.3s ease;
}

/* Modal Body */
.form-label {
  font-size: 1rem;
  color: #555;
  margin-bottom: 5px;
}

.custom-input {
  border-radius: 5px;
  border: 1px solid #ddd;
  padding: 10px;
  width: 100%;
  margin-bottom: 20px;
  transition: border-color 0.3s ease;
}

.custom-input:focus {
  border-color: #4e73df;
  outline: none;
  box-shadow: 0 0 5px rgba(78, 115, 223, 0.5);
}

/* Modal Footer */
.custom-modal-footer {
  background-color: #f9f9f9;
  padding: 15px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  border-top: 1px solid #ddd;
}

/* Button Styles */
.custom-btn-close {
  background-color: #6c757d;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.custom-btn-close:hover {
  background-color: #5a6268;
}

.custom-btn-submit {
  background-color: #4e73df;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.custom-btn-submit:hover {
  background-color: #3e5bcd;
  transform: translateY(-2px);
}

.custom-btn-submit:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>
  