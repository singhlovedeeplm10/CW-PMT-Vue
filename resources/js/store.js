import { createStore } from "vuex";
import axios from "axios";

const store = createStore({
  state() {
    return {
      userName: "Guest",
      userRole: null,
      userImage: null,
      userDesignation: null,
    };
  },
  mutations: {
    setUserDetails(state, { name, image, designation }) {
      state.userName = name;
      state.userImage = image;
      state.userDesignation = designation;
    },
    setUserRole(state, role) {
      state.userRole = role;
    },
  },
  actions: {
    async fetchUserDetails({ commit }) {
      try {
        const response = await axios.get("/api/user-details", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        commit("setUserDetails", {
          name: response.data.user_name, // Correctly mapped from API response
          image: response.data.user_image, // Correctly mapped from API response
          designation: response.data.user_designation, // Correctly mapped from API response
        });
      } catch (error) {
        console.error("Error fetching user details:", error);
      }
    },
    async fetchUserRole({ commit }) {
      try {
        const response = await axios.get("/api/user-role", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });
        commit("setUserRole", response.data.role);
      } catch (error) {
        console.error("Error fetching user role:", error);
      }
    },
  },
  getters: {
    getUserDetails(state) {
      return {
        name: state.userName,
        image: state.userImage,
        designation: state.userDesignation,
      };
    },
    getUserRole(state) {
      return state.userRole;
    },
  },
});

export default store;
