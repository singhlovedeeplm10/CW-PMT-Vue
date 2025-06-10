<template>
    <div class="d-flex justify-content-between task-card-container" style="width: 612px; height: 430px;">
        <!-- Task List Card -->
        <div class="task-card flex-fill shadow-sm position-relative" id="card2">
            <div class="task-card-header d-flex justify-content-between align-items-center">
                <h4 class="card_heading">Upcoming Birthdays</h4>
                <Calendar :selectedDate="tomorrowDate" @dateSelected="fetchBirthdays" />
            </div>

            <div class="birthday-card-body d-flex justify-content-center align-items-center">
                <div v-if="isLoading" class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>

                <div v-else-if="users.length" class="mt-3 w-100">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date of Birth</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td>
                                    <div class="birthday-profile">
                                        <img :src="user.user_image" alt="User Image" class="user-avatar" />
                                        <span class="user-name">{{ user.name }}</span>
                                    </div>
                                </td>
                                <td>{{ user.user_DOB }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="mt-3">
                    <p>No Member Exist.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Calendar from "@/components/forms/Calendar.vue";
import axios from "axios";
import { ref } from "vue";

export default {
    name: "UpcomingBirthdays",
    components: { Calendar },
    setup() {
        const users = ref([]);
        const isLoading = ref(false);
        const tomorrowDate = new Date();
        tomorrowDate.setDate(tomorrowDate.getDate() + 1);

        const fetchBirthdays = async (date) => {
            isLoading.value = true;
            try {
                const response = await axios.get("/api/upcoming-birthdays", {
                    params: { date },
                });
                users.value = response.data;
            } catch (error) {
                console.error("Error fetching birthdays:", error);
            } finally {
                isLoading.value = false;
            }
        };

        fetchBirthdays(tomorrowDate.toISOString().split("T")[0]);
        return { users, tomorrowDate, fetchBirthdays, isLoading };
    },
};
</script>

<style scoped>
.birthday-profile {
    display: flex;
    justify-content: center;
    align-items: center;
}

.user-avatar {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
    margin-right: 8px;
}

.user-name {
    font-size: 14px;
    white-space: nowrap;
}

.birthday-message {
    font-size: 14px;
    color: #888;
    margin-left: 50px;
    font-style: italic;
    display: block;
    max-width: 300px;
    line-height: 1.4;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.task-card {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-radius: 8px;
    padding: 10px;
    box-sizing: border-box;
    overflow: hidden;
    max-height: 100%;
}

.task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.birthday-card-body {
    overflow-y: auto;
    max-height: 300px;
    box-sizing: border-box;
    width: 100%;
}

.birthday-card-body::-webkit-scrollbar {
    width: 6px;
    background-color: #f1f1f1;
}

.birthday-card-body::-webkit-scrollbar-thumb {
    background-color: #c1c1c1;
    border-radius: 10px;
}

.birthday-card-body::-webkit-scrollbar-thumb:hover {
    background-color: #a1a1a1;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    padding: 10px 35px;
    font-size: 16px;
    font-weight: 600;
    background-color: #3498db;
    color: white;
    text-align: center;
    vertical-align: middle;
}

tbody td {
    padding: 10px;
    font-size: 15px;
    font-family: 'Poppins', sans-serif;
    border-bottom: 1px solid #f1f1f1;
    word-break: break-word;
    text-align: center;
    vertical-align: middle;
}

.profile {
    display: flex;
    align-items: center;
}

.profile img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 8px;
}

#card2 {
    border-radius: 8px;
    border: 1px solid #ccc;
}

.task-card-header h4 {
    font-family: 'Poppins', sans-serif;
}

.loader {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.user-name {
    font-size: 14px;
    white-space: nowrap;
}
</style>