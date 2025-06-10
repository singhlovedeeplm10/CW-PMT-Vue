<template>
  <master-component>
    <div class="timeline-container">
      <h2 class="timeline-heading">Activity Timeline</h2>
      <!-- Loader Spinner -->
      <div v-if="loading" class="loader-container">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <!-- Message when there is no data -->
      <div v-if="!loading && events.length === 0" class="no-data-message">
        <p>No activity timelines available to show.</p>
      </div>
      <div class="timeline">
        <div v-for="(event, index) in events" :key="index" class="timeline-item">
          <div class="timeline-icon" :class="event.type">
            <!-- Display icon based on event type (video/image) -->
            <i v-if="event.type === 'video'" class="fas fa-video"></i>
            <i v-else-if="event.type === 'photos'" class="fas fa-image"></i>
          </div>
          <div class="timeline-content">
            <div class="timeline-date">{{ formatDate(event.date) }}</div>
            <div class="timeline-card">
              <!-- <h3 class="timeline-head">{{ event.timeline }}</h3> -->
              <h2 class="timeline-title" v-html="event.title"></h2>
              <p class="timeline-description" v-html="event.description"></p>


              <!-- External Link Display (YouTube Thumbnails) -->
              <template v-if="event.externalLinks && event.externalLinks.length">
                <div class="external-links">
                  <div v-for="(link, i) in event.externalLinks" :key="i" class="external-link">
                    <a @click.prevent="openVideoModal(link.url)" class="thumbnail-link">
                      <img v-if="link.thumbnail" :src="link.thumbnail" alt="External Thumbnail" class="thumbnail" />
                      <div class="link-icon-overlay">
                        <i :class="link.icon" class="link-icon"></i>
                      </div>
                    </a>
                  </div>
                </div>
              </template>

              <!-- Combined Media Display -->
              <template v-if="event.mediaFiles && event.mediaFiles.length">
                <div class="media-container">
                  <!-- For single media item -->
                  <template v-if="event.mediaFiles.length === 1">
                    <img v-if="event.mediaFiles[0].type === 'image'" :src="event.mediaFiles[0].url" alt="Photo"
                      @click="viewImage(event.mediaFiles[0].url)" class="clickable-image single-media" />
                    <video v-else-if="event.mediaFiles[0].type === 'video'" :src="event.mediaFiles[0].url" controls
                      class="video-player single-media"></video>
                  </template>

                  <!-- For multiple media items -->
                  <div v-else class="media-gallery">
                    <template v-for="(file, index) in event.mediaFiles" :key="file.id">
                      <div class="media-item" @click="viewMedia(file, index)">
                        <img v-if="file.type === 'image'" :src="file.url" alt="Photo" class="clickable-image" />
                        <video v-else-if="file.type === 'video'" :src="file.url" controls
                          class="video-thumbnail"></video>
                        <!-- <div v-if="file.type === 'video'" class="video-play-icon">
                          <i class="fas fa-play"></i>
                        </div> -->
                      </div>
                    </template>
                  </div>
                </div>
              </template>

              <!-- Fallback for empty media -->
              <!-- <p v-else>No media available</p> -->

              <div class="post-actions">
                <i class="fas fa-thumbs-up action-icon" :class="{ 'liked': event.likedByUser }"
                  @click="likePost(index)"></i>
                <!-- Likes Count -->
                <span class="likes-count" @click="openLikesModal(event)">
                  {{ event.likes_count }}
                </span>

                <!-- Modal -->
                <div v-if="isLikesModalOpen" class="modal-overlay-likes" @click.self="closeLikesModal">
                  <div class="modal-content-likes">
                    <h5 class="modal-title">Liked by</h5>
                    <ul class="user-list">
                      <li v-for="user in selectedLikedUsers" :key="user.name" class="user-item">
                        <!-- User profile image in a circular format -->
                        <img v-if="user.user_image_url" :src="user.user_image_url" alt="User Image" class="user-image">
                        <span class="user-name">{{ user.name }}</span>
                      </li>
                    </ul>
                    <button class="close-button-likes" @click="closeLikesModal">Close</button>
                  </div>
                </div>

                <i class="fas fa-comment action-icon" @click="commentOnPost(index)"></i>


                <!-- Delete Button -->
                <button v-if="userRole === 'Admin'" class="delete-button top-right"
                  @click="deleteTimeline(event.timeline_id, index)">
                  <i class="fas fa-trash"></i>
                </button>

                <button v-if="userRole === 'Admin'" @click="editPost(event, index)" class="edit-button top-right">
                  <i class="fas fa-edit"></i>
                </button>

                <EditPostModal :show="showEditModal" :editData="editData" @save-edit="savePostEdit"
                  @close="closeEditModal" />
              </div>

              <!-- Comment Input Section -->
              <div v-if="event.showCommentInput" class="comment-input-container">
                <textarea v-model="event.newComment" class="comment-input full-width-textarea"
                  placeholder="Add a comment..." rows="3"></textarea>
                <button @click="postComment(index)" class="post-button">Post</button>
                <div v-for="(comment, idx) in event.comments || []" :key="idx" class="comment">
                  <div class="comment-container">
                    <img v-if="comment.user_image" :src="comment.user_image" alt="User Image" class="user-image" />
                    <div class="comment-content">
                      <strong v-if="comment.user_name" class="comment-author">{{ comment.user_name }}</strong>
                      <p class="comment-text">{{ comment.comment }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Video Modal -->
      <div v-if="isVideoModalVisible" class="video-modal-overlay" @click.self="closeVideoModal">
        <div class="video-modal">
          <button class="close-btn" @click="closeVideoModal">
            <i class="fas fa-times"></i>
          </button>
          <iframe v-if="videoUrl" :src="videoUrl" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen
            width="100%" height="315px" class="video-iframe"></iframe>
        </div>
      </div>

      <!-- Image Viewer Modal -->
      <!-- Media Viewer Modal -->
      <div v-if="selectedMedia" class="media-viewer" @click.self="closeMedia">
        <div class="media-container">
          <!-- Close button (top right) -->
          <button @click="closeMedia" class="close-btn">
            <i class="fas fa-times"></i>
          </button>

          <!-- Main media display -->
          <div class="media-display">
            <template v-if="selectedMedia.type === 'image'">
              <img :src="selectedMedia.url" class="enlarged-media" />
            </template>
            <template v-else-if="selectedMedia.type === 'video'">
              <video :src="selectedMedia.url" controls autoplay class="enlarged-media"></video>
            </template>
          </div>

          <!-- Navigation controls -->
          <div class="navigation-wrapper">
            <button @click.stop="prevMedia" class="nav-btn prev-btn" :disabled="currentMediaIndex === 0">
              <i class="fas fa-chevron-left"></i>
            </button>
            <button @click.stop="nextMedia" class="nav-btn next-btn"
              :disabled="currentMediaIndex === mediaItems.length - 1">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>

          <!-- Media counter -->
          <div class="media-counter">
            {{ currentMediaIndex + 1 }} / {{ mediaItems.length }}
          </div>
        </div>
      </div>

    </div>
  </master-component>
</template>

<script>
import axios from 'axios';
import MasterComponent from "./layouts/Master.vue";
import EditPostModal from "@/components/modals/EditPostModal.vue";

export default {
  name: "TimeLine",
  components: {
    MasterComponent,
    EditPostModal,
  },
  data() {
    return {
      events: [
        { comments: [] } // Initialize the comments array
      ],
      selectedImage: null,
      currentImageIndex: 0, // New state for managing the current image in the slider
      images: [], // Store the images to show in the modal
      showEditModal: false,
      editData: {
        title: '',
        description: '',
        photos: '',
        eventIndex: null
      },
      userRole: null,  // Add the userRole property
      isVideoModalVisible: false,
      videoUrl: null,  // Holds the YouTube video URL
      isLikesModalOpen: false,
      selectedLikedUsers: [],
      loading: true,
      selectedMedia: null,
      currentMediaIndex: 0,
      mediaItems: [],
    };
  },
  created() {
    this.fetchTimelineData();
    this.fetchUserRole(); // Fetch the user role when the component is created
  },
  watch: {
    selectedImage(newVal) {
      if (newVal) {
        window.addEventListener('keydown', this.handleKeyDown);
      } else {
        window.removeEventListener('keydown', this.handleKeyDown);
      }
    }
  },
  methods: {
    nextMedia() {
      if (this.currentMediaIndex < this.mediaItems.length - 1) {
        this.currentMediaIndex++;
        this.selectedMedia = this.mediaItems[this.currentMediaIndex];
      }
    },
    prevMedia() {
      if (this.currentMediaIndex > 0) {
        this.currentMediaIndex--;
        this.selectedMedia = this.mediaItems[this.currentMediaIndex];
      }
    },
    closeMedia() {
      this.selectedMedia = null;
      this.mediaItems = [];
    },

    viewMedia(file, index = null) {
      this.selectedMedia = file;

      const eventIndex = this.events.findIndex(event =>
        event.mediaFiles.some(f => f.url === file.url)
      );

      if (eventIndex !== -1) {
        const event = this.events[eventIndex];

        this.mediaItems = event.mediaFiles.map(f => ({
          url: f.url,
          type: f.type,
        }));

        this.currentMediaIndex = index !== null
          ? index
          : this.mediaItems.findIndex(m => m.url === file.url);
      }
    },

    // Open the modal and set liked users for the clicked post
    openLikesModal(event) {
      this.selectedLikedUsers = event.liked_users || []; // Fetch liked users
      this.isLikesModalOpen = true; // Show modal
    },
    // Close the modal
    closeLikesModal() {
      this.isLikesModalOpen = false;
    },
    async fetchUserRole() {
      try {
        const response = await axios.get("/api/user-role", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        this.userRole = response.data.role;
      } catch (error) {
        console.error("Error fetching user role:", error);
      }
    },
    async fetchTimelineData() {
      try {
        const response = await axios.get('/api/timelines', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        this.events = response.data.map((timeline) => {
          // Sort timeline_uploads by file_order
          const sortedUploads = timeline.timeline_uploads.sort((a, b) => {
            return (a.file_order || 0) - (b.file_order || 0);
          });

          // Process all media files (both images and videos) together
          const mediaFiles = sortedUploads
            .filter((upload) => (upload.file_type === 'image' || upload.file_type === 'video') && upload.file_path)
            .map((upload) => ({
              type: upload.file_type,
              url: `/uploads/${upload.file_path}`,
              id: upload.id,
              file_order: upload.file_order
            }));

          return {
            date: timeline.created_at,
            type: sortedUploads.some((upload) => upload.file_type === 'video') ? 'video' : 'photos',
            timeline: `Posted by ${timeline.user ? timeline.user.name : 'Unknown User'}`,
            title: timeline.title,
            description: timeline.description,
            mediaFiles, // This now contains both images and videos in correct order
            uploadIds: sortedUploads.filter((upload) => upload.file_path)
              .map((upload) => upload.id) || [],
            externalLinks: sortedUploads
              .filter((upload) => upload.file_link)
              .map((upload) => ({
                url: upload.file_link,
                thumbnail: upload.thumbnail || null,
                icon: upload.icon || 'fas fa-link',
              })) || [],
            likes_count: timeline.likes_count || 0,
            liked_users: timeline.liked_users || [],
            timeline_id: timeline.id,
            timeline_uploads_id: sortedUploads[0]?.id || null,
            likedByUser: timeline.likedByUser || false,
            comments: [],
          };
        });
        this.loading = false;

      } catch (error) {
        console.error('There was an error fetching the timeline data:', error);
        this.loading = false;
      }
    },

    formatDate(date) {
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(date).toLocaleDateString(undefined, options);
    },

    openVideoModal(url) {
      // Extract the YouTube video ID from the URL and set it to the iframe source
      const videoId = this.extractYouTubeId(url);
      this.videoUrl = `https://www.youtube.com/embed/${videoId}`;
      this.isVideoModalVisible = true;
    },

    closeVideoModal() {
      this.isVideoModalVisible = false;
      this.videoUrl = null;  // Clear the video URL
    },

    extractYouTubeId(url) {
      const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*\?v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
      return match ? match[1] : null;
    },

    playVideo(url) {
      const iframe = document.createElement("iframe");
      iframe.src = url;
      iframe.frameBorder = "0";
      iframe.allowFullscreen = true;
      iframe.style.width = "100%";
      iframe.style.height = "400px";
      iframe.style.borderRadius = "8px";

      const videoWrapper = event.target.closest(".video-wrapper");
      videoWrapper.innerHTML = ""; // Clear previous content
      videoWrapper.appendChild(iframe);
    },

    viewImage(url, index = null) {
      this.selectedImage = url;

      // Find the event containing this media file
      const eventIndex = this.events.findIndex(event =>
        event.mediaFiles.some(file => file.url === url)
      );

      if (eventIndex !== -1) {
        const event = this.events[eventIndex];

        // Filter only images for the slider (optional - you might want to include videos too)
        this.images = event.mediaFiles
          .filter(file => file.type === 'image')
          .map(file => file.url);
        this.currentImageIndex = index !== null ?
          index :
          this.images.indexOf(url);
      }
    },

    handleKeyDown(event) {
      if (!this.selectedMedia) return;

      if (event.key === 'ArrowLeft') {
        this.prevMedia();
      } else if (event.key === 'ArrowRight') {
        this.nextMedia();
      } else if (event.key === 'Escape') {
        this.closeMedia();
      }
    },

    async likePost(index) {
      const event = this.events[index];
      try {
        const response = await axios.post(
          '/api/like-post',
          {
            timeline_id: event.timeline_id,
            timeline_uploads_id: event.timeline_uploads_id,
          },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          }
        );

        if (response.data.success) {
          // Update the likes count and liked state from the backend
          this.events[index].likes_count = response.data.totalLikes;
          this.events[index].likedByUser = response.data.likedByUser;
        }
      } catch (error) {
        console.error('Error liking post:', error);
      }
    },

    async postComment(index) {
      const event = this.events[index];
      const comment = event.newComment;

      if (comment) {
        try {
          const response = await axios.post(
            '/api/timeline/comment',
            {
              timeline_id: event.timeline_id,
              timeline_uploads_id: event.timeline_uploads_id,
              comment: comment,
            },
            {
              headers: {
                Authorization: `Bearer ${localStorage.getItem("authToken")}`,
              },
            }
          );

          if (response.data.success) {
            // Fetch updated comments
            this.fetchComments(index);

            // Clear the input field
            event.newComment = '';
            event.showCommentInput = false;
          }
        } catch (error) {
          console.error('Error posting comment:', error);
        }
      }
    },

    async fetchComments(index) {
      const event = this.events[index];

      try {
        const response = await axios.get('/api/timeline/fetch-comments', {
          params: {
            timeline_id: [event.timeline_id],  // Ensure it's an array
            timeline_uploads_id: [event.timeline_uploads_id],  // Ensure it's an array
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (Array.isArray(response.data.comments)) {
          // Ensure that comments is an array before setting it
          this.events[index].comments = response.data.comments;
        } else {
          this.events[index].comments = []; // If no comments are returned, set as empty array
        }
      } catch (error) {
        console.error('Error fetching comments:', error);
      }
    },

    commentOnPost(index) {
      const event = this.events[index];

      // Toggle the visibility of the comment input field
      event.showCommentInput = !event.showCommentInput;

      if (event.showCommentInput) {
        // Fetch comments for the post when the input field is opened
        this.fetchComments(index);
      }
    },

    // Edit Post Methods
    editPost(event, index) {
      this.editData = {
        title: event.title || '',
        description: event.description || '',
        photos: event.mediaFiles
          ? event.mediaFiles.filter(f => f.type === 'image').map(f => f.url)
          : [],
        uploadIds: event.uploadIds || [],
        timeline_id: event.timeline_id,
        eventIndex: index
      };
      this.showEditModal = true;
    },

    openEditModal(post) {
      this.editData = { ...post };
      this.showEditModal = true;
    },

    closeEditModal() {
      this.showEditModal = false;
      this.editData = { title: '', description: '', photos: '', eventIndex: null };
    },

    async savePostEdit() {
      const eventIndex = this.editData.eventIndex;
      const updatedEvent = this.editData;

      try {
        const response = await axios.put(`/api/update-timelines/${this.events[eventIndex].timeline_id}`, {
          title: updatedEvent.title,
          description: updatedEvent.description
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (response.data.success) {
          // Update the event data in the timeline
          this.events[eventIndex].title = updatedEvent.title;
          this.events[eventIndex].description = updatedEvent.description;
          this.events[eventIndex].photos = updatedEvent.photos;
          this.closeEditModal();
          await this.fetchTimelineData();
        }
      } catch (error) {
        console.error('Error saving post edit:', error);
      }
    },
    async deleteTimeline(timelineId, index) {
      if (confirm('Are you sure you want to delete this timeline?')) {
        try {
          // Send delete request to the backend
          const response = await axios.delete(`/api/delete/timelines/${timelineId}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          });

          if (response.data.success) {
            // Remove the timeline from the events array
            this.events.splice(index, 1);
            alert('Timeline deleted successfully');
          } else {
            alert('Error deleting timeline');
          }
        } catch (error) {
          console.error('Error deleting timeline:', error);
          alert('An error occurred while deleting the timeline');
        }
      }
    },
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeyDown);
  },
  beforeDestroy() {
    window.removeEventListener('keydown', this.handleKeyDown);
  },
};
</script>

<style scoped>
.enlarged-video {
  max-width: 90%;
  max-height: 80vh;
  border-radius: 8px;
}

.media-container {
  margin: 15px 0;
}

.single-media {
  max-width: 100%;
  max-height: 500px;
  display: block;
  margin: 0 auto;
  border-radius: 8px;
}

.media-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 10px;
}

.media-item {
  position: relative;
  cursor: pointer;
  border-radius: 8px;
  overflow: hidden;
  aspect-ratio: 1;
}

.media-item img,
.media-item video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.media-item:hover img {
  transform: scale(1.05);
}

.video-thumbnail {
  background-color: #000;
}

.video-play-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 2rem;
  opacity: 0.8;
}

.video-player {
  width: 100%;
  max-height: 500px;
  border-radius: 8px;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.no-data-message {
  text-align: center;
  margin: 20px;
  font-size: 1.2em;
  color: #666;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100px;
}

.video-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.video-modal {
  position: relative;
  border-radius: 8px;
  width: 80%;
  max-width: 900px;
}

.video-iframe {
  width: 100%;
  height: 500px;
  border-radius: 8px;
}

.comment-input-container {
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  width: 100%;
  margin-top: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.comment-input-container h4 {
  margin-bottom: 10px;
  color: #2c3e50;
  font-weight: 600;
  font-size: 16px;
}

.comment-input {
  width: calc(100% - 100px);
  padding: 10px;
  margin-right: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
}

.comment-input:focus {
  border-color: #3498db;
  outline: none;
}

.full-width-textarea {
  width: 100%;
  resize: none;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
  box-sizing: border-box;
}

.post-button {
  margin-top: 5px;
  padding: 5px 10px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}

.post-button:hover {
  background-color: #0056b3;
}

.post-actions {
  align-items: center;
  margin-top: 10px;
  display: flex;
  gap: 8px;
}

.action-icon {
  font-size: 20px;
  color: #7f8c8d;
  cursor: pointer;
  transition: color 0.3s, transform 0.3s;
}

.action-icon:hover {
  color: #3498db;
  transform: scale(1.2);
}

.comment {
  margin-top: 10px;
  padding: 10px;
  border: 1px solid #eaeaea;
  border-radius: 5px;
  background-color: #fff;
}

.comment-container {
  display: flex;
  align-items: flex-start;
}

.user-image {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 10px;
}

.comment-content {
  display: flex;
  flex-direction: column;
}

.comment-author {
  font-weight: bold;
  color: #007bff;
  margin-bottom: 4px;
}

.comment-text {
  font-size: 14px;
  color: #333;
  line-height: 1.5;
  margin: 0;
}

.comment:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.comment strong {
  color: #3498db;
  font-weight: 600;
}

.comments-list {
  margin-top: 15px;
}

.comment:not(:last-child) {
  margin-bottom: 10px;
}

/* Base styles */
.media-viewer {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.92);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
}

.media-container {
  position: relative;
  width: 95%;
  height: 95%;
  max-width: 1400px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Close button - top right corner */
.close-btn {
  position: fixed;
  top: 20px;
  right: 20px;
  background: rgba(255, 255, 255, 0.15);
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 20px;
  cursor: pointer;
  z-index: 100;
  transition: all 0.2s ease;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Navigation buttons - left/right edges */
.navigation-wrapper {
  position: fixed;
  width: 100%;
  height: 100%;
  pointer-events: none;
  top: 0;
  left: 0;
}

.nav-btn {
  position: fixed;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.15);
  color: white;
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  font-size: 24px;
  cursor: pointer;
  z-index: 100;
  transition: all 0.2s ease;
  pointer-events: auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.prev-btn {
  left: 20px;
}

.next-btn {
  right: 20px;
}

/* Media display */
.media-display {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}


.enlarged-media {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 4px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  transition: transform 0.3s ease;
}

.enlarged-media:hover {
  transform: scale(1.02);
}

video.enlarged-media {
  width: 80%;
  height: 80%;
  background-color: #000;
}

/* Media counter - bottom center */
.media-counter {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.5);
  color: white;
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 14px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .nav-btn {
    width: 40px;
    height: 40px;
    font-size: 20px;
  }

  .close-btn {
    width: 36px;
    height: 36px;
    font-size: 18px;
  }

  video.enlarged-media {
    width: 95%;
    height: auto;
  }
}

@media (max-width: 480px) {
  .nav-btn {
    width: 36px;
    height: 36px;
    font-size: 18px;
    background: rgba(255, 255, 255, 0.25);
  }

  .prev-btn {
    left: 10px;
  }

  .next-btn {
    right: 10px;
  }

  .close-btn {
    top: 10px;
    right: 10px;
  }

  .media-counter {
    font-size: 12px;
    padding: 4px 12px;
    bottom: 10px;
  }
}

.clickable-image {
  cursor: pointer;
  transition: transform 0.3s ease;
}

.clickable-image:hover {
  transform: scale(1.05);
}

.timeline-container {
  max-width: 100%;
  margin: 30px auto;
  padding: 20px;
}

.timeline-heading {
  text-align: center;
  font-size: 24px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 30px;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  position: relative;
}

.timeline-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
  font-weight: bold;
  flex-shrink: 0;
}

.timeline-icon.email {
  background-color: #3498db;
}

.timeline-icon.comment {
  background-color: #e67e22;
}

.timeline-icon.photos {
  background-color: #9b59b6;
}

.timeline-icon.video {
  background-color: #2ecc71;
}

.timeline-content {
  flex-grow: 1;
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.timeline-date {
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 10px;
  font-weight: 500;
}

.timeline-card {
  display: flex;
  flex-direction: column;
  position: relative;
}

.timeline-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #34495e;
  margin-bottom: 10px;
}

.timeline-head {
  font-size: 14px;
  font-weight: 700;
  color: #667d95;
  font-style: italic;
  text-align: left;
  margin: 0;
}


.timeline-description {
  font-size: 0.9rem;
  color: darkcyan;
  margin-bottom: 0px;
  line-height: 1.4;
}

.timeline-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #34495e;
  margin-bottom: 10px;
}

.timeline-actions {
  display: flex;
  gap: 10px;
}

.btn {
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 500;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-primary {
  background-color: #3498db;
  color: #fff;
}

.btn-primary:hover {
  background-color: #2980b9;
  transform: translateY(-2px);
}

.btn-danger {
  background-color: #e74c3c;
  color: #fff;
}

.btn-danger:hover {
  background-color: #c0392b;
  transform: translateY(-2px);
}

.photo-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 15px;
  margin-top: 20px;
  transition: transform 0.3s ease;
}

.photo-gallery img {
  width: 100%;
  height: 100%;
  border-radius: 10px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.photo-gallery img:hover {
  transform: scale(1.1);
}

.video-wrapper {
  position: relative;
  width: 450px;
  height: 350px;

}

.video-wrapper video {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  object-fit: cover;
}


.video-wrapper iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 8px;
}

@media (max-width: 768px) {
  .timeline-container {
    padding: 15px;
  }

  .photo-gallery {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
  }

  .photo-gallery img {
    border-radius: 8px;
  }

  .timeline-item {
    flex-direction: column;
    align-items: center;
  }

  .timeline-icon {
    margin-bottom: 10px;
  }

  .timeline-content {
    padding: 15px;
  }
}

@media (max-width: 480px) {
  .photo-gallery {
    grid-template-columns: 1fr;
    gap: 8px;
  }
}

.thumbnail {
  width: 633px;
  height: 350px;
  object-fit: cover;
  cursor: pointer;
}

.external-links {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.external-link a {
  text-decoration: none;
  color: inherit;
}

.external-link span {
  display: inline-block;
  margin-top: 5px;
  font-size: 0.9rem;
}

.thumbnail-link {
  position: relative;
  display: inline-block;
}

.link-icon-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.link-icon {
  font-size: 2rem;
  text-shadow: 0 0 5px black;
  color: white;
}

.link-icon-img {
  width: 28%;
  height: 50%;
  object-fit: contain;
}

.edit-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
  color: #666;
  position: absolute;
}

.edit-button.top-right {
  top: 10px;
  right: 20px;
}

.delete-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
  color: #666;
  position: absolute;
}

.delete-button.top-right {
  top: 10px;
  right: 40px;
}

.liked {
  color: blue;

}

.likes-count {
  cursor: pointer;
  color: black;
  font-size: 16px;
  border-radius: 4px;
}

.modal-overlay-likes {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content-likes {
  background: linear-gradient(135deg, #ffffff, #f9f9f9);
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  max-width: 450px;
  width: 100%;
  animation: fadeIn 0.3s ease-in-out;
  position: relative;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 15px;
  text-align: center;
  color: #333;
}

.user-list {
  padding: 0;
  margin: 0;
  list-style: none;
  max-height: 250px;
  overflow-y: auto;
  border-top: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
  padding-top: 10px;
  padding-bottom: 10px;
}

.user-item {
  display: flex;
  align-items: center;
  padding: 10px;
  border-bottom: 1px solid #eee;
  transition: background-color 0.2s;
}

.user-item:hover {
  background-color: #f5f5f5;
}

.user-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.user-name {
  font-size: 1rem;
  font-weight: 500;
  color: #444;
}

.close-button-likes {
  background: linear-gradient(135deg, #ff5f6d, #ffc371);
  color: #fff;
  border: none;
  padding: 10px 20px;
  font-size: 1rem;
  cursor: pointer;
  border-radius: 25px;
  margin-top: 20px;
  display: block;
  width: 100%;
  text-align: center;
  box-shadow: 0 4px 10px rgba(255, 95, 109, 0.3);
  transition: transform 0.2s, box-shadow 0.2s;
}

.close-button-likes:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(255, 95, 109, 0.4);
}

/* Scrollbar Styling */
.user-list::-webkit-scrollbar {
  width: 8px;
}

.user-list::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 4px;
}

/* Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>