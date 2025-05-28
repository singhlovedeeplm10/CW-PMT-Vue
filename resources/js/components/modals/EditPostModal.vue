<template>
  <div>
    <!-- Modal Overlay -->
    <div v-if="show" class="modal-overlay" @click.self="closeModal"></div>

    <!-- Modal Content -->
    <div v-if="show" class="edit-post-modal">
      <div class="modal-content">
        <div class="modal-header custom-header">
          <h5 class="modal-title">Edit Post</h5>
          <button type="button" class="close-modal" @click="closeModal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="text" :value="decodeHtml(editData.title)"
            @input="editData.title = encodeHtml($event.target.value)" placeholder="Edit Title" class="modal-input" />
          <textarea :value="stripHtmlTags(editData.description)"
            @input="editData.description = encodeHtmlWithFormatting($event.target.value)" placeholder="Edit Description"
            class="modal-input"></textarea>
          <div class="button-group">
            <!-- <button @click="closeModal" class="cancel-button">Cancel</button> -->
            <button @click="savePostEdit" class="save-button">Save</button>
          </div>
        </div>
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
  watch: {
    show(newVal) {
      if (newVal) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    }
  },
  methods: {
    decodeHtml(html) {
      const txt = document.createElement("textarea");
      txt.innerHTML = html;
      return txt.value;
    },

    stripHtmlTags(html) {
      const tmp = document.createElement("div");
      tmp.innerHTML = html;
      let text = tmp.textContent || tmp.innerText || '';
      text = text.replace(/&nbsp;/g, ' ')
        .replace(/&rsquo;/g, "'")
        .replace(/&lsquo;/g, "'")
        .replace(/&mdash;/g, "—")
        .replace(/&amp;/g, "&");
      return text.trim();
    },

    encodeHtmlWithFormatting(text) {
      return `<p><b><font color="#6badde">${this.encodeHtml(text)}</font></b></p>`;
    },

    encodeHtml(text) {
      const txt = document.createElement("textarea");
      txt.textContent = text;
      return txt.innerHTML;
    },

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
.custom-header {
  background-color: #4e73df;
  color: white;
  border-top-left-radius: 3px;
  border-top-right-radius: 3px;
  padding: 13px 20px;
}

.custom-header .btn-close {
  background-color: white;
  border-radius: 50%;
  opacity: 0.8;
}

.custom-header .btn-close:hover {
  opacity: 1;
}

.close-modal {
  background: none;
  color: white;
  border: none;
  font-size: 22px;
  font-family: math;
}

.modal-body {
  background: linear-gradient(135deg, #f5f7fa, #e2e8f0);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.edit-post-modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #ffffff;
  z-index: 1000;
  width: 70%;
  max-width: 800px;
  min-height: 60vh;
  max-height: 90vh;
  overflow-y: auto;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translate(-50%, -60%);
  }

  to {
    opacity: 1;
    transform: translate(-50%, -50%);
  }
}

.modal-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}

h5 {
  font-size: 22px;
  color: white;
  text-align: left;
  background-color: #4e73df;
}

.modal-input {
  padding: 15px;
  font-size: 16px;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #e0e0e0;
  transition: all 0.3s ease;
  min-height: 20px;
  margin: 10px 0px;
}

.modal-input:focus {
  border-color: #3498db;
  outline: none;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

textarea.modal-input {
  min-height: 200px;
  resize: vertical;
}

.button-group {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 3px;
}

.save-button,
.cancel-button {
  padding: 12px 24px;
  font-size: 16px;
  cursor: pointer;
  border-radius: 8px;
  border: none;
  font-weight: 500;
  transition: all 0.2s ease;
}

.save-button {
  background-color: #27ae60;
  color: white;
}

.save-button:hover {
  background-color: #2ecc71;
  transform: translateY(-2px);
}

.cancel-button {
  background-color: #f1f1f1;
  color: #555;
}

.cancel-button:hover {
  background-color: #e0e0e0;
  transform: translateY(-2px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .edit-post-modal {
    width: 90%;
    padding: 30px;
  }

  .button-group {
    justify-content: space-between;
  }

  .save-button,
  .cancel-button {
    padding: 10px 20px;
  }
}
</style>