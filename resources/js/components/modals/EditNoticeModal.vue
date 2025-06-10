<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <h5>
        Edit Notice
        <button type="button" class="close-modal" @click="$emit('close')">
          &times;
        </button>
      </h5>
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
          <ButtonComponent :isLoading="isLoading" :label="'Save'" :loadingText="'Saving...'" :buttonClass="'save-btn'"
            @click="updateNotice" />
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
        title: this.decodeHtml(this.notice.title),
        order: this.notice.order,
        description: this.notice.description,
        start_date: this.notice.start_date,
        end_date: this.notice.end_date,
      },
      isLoading: false,
    };
  },

  methods: {
    decodeHtml(html) {
      const txt = document.createElement("textarea");
      txt.innerHTML = html;
      return txt.value;
    },
    async updateNotice() {
      this.isLoading = true;
      this.form.description = $("#description-editor").summernote("code");

      try {
        await axios.put(`/api/edit-notice/${this.notice.id}`, this.form);
        toast.success("Notice updated successfully!", {
          position: "top-right",
          autoClose: 1000,
        });
        this.$emit("noticeupdated");
        this.$emit("close");
      } catch (error) {
        toast.error("Failed to update notice. Please try again.", {
          position: "top-right",
          autoClose: 1000,
        });
        console.error("Error updating notice:", error);
      } finally {
        this.isLoading = false;
      }
    },
    handleEscKey(event) {
      if (event.key === "Escape") {
        this.$emit("close");
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

    // Add event listener for Esc key
    document.addEventListener("keydown", this.handleEscKey);
  },
  beforeUnmount() {
    // Remove event listener to prevent memory leaks
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
  border-radius: 12px;
  width: 700px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  max-height: 100%;
  overflow-y: auto;
}

h5 {
  color: white;
  background-color: #4e73df;
  padding: 22px 22px;
  border-top-left-radius: 11px;
  border-top-right-radius: 10px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  align-items: center;
  padding: 7px 20px;
}

.full-width {
  grid-column: span 2;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  font-size: 1rem;
  font-weight: 600;
  color: #555;
  margin-bottom: 8px;
}

.input-field {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
  box-sizing: border-box;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin: 10px 30px;
}

.save-btn {
  background-color: #4e73df;
  color: white;
  border-radius: 5px;
  padding: 10px 20px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.save-btn:hover {
  background-color: #3e5bcd;
  transform: translateY(-2px);
}

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

.note-modal {
  display: block !important;
  z-index: 1055 !important;
  background-color: #fff;
}

.modal-backdrop {
  display: none !important;
}

.note-popover {
  z-index: 1056 !important;
}
</style>