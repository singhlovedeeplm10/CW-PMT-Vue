<template>
  <div class="modal-overlay" @click.self="closeModal">
    <div class="modal-content">
      <h5>Add New Notice
        <button type="button" class="close-modal" @click="$emit('close')">&times;</button>
      </h5>

      <form @submit.prevent="submitForm" class="modal-form">
        <div class="form-row">
          <!-- Title input field (No longer required) -->
          <div class="form-group">
            <InputField label="Title" :modelValue="form.title" @update:modelValue="(value) => (form.title = value)"
              placeholder="Enter notice title" :isRequired="false" />
          </div>

          <!-- Order input field -->
          <div class="form-group" :class="{ 'error': validationErrors.order }">
            <InputField label="Order" type="number" :modelValue="form.order"
              @update:modelValue="(value) => (form.order = value)" placeholder="Enter order number"
              :isRequired="true" />
            <p v-if="validationErrors.order" class="error-message">Order is required.</p>
          </div>
        </div>

        <div class="form-row">
          <!-- Start Date -->
          <div class="form-group" :class="{ 'error': validationErrors.start_date }">
            <DateInput label="Start Date" id="start_date" name="start_date" :modelValue="form.start_date"
              @update:modelValue="(value) => (form.start_date = value)" :minDate="minDate" :maxDate="maxDate"
              :condition="true" />
            <p v-if="validationErrors.start_date" class="error-message">Start Date is required.</p>
          </div>

          <!-- End Date -->
          <div class="form-group" :class="{ 'error': validationErrors.end_date }">
            <DateInput label="End Date" id="end_date" name="end_date" :modelValue="form.end_date"
              @update:modelValue="(value) => (form.end_date = value)" :minDate="form.start_date" :maxDate="maxDate"
              :condition="true" />
            <p v-if="validationErrors.end_date" class="error-message">End Date is required.</p>
          </div>
        </div>

        <!-- Description field with Summernote -->
        <div class="form-group full-width" :class="{ 'error': validationErrors.description }">
          <label for="description-editor">Description</label>
          <div id="description-editor"></div>
          <p v-if="validationErrors.description" class="error-message">Description is required.</p>
        </div>

        <!-- Buttons -->
        <div class="form-actions">
          <ButtonComponent label="Save" buttonClass="submit-btn" :isLoading="isLoading" loadingText="Saving..."
            @click="submitForm" />

        </div>
      </form>
    </div>
  </div>
</template>

<script>
import InputField from "@/components/inputs/InputField.vue";
import DateInput from "@/components/inputs/DateInput.vue";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import $ from "jquery";
import "summernote/dist/summernote-lite.css";
import "summernote/dist/summernote-lite.js";

export default {
  name: "AddNoticeModal",
  components: {
    InputField,
    DateInput,
    ButtonComponent,
  },
  data() {
    return {
      form: {
        title: "",
        order: 0,
        description: "",
        start_date: "",
        end_date: "",
      },
      validationErrors: {},
      minDate: new Date().toISOString().split("T")[0],
      maxDate: null,
      isLoading: false, // Loader state
    };
  },
  methods: {
    handleEscKey(event) {
      if (event.key === "Escape") {
        this.closeModal();
      }
    },
    closeModal() {
      this.$emit("close");
    },
    async submitForm() {
      if (this.isLoading) return; // Prevent duplicate requests
      this.isLoading = true; // Show loader

      this.form.description = $("#description-editor").summernote("code");

      // Reset validation errors
      this.validationErrors = {};

      // Validate fields (Title validation removed)
      if (!this.form.order && this.form.order !== 0) this.validationErrors.order = true;
      if (!this.form.description || this.form.description === "<p><br></p>") this.validationErrors.description = true;
      if (!this.form.start_date) this.validationErrors.start_date = true;
      if (!this.form.end_date) this.validationErrors.end_date = true;

      if (Object.keys(this.validationErrors).length > 0) {
        toast.error("Please fill in all required fields!", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
        this.isLoading = false; // Hide loader
        return;
      }

      if (new Date(this.form.start_date) > new Date(this.form.end_date)) {
        toast.error("End Date must be greater than or equal to Start Date!", {
          position: "top-right",
          autoClose: 1000, // Set to 2 seconds
        });
        this.isLoading = false; // Hide loader
        return;
      }

      try {
        const response = await fetch("/api/store-notices", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(this.form),
        });

        if (!response.ok) {
          const errorData = await response.json();
          const errorMessage = errorData.errors
            ? JSON.stringify(errorData.errors)
            : errorData.message;
          toast.error(`Error: ${errorMessage}`, {
            position: "top-right",
            autoClose: 1000,
          });
          this.isLoading = false; // Hide loader
          return;
        }

        const data = await response.json();
        toast.success("Notice created successfully!", {
          position: "top-right",
          autoClose: 1000,
        });

        this.$emit("noticeadded");
        this.closeModal();
      } catch (error) {
        console.error("Error saving notice:", error);
        toast.error("An error occurred while saving the notice.", {
          position: "top-right",
          autoClose: 1000,
        });
      } finally {
        this.isLoading = false; // Hide loader
      }
    },
  },
  mounted() {
    $("#description-editor").summernote({
      placeholder: "Enter a detailed description",
      tabsize: 2,
      height: 200,
      dialogsInBody: true,
      callbacks: {
        onInit: function () {
          $(".note-modal").appendTo("body");
        },
      },
    });

    // Add event listener for Esc key
    document.addEventListener("keydown", this.handleEscKey);
  },
  beforeUnmount() {
    // Remove event listener when component is destroyed
    document.removeEventListener("keydown", this.handleEscKey);
  },
};
</script>

<style scoped>
.close-modal {
  background: none;
  color: white;
  border: none;
  font-size: 22px;
  font-family: math;
  position: absolute;
  top: 20px;
  right: 25px;
  cursor: pointer;
}

.error-message {
  color: red;
  font-size: 0.9rem;
  margin-top: 5px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #ffffff;
  border-radius: 12px;
  width: 700px;
  height: 690px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

h5 {
  color: white;
  background-color: #4e73df;
  padding: 22px 22px;
  border-top-left-radius: 11px;
  border-top-right-radius: 10px;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.form-group {
  flex: 1;
  padding: 0px 20px;
}

.full-width {
  width: 100%;
}

label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #555555;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin: 0px 30px;
}

.submit-btn,
.cancel-btn {
  padding: 12px 20px;
  font-size: 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  border: none;
  color: white;
}

.submit-btn {
  background-color: #4e73df;
  color: white;
  border-radius: 5px;
  padding: 10px 20px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.submit-btn:hover {
  background-color: #3e5bcd;
  transform: translateY(-2px);
}

.cancel-btn {
  background-color: grey;
}

.cancel-btn:hover {
  background-color: grey;
}
</style>