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
                <h3 class="timeline-title">{{ event.title }}</h3>
                <p class="timeline-description">{{ event.description }}</p>
                <template v-if="event.type === 'email'">
                  <div class="timeline-actions">
                    <button class="btn btn-primary">Read more</button>
                    <button class="btn btn-danger">Delete</button>
                  </div>
                </template>
                <template v-else-if="event.type === 'photos'">
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
                  <div class="video-wrapper" @click="playVideo(event.videoUrl)">
                    <iframe
                      v-if="event.videoUrl"
                      :src="event.videoUrl"
                      frameborder="0"
                      allowfullscreen
                    ></iframe>
                    <div v-else>
                      <a :href="event.videoUrl" target="_blank">Watch Video</a>
                    </div>
                  </div>
                </template>
                <!-- Like and Comment Icons -->
                <div class="post-actions">
                  <i class="fas fa-thumbs-up action-icon" @click="likePost(index)"></i>
                  <i class="fas fa-comment action-icon" @click="commentOnPost(index)"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Image Viewer Modal -->
      <div v-if="selectedImage" class="image-viewer" @click="closeImage">
        <img :src="selectedImage" class="enlarged-image" />
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
        events: [],  // Array to store the fetched events
        selectedImage: null,
      };
    },
    created() {
      this.fetchTimelineData();
    },
    methods: {
      async fetchTimelineData() {
        try {
          const response = await axios.get('/api/timelines', {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,  // Make sure to add token here for authentication
            },
          });
  
          // Process the response to match the structure you want for your timeline events
          this.events = response.data.map(timeline => ({
            date: timeline.created_at,  // Use the creation date or another date field
            type: 'photos',  // You can check the type based on the file type
            title: `${timeline.user ? timeline.user.name : 'Unknown User'} shared a new post`,  // Check if user exists
            description: timeline.description,
            photos: timeline.timeline_uploads.filter(upload => upload.file_type === 'image').map(upload => `/storage/${upload.file_path}`),
            videoUrl: timeline.timeline_uploads.find(upload => upload.file_type === 'video') ? `/storage/${timeline.timeline_uploads.find(upload => upload.file_type === 'video').file_path}` : null
          }));
        } catch (error) {
          console.error("There was an error fetching the timeline data:", error);
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
  
      likePost(index) {
        alert(`Liked post #${index + 1}`);
      },
  
      commentOnPost(index) {
        alert(`Comment on post #${index + 1}`);
      },
    },
  };
  </script>  

  <style scoped>
  /* Like and Comment Icons */
  .post-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px; /* Reduced margin */
  }
  
  .action-icon {
    font-size: 20px; /* Smaller icon size */
    color: #7f8c8d;
    cursor: pointer;
    transition: color 0.3s, transform 0.3s;
  }
  
  .action-icon:hover {
    color: #3498db;
    transform: scale(1.2);
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
  }
  
  .timeline-title {
    font-size: 1.3rem; /* Reduced title font size */
    font-weight: 700;
    color: #34495e;
    margin-bottom: 10px; /* Reduced margin */
  }
  
  .timeline-description {
    font-size: 0.9rem; /* Reduced description font size */
    color: #7f8c8d;
    margin-bottom: 15px; /* Reduced margin */
    line-height: 1.4;
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
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); /* Reduced grid size */
    gap: 10px; /* Reduced gap */
  }
  
  .photo-gallery img {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    object-fit: cover;
    transition: transform 0.3s ease;
  }
  
  .photo-gallery img:hover {
    transform: scale(1.05);
  }
  
  /* Video Wrapper */
  .video-wrapper {
    position: relative;
    padding-top: 56.25%;
    background-color: #ecf0f1;
    border-radius: 8px;
    cursor: pointer;
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
  </style>
  