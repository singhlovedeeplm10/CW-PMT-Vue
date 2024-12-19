<template>
    <master-component>
      <div class="upload-timeline-container">
        <h1 class="title">Upload Timeline</h1>
        <form class="timeline-form" @submit.prevent="submitTimeline">
          <div class="form-group">
            <label for="title">Title</label>
            <input
              type="text"
              id="title"
              v-model="form.title"
              placeholder="Enter the title"
              required
            />
          </div>
  
          <div class="form-group">
            <label for="description">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              placeholder="Write your description"
              class="summernote"
            ></textarea>
          </div>
  
          <div class="form-group">
            <label for="uploadMedia">Upload Image/Video</label>
            <input type="file" id="uploadMedia" @change="handleFileUpload" />
          </div>
  
          <div class="form-group">
            <label for="uploadLink">Upload Link for Image/Video</label>
            <input
              type="url"
              id="uploadLink"
              v-model="form.uploadLink"
              placeholder="Enter the URL"
            />
          </div>
  
          <button class="submit-btn" type="submit">Upload</button>
        </form>
      </div>
    </master-component>
  </template>
  
  <script>
  import MasterComponent from "./layouts/Master.vue";
  import { toast } from "vue3-toastify";
  
  export default {
    name: "UploadTimeline",
    components: { MasterComponent },
    data() {
      return {
        form: {
          title: "",
          description: "",
          uploadMedia: null,
          uploadLink: "",
        },
      };
    },
    methods: {
      handleFileUpload(event) {
        this.form.uploadMedia = event.target.files;
      },
      async submitTimeline() {
        const formData = new FormData();
        formData.append('title', this.form.title);
        formData.append('description', this.form.description);
  
        // Add files to the form data
        if (this.form.uploadMedia) {
          for (let i = 0; i < this.form.uploadMedia.length; i++) {
            formData.append('uploadMedia[]', this.form.uploadMedia[i]);
          }
        }
  
        // Add the external link (if provided)
        if (this.form.uploadLink) {
          formData.append('uploadLink', this.form.uploadLink);
        }
  
        try {
          const response = await axios.post('/api/upload-timeline', formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          });
  
          // Show success toast
          toast.success("Timeline uploaded successfully!");

  
        } catch (error) {
          // Show error toast
          toast.error("Failed to upload timeline.");
          console.error(error.response.data);
        }
      },
    },
  };
  </script>
  
  
  <style scoped>
  .upload-timeline-container {
    max-width: 600px;
    margin: 2rem auto;
    background: #f9f9f9;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
  }
  
  .title {
    font-size: 24px;
    text-align: center;
    color: #4a90e2;
    margin-bottom: 1rem;
  }
  
  .timeline-form {
    display: flex;
    flex-direction: column;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  label {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin-bottom: 0.5rem;
  }
  
  input,
  textarea {
    width: 100%;
    padding: 0.5rem;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s;
  }
  
  input:focus,
  textarea:focus {
    outline: none;
    border-color: #4a90e2;
  }
  
  textarea {
    min-height: 100px;
    resize: none;
  }
  
  .summernote {
    background: white;
  }
  
  .submit-btn {
    padding: 0.8rem 1.5rem;
    font-size: 16px;
    color: white;
    background: #4a90e2;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
  }
  
  .submit-btn:hover {
    background: #357ab7;
    transform: scale(1.05);
  }
  
  .submit-btn:active {
    transform: scale(1);
  }
  </style>
  