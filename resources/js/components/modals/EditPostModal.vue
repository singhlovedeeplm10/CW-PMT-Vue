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
          <!-- Title Input -->
          <input type="text" :value="decodeHtml(localEditData.title)"
            @input="localEditData.title = encodeHtml($event.target.value)" placeholder="Edit Title"
            class="modal-input" />

          <!-- Description Input -->
          <textarea :value="stripHtmlTags(localEditData.description)"
            @input="localEditData.description = encodeHtmlWithFormatting($event.target.value)"
            placeholder="Edit Description" class="modal-input"></textarea>

          <!-- Image Editing Section -->
          <div class="image-edit-section">
            <h6>Edit Images</h6>

            <!-- Image Previews -->
            <div v-if="localEditData.photos && localEditData.photos.length" class="image-preview-container"
              @dragover.prevent @drop="handleDrop">
              <div v-for="(photo, index) in localEditData.photos" :key="index" class="image-preview-item"
                draggable="true" @dragstart="handleDragStart(index)" @dragenter.prevent="handleDragEnter(index)"
                @dragend="handleDragEnd">
                <div class="image-actions">
                  <button @click="removeImage(index)" class="remove-image-btn">X</button>
                </div>
                <img :src="photo.url || photo" alt="Post photo" class="preview-image" />
              </div>
            </div>

            <!-- No Images Message -->
            <div v-else>
              <p>No images in this post</p>
            </div>
          </div>

          <!-- Save Button -->
          <div class="button-group">
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
  data() {
    return {
      localEditData: {
        title: '',
        description: '',
        photos: []
      },
      dragStartIndex: null,
      dragOverIndex: null
    };
  },

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

        // Initialize with empty array if photos is undefined
        const photos = Array.isArray(this.editData.photos) ? this.editData.photos : [];

        // Safely map photos
        this.localEditData = {
          title: this.editData.title || '',
          description: this.editData.description || '',
          photos: photos.map((photo, index) => ({
            url: typeof photo === 'string' ? photo : photo.url || photo,
            id: (this.editData.uploadIds && this.editData.uploadIds[index]) || null
          }))
        };
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

    // Image handling methods
    async removeImage(index) {
      const imageToRemove = this.localEditData.photos[index];

      // If the image has an ID (meaning it exists in the database), delete it
      if (imageToRemove.id) {
        try {
          await axios.delete(`/api/delete-timeline-image/${imageToRemove.id}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          });

          // Remove from local array only after successful deletion
          this.localEditData.photos.splice(index, 1);

        } catch (error) {
          console.error('Error deleting image:', error);
          // You might want to show an error message to the user here
        }
      } else {
        // If it's a new image that hasn't been saved to DB yet, just remove it
        this.localEditData.photos.splice(index, 1);
      }
    },

    // Drag and drop methods
    handleDragStart(index) {
      this.dragStartIndex = index;
      // Add visual feedback
      event.target.classList.add('dragging');
    },

    handleDragEnter(index) {
      this.dragOverIndex = index;
    },

    handleDragEnd() {
      // Remove visual feedback
      event.target.classList.remove('dragging');
      this.dragOverIndex = null;
    },

    handleDrop() {
      if (this.dragStartIndex !== null && this.dragOverIndex !== null &&
        this.dragStartIndex !== this.dragOverIndex) {
        // Reorder the photos array
        const movedItem = this.localEditData.photos.splice(this.dragStartIndex, 1)[0];
        this.localEditData.photos.splice(this.dragOverIndex, 0, movedItem);

        // You could optionally update the order immediately here if you want
        // But we'll wait until save to minimize API calls
      }
      this.dragStartIndex = null;
      this.dragOverIndex = null;
    },

    async savePostEdit() {
      // Update the basic post data
      this.editData.title = this.localEditData.title;
      this.editData.description = this.localEditData.description;
      this.editData.photos = [...this.localEditData.photos];

      // Prepare the image order update
      const imageOrderUpdate = {
        timeline_id: this.editData.timeline_id,
        image_order: this.localEditData.photos.map((photo, index) => ({
          id: photo.id,
          order: index + 1  // Starting order from 1
        }))
      };

      try {
        // First update the image order
        await axios.post('/api/update-image-order', imageOrderUpdate, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        // Then emit the save event
        this.$emit("save-edit");

      } catch (error) {
        console.error('Error updating image order:', error);
        // You might want to show an error message to the user here
        // But still emit save-edit in case you want to proceed with other updates
        this.$emit("save-edit");
      }
    },

    closeModal() {
      this.$emit("close");
    },
  },
};
</script>

<style scoped>
.image-edit-section {
  margin: 20px 0;
  padding: 20px;
  border: 2px dashed #e0e0e0;
  border-radius: 8px;
  background-color: #f9f9f9;
  transition: all 0.3s ease;
}

.image-edit-section:hover {
  border-color: #4a9fe0;
  background-color: #f5f9fd;
}

.image-preview-container {
  min-height: 150px;
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin: 15px 0;
  padding: 3px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.image-preview-item {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  position: relative;
  width: 160px;
  height: 130px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  background-color: #fff;
}

.image-preview-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.image-preview-item.dragging {
  opacity: 0.7;
  transform: scale(1.05) rotate(2deg);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
  z-index: 10;
}

.image-preview-item.drag-over {
  transform: scale(1.03);
  box-shadow: 0 0 0 2px #4a9fe0;
  background-color: rgba(74, 159, 224, 0.1);
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s ease;
}

.image-actions {
  position: absolute;
  top: 8px;
  right: 8px;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.image-preview-item:hover .image-actions {
  opacity: 1;
}

.remove-image-btn {
  background-color: #ff4444;
  color: white;
  border: none;
  font-weight: bold;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  font-size: 12px;
  line-height: 22px;
  text-align: center;
  padding: 0;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
}

.remove-image-btn:hover {
  background-color: #cc0000;
  transform: scale(1.1);
}

/* Drop indicator styles */
.drop-indicator {
  position: absolute;
  height: 4px;
  background-color: #4a9fe0;
  border-radius: 2px;
  pointer-events: none;
  z-index: 1;
  transition: all 0.2s ease;
}

/* Add some animation for drag operations */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(74, 159, 224, 0.4);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(74, 159, 224, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(74, 159, 224, 0);
  }
}

.image-preview-container.drag-active {
  animation: pulse 2s infinite;
  border: 2px dashed #4a9fe0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .image-preview-item {
    width: 120px;
    height: 100px;
  }

  .image-preview-container {
    gap: 15px;
  }
}

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