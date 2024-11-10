<template>
  <master-component>
  <div v-if="show" class="modal-overlay">
    <div class="modal-content">
      <h2>Add Break</h2>
      <input type="text" v-model="breakReason" placeholder="Enter break reason" class="form-control" />
      <button class="btn btn-primary mt-3" @click="takeBreak">Take Break</button>
      <button class="btn btn-secondary mt-3" @click="closeModal">Close</button>
    </div>
  </div>
</master-component>
</template>

<script>
import MasterComponent from './layouts/Master.vue';

export default {
  name: "AddBreak",
  components: {
      MasterComponent,
    },
  props: {
    show: Boolean,
  },
  data() {
    return {
      breakReason: "",
    };
  },
  methods: {
    takeBreak() {
      this.$emit("take-break", this.breakReason);
      this.closeModal();
    },
    closeModal() {
      this.$emit("close");
    },
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background-color: #fff;
  padding: 20px;
  border-radius: 5px;
  width: 300px;
  text-align: center;
}
</style>
