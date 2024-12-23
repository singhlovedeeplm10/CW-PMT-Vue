<template>
  <master-component>
    <div class="timeline-container">
      <h2 class="timeline-heading">Activity Timeline</h2>
      <div class="timeline">
        <div v-for="(event, index) in events" :key="index" class="timeline-item">
          <div class="timeline-icon" :class="event.type"></div>
          <div class="timeline-content">
            <div class="timeline-date">{{ formatDate(event.date) }}</div>
            <div class="timeline-card">
              <h3 class="timeline-head">{{ event.timeline }}</h3>
              <h2 class="timeline-title">{{ event.title }}</h2>
              <p class="timeline-description">{{ event.description }}</p>

              <!-- External Link Display -->
              <template v-if="event.externalLinks.length">
                <div class="external-links">
                  <div v-for="(link, i) in event.externalLinks" :key="i" class="external-link">
                    <a :href="link.url" target="_blank" class="thumbnail-link">
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
                  <img
                    v-for="(photo, index) in event.photos"
                    :key="index"
                    :src="photo"
                    alt="Photo"
                    @click="viewImage(photo)"
                    class="clickable-image"
                  />
                </div>
              </template>

              <template v-else-if="event.type === 'video'">
                <div class="video-wrapper">
                  <video
                    v-if="event.videoUrl"
                    :src="event.videoUrl"
                    controls
                    class="video-player"
                  ></video>
                  <p v-else>No video available</p>
                </div>
              </template>

              <!-- Like and Comment Icons -->
              <div class="post-actions">
                <i class="fas fa-thumbs-up action-icon" @click="likePost(index)"></i>
                <span class="likes-count">{{ event.likes_count }}</span>
                <i class="fas fa-comment action-icon" @click="commentOnPost(index)"></i>

                <!-- Comment Input Section -->
                <div v-if="event.showCommentInput" class="comment-input-container">
                  <input
                    type="text"
                    v-model="event.newComment"
                    class="comment-input"
                    placeholder="Add a comment..."
                  />
                  <button @click="postComment(index)" class="post-button">Post</button>
                  <div v-for="(comment, idx) in event.comments || []" :key="idx" class="comment">
                  <p>
                    <strong v-if="comment.user_name">{{ comment.user_name }} commented: </strong>
                    <span v-if="comment.user_name">{{ comment.comment }}</span>
                    <span v-else>{{ comment.comment }}</span>
                  </p>
                </div>
                </div>

                <!-- Edit Post Button (Visible only for Admin) -->
                <button v-if="userRole === 'Admin'" @click="editPost(event, index)" class="edit-button">Edit Post</button>
              </div>

              <!-- Comment Section -->
              <!-- <div class="comments-list">
                
              </div> -->

            </div>
          </div>
        </div>
      </div>

      <!-- Image Viewer Modal -->
      <div v-if="selectedImage" class="image-viewer" @click="closeImage">
        <img :src="selectedImage" class="enlarged-image" />
      </div>

      <!-- Edit Post Modal -->
      <div v-if="showEditModal" class="edit-post-modal">
        <div class="modal-content">
          <h3>Edit Post</h3>
          <input type="text" v-model="editData.title" placeholder="Edit Title" class="modal-input" />
          <textarea v-model="editData.description" placeholder="Edit Description" class="modal-input"></textarea>
          <button @click="savePostEdit" class="save-button">Save</button>
          <button @click="closeEditModal" class="cancel-button">Cancel</button>
        </div>
      </div>
    </div>
  </master-component>
</template>



<script>
import axios from 'axios';
import MasterComponent from "./layouts/Master.vue";

