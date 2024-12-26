<template>
  <div v-if="show" class="edit-post-modal">
    <div class="modal-content">
      <h3>Edit Post</h3>
      <input
        type="text"
        v-model="editData.title"
        placeholder="Edit Title"
        class="modal-input"
      />
      <textarea
        v-model="editData.description"
        placeholder="Edit Description"
        class="modal-input"
      ></textarea>
      <div class="button-group">
        <button @click="savePostEdit" class="save-button">Save</button>
        <button @click="closeModal" class="cancel-button">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "EditPostModal",
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    editData: {
      type: Object,
      required: true,
    },
  },
  methods: {
    savePostEdit() {
      this.$emit("save-edit");
    },
    closeModal() {
      this.$emit("close");
    },
  },
};
</script>

<style scoped>
.edit-post-modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(1);
  background-color: #ffffff;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  padding: 40px;
  border-radius: 16px;
  z-index: 1000;
  width: 50%;
  max-width: 600px;
  height: auto;
  max-height: 90vh;
  overflow-y: auto;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
}

.modal-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

h3 {
  font-size: 28px;
  color: #2c3e50;
  text-align: center;
  margin-bottom: 20px;
  font-weight: 600;
}

.modal-input {
  padding: 15px;
  font-size: 16px;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #ccc;
  border-radius: 8px;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.modal-input:focus {
  border-color: #3498db;
  outline: none;
  box-shadow: 0 0 8px rgba(52, 152, 219, 0.5);
}

.button-group {
  display: flex;
  justify-content: space-between;
  gap: 15px;
}

.save-button,
.cancel-button {
  padding: 15px;
  font-size: 16px;
  cursor: pointer;
  border-radius: 8px;
  border: none;
  width: 100%;
  text-align: center;
  transition: background-color 0.3s, transform 0.2s;
}

.save-button {
  background-color: #27ae60;
  color: white;
  font-weight: 500;
}

.save-button:hover {
  background-color: #2ecc71;
  transform: scale(1.05);
}

.cancel-button {
  background-color: #e74c3c;
  color: white;
  font-weight: 500;
}

.cancel-button:hover {
  background-color: #ff6b6b;
  transform: scale(1.05);
}

.edit-post-modal::before {
  content: "";
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
}
</style>