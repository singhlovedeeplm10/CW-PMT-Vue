<template>
  <div class="modal fade" id="addbreakmodal" tabindex="-1" aria-labelledby="addbreakmodallabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content add-break-modal">
        <div class="modal-header">
          <h5 class="modal-title" id="addbreakmodallabel">Add Break</h5>
          <button type="button" class="close-modal" data-bs-dismiss="modal" aria-label="Close"
            @click="resetModal">&times;</button>
        </div>
        <div class="modal-body" @keyup.enter="handleEnterKey">
          <div class="form-group mb-4" v-if="!isOnBreak">
            <InputField label="Break Reason" inputType="text" placeholder="Enter your break reason" :isRequired="true"
              inputClass="input-break-reason" v-model="breakReason" ref="breakReasonInput" />
          </div>
          <ButtonComponent v-if="!isLoading" label="Take Break " iconClass="fas fa-clock"
            buttonClass="btn-success w-100" @click="handleBreakStart" ref="breakButton" />
          <div v-else class="text-center">
            <span class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import InputField from "@/components/inputs/InputField.vue";
import ButtonComponent from "@/components/forms/ButtonComponent.vue";
import { ref, onMounted, onUnmounted } from "vue";
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
    const isLoading = ref(false);
    const breakReasonInput = ref(null);
    const breakButton = ref(null);

    const resetModal = () => {
      breakReason.value = "";
    };

    const handleEnterKey = (event) => {
      if (event.key === 'Enter' && !isLoading.value && breakReason.value.trim()) {
        handleBreakStart();
      }
    };

    // Add keyboard event listener when modal is shown
    const setupKeyboardListener = () => {
      const modal = document.getElementById('addbreakmodal');
      if (modal) {
        modal.addEventListener('keyup', handleEnterKey);
      }
    };

    // Remove keyboard event listener when modal is hidden
    const removeKeyboardListener = () => {
      const modal = document.getElementById('addbreakmodal');
      if (modal) {
        modal.removeEventListener('keyup', handleEnterKey);
      }
    };

    onMounted(() => {
      const modal = document.getElementById('addbreakmodal');
      modal.addEventListener('shown.bs.modal', () => {
        if (breakReasonInput.value && breakReasonInput.value.$el) {
          breakReasonInput.value.$el.querySelector('input').focus();
        }
      });
      setupKeyboardListener();
    });

    onUnmounted(() => {
      removeKeyboardListener();
    });

    const handleBreakStart = async () => {
      if (!breakReason.value.trim()) {
        toast.error("Break reason is required.", { position: "top-right" });
        return;
      }

      isLoading.value = true;

      try {
        const response = await axios.post(
          "/api/start-break",
          { reason: breakReason.value },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            },
          }
        );

        if (response.status === 201) {
          resetModal();
          const modal = bootstrap.Modal.getInstance(
            document.getElementById("addbreakmodal")
          );
          modal.hide();

          toast.success("Break started successfully.", {
            position: "top-right",
            autoClose: 1000,
          });

          emit("breakStarted", {
            reason: breakReason.value,
            startTime: Date.now(),
          });

          localStorage.setItem("isOnBreak", "true");
          localStorage.setItem("breakStartTime", Date.now());
          localStorage.setItem("totalBreakTime", 0);
        }
      } catch (error) {
        toast.error("Failed to start break. Please try again.", {
          position: "top-right",
          autoClose: 1000,
        });
      } finally {
        isLoading.value = false;
      }
    };

    return {
      breakReason,
      resetModal,
      handleBreakStart,
      isLoading,
      breakReasonInput,
      breakButton,
      handleEnterKey,
    };
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
}

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