export default {
  name: "TimeLine",
  components: { MasterComponent },
  data() {
    return {
      events: [
        { comments: [] } // Initialize the comments array
      ],
      selectedImage: null,
      showEditModal: false,
      editData: {
        title: '',
        description: '',
        eventIndex: null
      },
      userRole: null,  // Add the userRole property
    };
  },
  created() {
    this.fetchTimelineData();
    this.fetchUserRole(); // Fetch the user role when the component is created
  },
  methods: {
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
          timeline: `${timeline.user ? timeline.user.name : 'Unknown User'} shared a new post`,
          title: timeline.title,
          description: timeline.description,
          photos: timeline.timeline_uploads.filter((upload) => upload.file_type === 'image' && upload.file_path)
            .map((upload) => `/storage/${upload.file_path}`),
          videoUrl: timeline.timeline_uploads.find((upload) => upload.file_type === 'video' && upload.file_path)
            ? `/storage/${timeline.timeline_uploads.find((upload) => upload.file_type === 'video').file_path}` 
            : null,
          externalLinks: timeline.timeline_uploads
            .filter((upload) => upload.file_link)
            .map((upload) => ({
              url: upload.file_link,
              thumbnail: upload.thumbnail || null,
              icon: upload.icon || 'fas fa-link',
            })),
          likes_count: timeline.likes_count || 0, // Include the likes_count from the backend
          timeline_id: timeline.id,
          timeline_uploads_id: timeline.timeline_uploads[0]?.id || null, // Ensure valid ID
          comments: [],
        }));
      } catch (error) {
        console.error('There was an error fetching the timeline data:', error);
      }
    },

    formatDate(date) {
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(date).toLocaleDateString(undefined, options);
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
    },

    closeImage() {
      this.selectedImage = null;
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
          // Update the likes count from the backend
          this.events[index].likes_count = response.data.totalLikes;
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

    closeEditModal() {
      this.showEditModal = false;
      this.editData = { title: '', description: '', eventIndex: null };
    },

    async savePostEdit() {
      const eventIndex = this.editData.eventIndex;
      const updatedEvent = this.editData;

      try {
        const response = await axios.put(`/api/timelines/${this.events[eventIndex].timeline_id}`, {
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
  },
};
</script>

  <style scoped>

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 50%;
  max-width: 500px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.modal-input {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border-radius: 4px;
  border: 1px solid #ccc;
}

.save-button,
.cancel-button {
  padding: 10px 20px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.save-button:hover {
  background-color: #2980b9;
}

.cancel-button {
  background-color: #e74c3c;
}

.cancel-button:hover {
  background-color: #c0392b;
}
.comment-input-container {
  margin-top: 15px;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
.comment-input-container h4 {
  margin-bottom: 10px;
  color: #2c3e50;
  font-weight: 600;
  font-size: 16px;
}

.comment-input {
  width: 100%;
  padding: 12px;
  margin-bottom: 10px;
  border-radius: 6px;
  border: 1px solid #ddd;
  font-size: 14px;
  transition: border-color 0.3s ease;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
}

.comment-input:focus {
  border-color: #3498db;
  outline: none;
}

.post-button {
  padding: 10px 16px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.post-button:hover {
  background-color: #45a049;
  transform: scale(1.05);
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
  padding: 12px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  font-size: 14px;
  color: #2c3e50;
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
  
  /* Image Viewer */
  .image-viewer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  
  .enlarged-image {
    max-width: 80%; /* Smaller max width */
    max-height: 80%; /* Smaller max height */
    border-radius: 12px;
    transition: transform 0.3s ease-in-out;
  }
  
  .clickable-image {
    cursor: pointer;
    transition: transform 0.3s ease;
  }
  
  .clickable-image:hover {
    transform: scale(1.05);
  }
  .timeline-container {
    max-width: 100%; /* Reduced max width */
    margin: 30px auto;
    padding: 20px; /* Reduced padding */
  }
  
  .timeline-heading {
    text-align: center;
    font-size: 24px; /* Smaller heading font size */
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 30px; /* Reduced margin */
  }
  
  .timeline {
    display: flex;
    flex-direction: column;
    gap: 20px; /* Reduced gap */
  }
  
  /* Timeline Item */
  .timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 15px; /* Reduced gap */
    position: relative;
  }
  
  .timeline-icon {
    width: 50px; /* Reduced size */
    height: 50px; /* Reduced size */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px; /* Smaller icon font size */
    font-weight: bold;
    flex-shrink: 0;
  }
  
  h3 {
    font-size: 1.4rem; /* Smaller title font size */
    font-weight: 600;
    color: #333;
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
    padding: 20px; /* Reduced padding */
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  }
  
  .timeline-date {
    font-size: 14px; /* Smaller date font size */
    color: #7f8c8d;
    margin-bottom: 10px; /* Reduced margin */
    font-weight: 500;
  }
  
  .timeline-card {
    display: flex;
    flex-direction: column;
    position: relative;
  }
  
  .timeline-title {
    font-size: 1.3rem; /* Reduced title font size */
    font-weight: 700;
    color: #34495e;
    margin-bottom: 10px; /* Reduced margin */
  }
  .timeline-head {
    font-size: 1.3rem; /* Reduced title font size */
    font-weight: 700;
    color: #34495e;
    margin-bottom: 10px; /* Reduced margin */
  }
  
  .timeline-description {
    font-size: 0.9rem; /* Reduced description font size */
    color: darkcyan;
    margin-bottom: 15px; /* Reduced margin */
    line-height: 1.4;
  }
  .timeline-title {
    font-size: 1.3rem; /* Reduced title font size */
    font-weight: 700;
    color: #34495e;
    margin-bottom: 10px; /* Reduced margin */
  }
  
  /* Actions & Buttons */
  .timeline-actions {
    display: flex;
    gap: 10px; /* Reduced gap */
  }
  
  .btn {
    padding: 8px 16px; /* Reduced padding */
    font-size: 14px; /* Smaller font size */
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
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); /* Create a flexible grid layout */
  gap: 15px; /* Space between images */
  margin-top: 20px; /* Top margin for better spacing */
  transition: transform 0.3s ease;
}
  
.photo-gallery img {
  width: 100%; /* Ensure the images take the full width of their container */
  height: 100%; /* Ensure the images take the full height of their container */
  border-radius: 10px; /* Rounded corners for images */
  object-fit: cover; /* Make sure the image fills the space without stretching */
  transition: transform 0.3s ease; /* Smooth transition for hover effect */
}
  
.photo-gallery img:hover {
  transform: scale(1.1); /* Slight zoom effect when hovering over the image */
}
  
  .video-wrapper {
  position: relative;
  width: 450px; /* Fixed width */
  height: 350px; /* Fixed height (16:9 aspect ratio) */
  
}

.video-wrapper video {
  width: 100%;
  height: 100%; /* Fill the wrapper */
  border-radius: 8px;
  object-fit: cover; /* Ensures the video covers the area */
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
      padding: 15px; /* Reduced padding */
    }
    .photo-gallery {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); /* Fewer columns on smaller screens */
    gap: 10px; /* Reduced gap between images on smaller screens */
  }
  .photo-gallery img {
    border-radius: 8px; /* Smaller border radius for smaller screens */
  }
  
    .timeline-item {
      flex-direction: column;
      align-items: center;
    }
  
    .timeline-icon {
      margin-bottom: 10px;
    }
  
    .timeline-content {
      padding: 15px; /* Reduced padding */
    }
  }
  @media (max-width: 480px) {
  .photo-gallery {
    grid-template-columns: 1fr; /* Only one column on very small screens */
    gap: 8px; /* Further reduce the gap between images */
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

  </style>
  