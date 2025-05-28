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

              <!-- Local Media Display -->
              <template v-if="event.type === 'photos'">
                <div class="photo-gallery">
                  <img v-for="(photo, index) in event.photos" :key="index" :src="photo" alt="Photo"
                    @click="viewImage(photo)" class="clickable-image" />
                </div>
              </template>

              <!-- Video or Other Content -->
              <template v-else-if="event.type === 'video'">
                <div class="video-wrapper">
                  <video v-if="event.videoUrl" :src="event.videoUrl" controls class="video-player"></video>
                  <p v-else>No video available</p>
                </div>
              </template>
              <!-- <h3 class="timeline-head">{{ event.timeline }}</h3> -->
              <!-- Like and Comment Icons -->
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


                <!-- Comment Input Section -->
                <div v-if="event.showCommentInput" class="comment-input-container">
                  <textarea v-model="event.newComment" class="comment-input full-width-textarea"
                    placeholder="Add a comment..." rows="3"></textarea>
                  <button @click="postComment(index)" class="post-button">Post</button>
                  <div v-for="(comment, idx) in event.comments || []" :key="idx" class="comment">
                    <div class="comment-author-info">
                      <img v-if="comment.user_image" :src="comment.user_image" alt="User Image" class="user-image">
                      <strong v-if="comment.user_name" class="comment-author">{{ comment.user_name }} </strong>
                    </div>
                    <p class="comment-text">
                      <span v-if="comment.user_name">{{ comment.comment }}</span>
                      <span v-else>{{ comment.comment }}</span>
                    </p>
                  </div>
                </div>


                <button v-if="userRole === 'Admin'" @click="editPost(event, index)" class="edit-button top-right">
                  <i class="fas fa-edit"></i>
                </button>



                <EditPostModal :show="showEditModal" :editData="editData" @save-edit="savePostEdit"
                  @close="closeEditModal" />
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
      <div v-if="selectedImage" class="image-viewer" @click.self="closeImage">
        <div class="image-slider">
          <!-- Display the current image -->
          <img :src="selectedImage" class="enlarged-image" />

          <!-- Navigation buttons -->
          <div class="slider-controls">
            <button v-if="currentImageIndex > 0" @click="prevImage" class="prev-btn">
              <i class="fas fa-chevron-left"></i>
            </button>
            <button v-if="currentImageIndex < images.length - 1" @click="nextImage" class="next-btn">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>

          <!-- Close button -->
          <button @click="closeImage" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
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
        eventIndex: null
      },
      userRole: null,  // Add the userRole property
      isVideoModalVisible: false,
      videoUrl: null,  // Holds the YouTube video URL
      isLikesModalOpen: false,
      selectedLikedUsers: [],
      loading: true,
    };
  },
  created() {
    this.fetchTimelineData();
    this.fetchUserRole(); // Fetch the user role when the component is created
  },
  methods: {
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

        this.events = response.data.map((timeline) => ({
          date: timeline.created_at,
          type: timeline.timeline_uploads.some((upload) => upload.file_type === 'video') ? 'video' : 'photos',
          timeline: `Posted by ${timeline.user ? timeline.user.name : 'Unknown User'}`,
          title: timeline.title,
          description: timeline.description,
          photos: timeline.timeline_uploads.filter((upload) => upload.file_type === 'image' && upload.file_path)
            .map((upload) => `/storage/${upload.file_path}`) || [],
          videoUrl: timeline.timeline_uploads.find((upload) => upload.file_type === 'video' && upload.file_path)
            ? `/storage/${timeline.timeline_uploads.find((upload) => upload.file_type === 'video').file_path}`
            : null,
          externalLinks: timeline.timeline_uploads
            .filter((upload) => upload.file_link)
            .map((upload) => ({
              url: upload.file_link,
              thumbnail: upload.thumbnail || null,
              icon: upload.icon || 'fas fa-link',
            })) || [],
          likes_count: timeline.likes_count || 0,
          liked_users: timeline.liked_users || [], // Add liked_users
          timeline_id: timeline.id,
          timeline_uploads_id: timeline.timeline_uploads[0]?.id || null,
          likedByUser: timeline.likedByUser || false,
          comments: [],
        }));
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

    viewImage(photo) {
      this.selectedImage = photo;
      // Set up the images array (for the slider)
      const eventIndex = this.events.findIndex(event => event.photos.includes(photo));
      if (eventIndex !== -1) {
        this.images = this.events[eventIndex].photos;  // Get all images from the selected event
        this.currentImageIndex = this.images.indexOf(photo);  // Set the current image index
      }
    },

    closeImage() {
      this.selectedImage = null;
      this.images = [];
    },

    // Navigate to the next image
    nextImage() {
      if (this.currentImageIndex < this.images.length - 1) {
        this.currentImageIndex++;
        this.selectedImage = this.images[this.currentImageIndex];
      }
    },

    // Navigate to the previous image
    prevImage() {
      if (this.currentImageIndex > 0) {
        this.currentImageIndex--;
        this.selectedImage = this.images[this.currentImageIndex];
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
      // Prefill the modal with the selected post data
      this.editData.title = event.title;
      this.editData.description = event.description;
      this.editData.eventIndex = index;
      this.showEditModal = true;
    },

    openEditModal(post) {
      this.editData = { ...post };
      this.showEditModal = true;
    },

    closeEditModal() {
      this.showEditModal = false;
      this.editData = { title: '', description: '', eventIndex: null };
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
          this.closeEditModal();
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
};
</script>

<style scoped>
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
  /* background: white;
  padding: 20px; */
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
  /* Make the textarea full width */
  resize: none;
  /* Disable resizing if needed */
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

/* Like and Comment Icons */
.post-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 10px;
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

.comment-text {
  font-size: 14px;
  color: #333;
  line-height: 1.5;
}

.comment-author {
  font-weight: bold;
  color: #007bff;
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

/* Image Viewer Modal Styles */
.image-viewer {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.image-viewer[style*="display: flex"] {
  opacity: 1;
  visibility: visible;
}


.image-slider {
  position: relative;
  max-width: 90%;
  max-height: 90%;
  /* background-color: white;
  padding: 20px; */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.enlarged-image {
  max-width: 100%;
  max-height: 70vh;
  object-fit: contain;
}

.slider-controls {
  position: absolute;
  bottom: 10px;
  width: 100%;
  display: flex;
  justify-content: center;
  /* Center the buttons */
  gap: 15px;
  /* Space between buttons */
}

.prev-btn,
.next-btn {
  background-color: rgba(0, 0, 0, 0.6);
  color: white;
  border: none;
  padding: 10px 20px;
  font-size: 18px;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.3s;
}

.prev-btn:hover,
.next-btn:hover {
  background-color: rgba(0, 0, 0, 0.8);
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: rgba(0, 0, 0, 0.6);
  color: white;
  border: none;
  padding: 5px 12px;
  font-size: 18px;
  border-radius: 50%;
  cursor: pointer;
  transition: background-color 0.3s;
}

.close-btn:hover {
  background-color: rgba(0, 0, 0, 0.8);
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
  /* Reduced max width */
  margin: 30px auto;
  padding: 20px;
  /* Reduced padding */
}

.timeline-heading {
  text-align: center;
  font-size: 24px;
  /* Smaller heading font size */
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 30px;
  /* Reduced margin */
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 20px;
  /* Reduced gap */
}

/* Timeline Item */
.timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  /* Reduced gap */
  position: relative;
}

.timeline-icon {
  width: 50px;
  /* Reduced size */
  height: 50px;
  /* Reduced size */
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
  /* Smaller icon font size */
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

/* Timeline Content */
.timeline-content {
  flex-grow: 1;
  background: #fff;
  padding: 20px;
  /* Reduced padding */
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.timeline-date {
  font-size: 14px;
  /* Smaller date font size */
  color: #7f8c8d;
  margin-bottom: 10px;
  /* Reduced margin */
  font-weight: 500;
}

.timeline-card {
  display: flex;
  flex-direction: column;
  position: relative;
}

.timeline-title {
  font-size: 1.3rem;
  /* Reduced title font size */
  font-weight: 700;
  color: #34495e;
  margin-bottom: 10px;
  /* Reduced margin */
}

.timeline-head {
  font-size: 14px;
  font-weight: 700;
  color: #667d95;
  font-style: italic;
  text-align: left;
  margin: 0;
  /* remove auto-centering unless needed for layout */
}


.timeline-description {
  font-size: 0.9rem;
  /* Reduced description font size */
  color: darkcyan;
  margin-bottom: 15px;
  /* Reduced margin */
  line-height: 1.4;
}

.timeline-title {
  font-size: 1.3rem;
  /* Reduced title font size */
  font-weight: 700;
  color: #34495e;
  margin-bottom: 10px;
  /* Reduced margin */
}

/* Actions & Buttons */
.timeline-actions {
  display: flex;
  gap: 10px;
  /* Reduced gap */
}

.btn {
  padding: 8px 16px;
  /* Reduced padding */
  font-size: 14px;
  /* Smaller font size */
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

/* Photo Gallery */
.photo-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  /* Create a flexible grid layout */
  gap: 15px;
  /* Space between images */
  margin-top: 20px;
  /* Top margin for better spacing */
  transition: transform 0.3s ease;
}

.photo-gallery img {
  width: 100%;
  /* Ensure the images take the full width of their container */
  height: 100%;
  /* Ensure the images take the full height of their container */
  border-radius: 10px;
  /* Rounded corners for images */
  object-fit: cover;
  /* Make sure the image fills the space without stretching */
  transition: transform 0.3s ease;
  /* Smooth transition for hover effect */
}

.photo-gallery img:hover {
  transform: scale(1.1);
  /* Slight zoom effect when hovering over the image */
}

.video-wrapper {
  position: relative;
  width: 450px;
  /* Fixed width */
  height: 350px;
  /* Fixed height (16:9 aspect ratio) */

}

.video-wrapper video {
  width: 100%;
  height: 100%;
  /* Fill the wrapper */
  border-radius: 8px;
  object-fit: cover;
  /* Ensures the video covers the area */
}


.video-wrapper iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 8px;
}

/* Responsiveness */
@media (max-width: 768px) {
  .timeline-container {
    padding: 15px;
    /* Reduced padding */
  }

  .photo-gallery {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    /* Fewer columns on smaller screens */
    gap: 10px;
    /* Reduced gap between images on smaller screens */
  }

  .photo-gallery img {
    border-radius: 8px;
    /* Smaller border radius for smaller screens */
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
    /* Reduced padding */
  }
}

@media (max-width: 480px) {
  .photo-gallery {
    grid-template-columns: 1fr;
    /* Only one column on very small screens */
    gap: 8px;
    /* Further reduce the gap between images */
  }
}

.thumbnail {
  width: 450px;
  height: 350px;
  object-fit: cover;
  margin: 5px;
  border-radius: 8px;
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
  color: white;
  text-shadow: 0 0 5px black;
}

.edit-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
  color: #666;
  position: absolute;
  /* Absolute positioning for flexibility */
}

.edit-button.top-right {
  top: 10px;
  /* Adjust as needed */
  right: 20px;
  /* Adjust as needed */
}

.delete-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
  color: #666;
  position: absolute;
  /* Absolute positioning for flexibility */
}

.delete-button.top-right {
  top: 10px;
  /* Adjust as needed */
  right: 40px;
  /* Adjust as needed */
}

.liked {
  color: blue;
  /* Change the color of the like icon when it's liked */
}

.likes-count {
  cursor: pointer;
  color: black;
  font-size: 16px;
  border-radius: 4px;
}

/* Modal Overlay */
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

/* Modal Content */
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

/* Modal Title */
.modal-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 15px;
  text-align: center;
  color: #333;
}

/* User List */
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

/* User Item */
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

/* User Image */
.user-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

/* User Name */
.user-name {
  font-size: 1rem;
  font-weight: 500;
  color: #444;
}

/* Close Button */
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