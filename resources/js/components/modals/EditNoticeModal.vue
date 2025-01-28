<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <h2>Edit Notice</h2>
      <form @submit.prevent="updateNotice">
        <label for="title">Title</label>
        <input v-model="form.title" id="title" class="input-field" required />

        <label for="description">Description</label>
        <div id="description-editor"></div>

        <label for="start_date">Start Date</label>
        <input type="date" v-model="form.start_date" id="start_date" class="input-field" required />

        <label for="end_date">End Date</label>
        <input type="date" v-model="form.end_date" id="end_date" class="input-field" required />

        <div class="modal-actions">
          <ButtonComponent
            :isLoading="isLoading"
            :label="'Save'"
            :loadingText="'Saving...'"
            :buttonClass="'save-btn'"
            @click="updateNotice"
          />
          <ButtonComponent 
            :label="'Cancel'" 
            :buttonClass="'cancel-btn'" 
            @click="$emit('close')" 
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
import ButtonComponent from "@/components/ButtonComponent.vue";
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
          autoClose: 3000,
        });
        this.$emit("noticeupdated");
        this.$emit("close");
      } catch (error) {
        toast.error("Failed to update notice. Please try again.", {
          position: "top-right",
          autoClose: 3000,
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
    background: white;
    padding: 30px;
    border-radius: 12px;
    width: 600px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    max-height: 90%;
    overflow-y: auto;
  }
  
  h2 {
    margin: 0 0 20px;
    font-size: 1.8rem;
    color: #333;
  }
  
  label {
    display: block;
    margin-bottom: 8px;
    font-size: 1rem;
    font-weight: 600;
    color: #555;
  }
  
  .input-field {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    box-sizing: border-box;
  }
  
  .textarea-field {
    width: 100%;
    height: 150px;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
    resize: vertical;
    box-sizing: border-box;
  }
  
  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
  }
  
  .save-btn {
    background-color: #4caf50;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .save-btn:hover {
    background-color: #45a049;
  }
  
  .cancel-btn {
    background-color: #f44336;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .cancel-btn:hover {
    background-color: #e53935;
  }
  </style>