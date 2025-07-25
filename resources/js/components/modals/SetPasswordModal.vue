<template>
    <div class="modal-overlay" v-if="visible" @keydown.enter="handleEnterKey">
        <div class="modal-content">
            <button class="close-btn" @click="goBack">×</button>
            <h3>Set Profile Password</h3>

            <PasswordInput v-model="password" id="new-password" :label="''" placeholder="Enter new password"
                @input-blur="clearError('password')" style="margin-top: -20px;" ref="firstInput" />

            <PasswordInput v-model="password_confirmation" id="confirm-password" :label="''"
                placeholder="Confirm password" @input-blur="clearError('password_confirmation')"
                style="margin-top: -20px;" />

            <button @click="setPassword" class="btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                {{ loading ? "Setting..." : "Set Password" }}
            </button>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { toast } from "vue3-toastify";
import { setCookie, getCookie, deleteCookie } from "@/utils/cookie";
import PasswordInput from "@/components/inputs/PasswordInput.vue";

export default {
    name: "SetPasswordModal",
    components: {
        PasswordInput,
    },
    props: ["visible"],
    data() {
        return {
            password: "",
            password_confirmation: "",
            loading: false,
        };
    },
    methods: {
        goBack() {
            this.$router.go(-1);
        },
        clearError(field) {
            // Optional: placeholder for blur validation
        },
        handleEnterKey(e) {
            // Prevent default form submission behavior
            e.preventDefault();
            if (!this.loading) {
                this.setPassword();
            }
        },
        async setPassword() {
            if (!this.password || !this.password_confirmation) {
                toast.error("Please fill both fields.", {
                    autoClose: 1000,
                    position: "top-right",
                });
                return;
            }

            if (this.password !== this.password_confirmation) {
                toast.error("Passwords do not match.", {
                    autoClose: 1000,
                    position: "top-right",
                });
                return;
            }

            if (this.password.length < 8) {
                toast.error("Password must be at least 8 characters.", {
                    autoClose: 1000,
                    position: "top-right",
                });
                return;
            }

            this.loading = true;

            try {
                await axios.post(
                    "/api/set-profile-password",
                    {
                        password: this.password,
                        password_confirmation: this.password_confirmation,
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
                        },
                    }
                );

                toast.success("Profile password set successfully.", {
                    autoClose: 1000,
                    position: "top-right",
                });

                setCookie("profile_verified", "true", 15);
                this.$emit("close");
            } catch (error) {
                const messages = error.response?.data?.errors;
                if (messages) {
                    const allErrors = Object.values(messages).flat();
                    allErrors.forEach((msg) => {
                        toast.error(msg, {
                            autoClose: 1500,
                            position: "top-right",
                        });
                    });
                } else {
                    toast.error("Failed to set password.", {
                        autoClose: 1000,
                        position: "top-right",
                    });
                }
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
    },
    mounted() {
        // Focus on the first input when modal appears
        if (this.visible && this.$refs.firstInput) {
            this.$refs.firstInput.focus();
        }
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.$nextTick(() => {
                    if (this.$refs.firstInput) {
                        this.$refs.firstInput.focus();
                    }
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
    margin-bottom: 15px;
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
