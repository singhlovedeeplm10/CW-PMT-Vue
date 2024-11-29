<template>
    <div
      class="modal fade"
      id="addbreakmodal"
      tabindex="-1"
      aria-labelledby="addbreakmodallabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content add-break-modal">
          <div class="modal-header">
            <h5 class="modal-title" id="addbreakmodallabel">Add Break</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <div class="form-group mb-4" v-if="!isOnBreak">
              <InputField
                label="Break Reason"
                inputType="text"
                placeholder="Enter your break reason"
                :isRequired="true"
                inputClass="input-break-reason"
                v-model="breakReason"
              />
            </div>
            <ButtonComponent
              label="Take Break"
              iconClass="fas fa-clock"
              buttonClass="btn-success w-100"
              @click="startBreak"
            />
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import InputField from "@/components/InputField.vue";
  import ButtonComponent from "@/components/ButtonComponent.vue";
  import { ref } from "vue";
  import { toast } from "vue3-toastify";
  import "vue3-toastify/dist/index.css";
  import axios from "axios";
  import * as bootstrap from "bootstrap";
  
  export default {
    name: "AddBreakModal",
    components: {
      InputField,
      ButtonComponent,
    },
    props: {
      isOnBreak: Boolean,
    },
    emits: ["breakStarted"],
    setup(_, { emit }) {
    const breakReason = ref("");

    const startBreak = async () => {
      if (!breakReason.value.trim()) {
        toast.error("Break reason is required.", { position: "top-right" });
        return;
      }

      try {
        const response = await axios.post(
          "/api/start-break",
          {
            reason: breakReason.value,
          },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          }
        );

        if (response.status === 201) {
          const modal = bootstrap.Modal.getInstance(
            document.getElementById("addbreakmodal")
          );
          modal.hide();
          toast.success("Break started successfully.", {
            position: "top-right",
          });

          emit("breakStarted", { reason: breakReason.value, startTime: Date.now() });
        }
      } catch (error) {
        toast.error("Failed to start break. Please try again.", {
          position: "top-right",
        });
      }
    };

    return {
      breakReason,
      startBreak,
    };
  },
};
</script>
  
  <style scoped>
  .add-break-modal {
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    background: #f9f9f9;
    overflow: hidden;
  }
  
  .modal-header {
    background: #007bff;
    color: #fff;
    border-bottom: 1px solid #007bff;
  }
  
  .modal-header .btn-close {
    color: #fff;
    opacity: 1;
  }
  
  .modal-body {
    padding: 20px;
    background: #ffffff;
  }
  
  .input-break-reason {
    border: 1px solid #ced4da;
    padding: 10px;
    border-radius: 4px;
  }
  
  .btn-success {
    background: #28a745;
    border: none;
    font-size: 16px;
    padding: 10px 15px;
    transition: background-color 0.3s ease;
  }
  
  .btn-success:hover {
    background: #218838;
  }
  </style>
  