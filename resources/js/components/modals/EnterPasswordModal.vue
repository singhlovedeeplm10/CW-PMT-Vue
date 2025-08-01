<template>
    <div class="modal-overlay" v-if="visible" @keydown.enter="handleEnterKey">
        <div class="modal-content">
            <button class="close-btn" @click="goBack">×</button>
            <h3>Enter Profile Password</h3>

            <PasswordInput v-model="password" id="profile-password" :label="''"
                placeholder="Enter your profile password" @input-blur="clearError" />

            <button @click="verifyPassword" class="btn-primary" :disabled="loading" ref="submitButton">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                {{ loading ? "Verifying..." : "Submit" }}
            </button>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { toast } from "vue3-toastify";
import { setCookie } from "@/utils/cookie";
import PasswordInput from "@/components/inputs/PasswordInput.vue";

export default {
    name: "EnterPasswordModal",
    components: {
        PasswordInput,
    },
    props: ["visible"],
    data() {
        return {
            password: "",
            loading: false,
        };
    },
    methods: {
        goBack() {
            this.$router.go(-1);
        },
        clearError() {
            // Placeholder for blur error handling
        },
        handleEnterKey(e) {
            // Prevent default form submission behavior
            e.preventDefault();
            if (!this.loading) {
                this.verifyPassword();
            }
        },
        async verifyPassword() {
            if (!this.password) {
                toast.error("Please enter your password.", {
                    autoClose: 1000,
                    position: "top-right",
                });
                return;
            }

            this.loading = true;

            try {
                const response = await axios.post(
                    "/api/verify-profile-password",
                    { password: this.password },
                    {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
                        },
                    }
                );

                if (response.data.success) {
                    toast.success("Password verified.", {
                        autoClose: 1000,
                        position: "top-right",
                    });

                    setCookie("profile_verified", "true", 15);
                    this.$emit("close");
                } else {
                    toast.error(response.data.message || "Incorrect password", {
                        autoClose: 1000,
                        position: "top-right",
                    });
                }
            } catch (error) {
                const msg =
                    error.response?.data?.message ||
                    error.response?.data?.error ||
                    "Failed to verify password.";
                toast.error(msg, {
                    autoClose: 1000,
                    position: "top-right",
                });
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
    },
    mounted() {
        // Focus on the password input when modal appears
        if (this.visible) {
            this.$nextTick(() => {
                const input = document.getElementById('profile-password');
                if (input) input.focus();
            });
        }
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.$nextTick(() => {
                    const input = document.getElementById('profile-password');
                    if (input) input.focus();
                });
            }
        }
    }
};
</script>

<style scoped>
.close-btn {
    position: absolute;
    top: -8px;
    right: -9px;
    background-color: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 30px;
    height: 31px;
    font-size: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
}

.close-btn:hover {
    cursor: pointer;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.55);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-content {
    background: #fff;
    border-radius: 8px;
    padding: 30px 40px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    text-align: center;
}

.modal-content h3 {
    font-size: 22px;
    color: #333;
    font-weight: 600;
}

.modal-input {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 16px;
    box-sizing: border-box;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    font-weight: bold;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #003d82);
}
</style>
