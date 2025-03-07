<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <h2>Edit Notice</h2>
      <form @submit.prevent="updateNotice">
        <div class="form-grid">
          <!-- Left Column -->
          <div class="form-group">
            <label for="title">Title</label>
            <input v-model="form.title" id="title" class="input-field" required />
          </div>

          <div class="form-group">
            <label for="order">Order</label>
            <input type="number" v-model="form.order" id="order" class="input-field" required />
          </div>

          <!-- Full-width Description -->
          

          <!-- Right Column -->
          <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" v-model="form.start_date" id="start_date" class="input-field" required />
          </div>

          <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" v-model="form.end_date" id="end_date" class="input-field" required />
          </div>

          <div class="form-group full-width">
            <label for="description">Description</label>
            <div id="description-editor"></div>
          </div>
        </div>

        <div class="modal-actions">
          <ButtonComponent 
            :label="'Cancel'" 
            :buttonClass="'cancel-btn'" 
            @click="$emit('close')" 
          />
          
          <ButtonComponent
            :isLoading="isLoading"
            :label="'Save'"
            :loadingText="'Saving...'"
            :buttonClass="'save-btn'"
            @click="updateNotice"
          />
         
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import $ from "jquery";
import "summernote/dist/summernote-lite.css";
import "summernote/dist/summernote-lite.js";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default {
  name: "EditNoticeModal",
  props: {
    notice: {
      type: Object,
      required: true,
    },
  },
  components: {
    ButtonComponent,
  },
  data() {
    return {
      form: {
        title: this.notice.title,
        order: this.notice.order,  // Added order field
        description: this.notice.description,
        start_date: this.notice.start_date,
        end_date: this.notice.end_date,
      },
      isLoading: false,
    };
  },
  methods: {
    async updateNotice() {
    this.isLoading = true;
    this.form.description = $("#description-editor").summernote("code");

    try {
      await axios.put(`/api/edit-notice/${this.notice.id}`, this.form);
      toast.success("Notice updated successfully!", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
      this.$emit("noticeupdated");
      this.$emit("close");
    } catch (error) {
      toast.error("Failed to update notice. Please try again.", {
        position: "top-right",
        autoClose: 1000, // Set to 2 seconds
      });
      console.error("Error updating notice:", error);
    } finally {
      this.isLoading = false;
    }
  },
  },
  mounted() {
    $("#description-editor")
      .summernote({
        placeholder: "Enter a detailed description",
        tabsize: 2,
        height: 200,
        dialogsInBody: true,
        callbacks: {
          onInit: function () {
            $(".note-modal").appendTo("body");
          },
        },
      })
      .summernote("code", this.form.description);
  },
};
</script>

<style scoped>
/* Modal Overlay */
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

/* Modal Content */
.modal-content {
  background: white;
  padding: 30px;
  border-radius: 12px;
  width: 700px; /* Increased width for horizontal layout */
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  max-height: 100%;
  overflow-y: auto;
}

/* Headings */
h2 {
  margin-bottom: 20px;
  font-size: 1.8rem;
  color: #333;
  text-align: center;
}

/* Form Grid (Two-Column Layout) */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  align-items: center;
}

/* Make Description Full Width */
.full-width {
  grid-column: span 2;
}

/* Form Group */
.form-group {
  display: flex;
  flex-direction: column;
}

/* Labels */
label {
  font-size: 1rem;
  font-weight: 600;
  color: #555;
  margin-bottom: 8px;
}

/* Input Fields */
.input-field {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
  box-sizing: border-box;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 20px;
}

/* Save Button */
.save-btn {
  background-color: #0056b3;
  color: white;
  padding: 12px 20px;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.save-btn:hover {
  background-color: #0056b3;
}

/* Cancel Button */
.cancel-btn {
  background-color: grey;
  color: white;
  padding: 12px 20px;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.cancel-btn:hover {
  background-color: grey;
}

/* Ensure Summernote modals are always visible */
.note-modal {
  display: block !important;
  z-index: 1055 !important;
  background-color: #fff;
}

/* Remove or hide the backdrop from blocking user interaction */
.modal-backdrop {
  display: none !important;
}

/* Ensure Summernote dialogs are layered above other UI elements */
.note-popover {
  z-index: 1056 !important;
}
</style>