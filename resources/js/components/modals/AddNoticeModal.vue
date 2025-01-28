<template>
  <div class="modal-overlay" @click.self="closeModal">
    <div class="modal-content">
      <h2>Add New Notice</h2>
      <form @submit.prevent="submitForm">
        <!-- Title input field -->
        <div class="form-group">
          <InputField
            label="Title"
            :modelValue="form.title"
            @update:modelValue="(value) => (form.title = value)"
            placeholder="Enter notice title"
            :isRequired="true"
          />
        </div>

        <!-- Description field with Summernote -->
        <div class="form-group">
          <label for="description-editor">Description</label>
          <div id="description-editor"></div>
        </div>

        <!-- Start Date -->
        <div class="form-group">
          <DateInput
            label="Start Date"
            id="start_date"
            name="start_date"
            :modelValue="form.start_date"
            @update:modelValue="(value) => (form.start_date = value)"
            :minDate="minDate"
            :maxDate="maxDate"
            :condition="true"
          />
        </div>

        <!-- End Date -->
        <div class="form-group">
          <DateInput
            label="End Date"
            id="end_date"
            name="end_date"
            :modelValue="form.end_date"
            @update:modelValue="(value) => (form.end_date = value)"
            :minDate="form.start_date"
            :maxDate="maxDate"
            :condition="true"
          />
        </div>

        <!-- Buttons -->
        <div class="form-actions">
          <button type="submit" class="submit-btn">Save</button>
          <button type="button" class="cancel-btn" @click="closeModal">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import InputField from "@/components/InputField.vue";
import DateInput from "@/components/DateInput.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import $ from "jquery"; // Import jQuery for Summernote compatibility
import "summernote/dist/summernote-lite.css";
import "summernote/dist/summernote-lite.js";

export default {
  name: "AddNoticeModal",
  components: {
    InputField,
    DateInput,
  },
  data() {
    return {
      form: {
        title: "",
        description: "",
        start_date: "",
        end_date: "",
      },
      minDate: new Date().toISOString().split("T")[0],
      maxDate: null,
    };
  },
  methods: {
    closeModal() {
      this.$emit("close");
    },
    async submitForm() {
      this.form.description = $("#description-editor").summernote("code");

      if (
        !this.form.title ||
        !this.form.description ||
        !this.form.start_date ||
        !this.form.end_date
      ) {
        toast.error("All fields are required!", {
          position: "top-right",
          autoClose: 3000,
        });
        return;
      }

      if (new Date(this.form.start_date) > new Date(this.form.end_date)) {
        toast.error("End Date must be greater than or equal to Start Date!", {
          position: "top-right",
          autoClose: 3000,
        });
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
            autoClose: 3000,
          });
          return;
        }

        const data = await response.json();
        toast.success("Notice created successfully!", {
          position: "top-right",
          autoClose: 3000,
        });

        this.$emit("noticeadded");
        this.closeModal();
      } catch (error) {
        console.error("Error saving notice:", error);
        toast.error("An error occurred while saving the notice.", {
          position: "top-right",
          autoClose: 3000,
        });
      }
    },
  },
  mounted() {
    $("#description-editor").summernote({
  placeholder: "Enter a detailed description",
  tabsize: 2,
  height: 200,
  dialogsInBody: true, // Attach dialogs outside modal to prevent backdrop issues
  callbacks: {
    onInit: function () {
      $(".note-modal").appendTo("body"); // Ensure dialogs are outside of parent modal
    },
  },
});

  },
};
</script>


<style scoped>
.note-modal {
  display: block !important;
  z-index: 1050 !important;
  background: #fff;
}

/* Remove unnecessary backdrop overlay */
.modal-backdrop {
  display: none !important;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6); /* Darker overlay */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #ffffff;
  padding: 30px; /* Increased padding */
  border-radius: 12px; /* Softer corners */
  width: 600px; /* Increased width */
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* More prominent shadow */
}

h2 {
  margin-top: 0;
  font-size: 1.8rem; /* Larger heading */
  color: #333333;
}

.form-group {
  margin-bottom: 20px; /* Increased spacing */
}

label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px; /* Slightly more spacing */
  color: #555555;
}

textarea {
  width: 100%;
  padding: 12px; /* Increased padding for better usability */
  border: 1px solid #ccc;
  border-radius: 6px; /* Softer corners */
  font-size: 1rem;
  resize: vertical; /* Allow resizing */
  min-height: 120px; /* Increased size for the description */
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px; /* More space between buttons */
}

.submit-btn {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 12px 20px; /* Larger button */
  font-size: 1rem;
  border-radius: 6px; /* Softer corners */
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-btn:hover {
  background-color: #45a049;
}

.cancel-btn {
  background-color: #f44336;
  color: white;
  border: none;
  padding: 12px 20px; /* Larger button */
  font-size: 1rem;
  border-radius: 6px; /* Softer corners */
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.cancel-btn:hover {
  background-color: #e53935;
}
</style>

  