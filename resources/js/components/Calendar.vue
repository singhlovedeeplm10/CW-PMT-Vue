<template>
    <div>
      <h1>Select a Date</h1>
      <Calendar :selectedDate="date" @dateSelected="onDateSelected" />
      <p>Selected Date: {{ date.toDateString() }}</p>
    </div>
  </template>
  
  <script>
  import Calendar from "@/components/Calendar.vue";
  import { ref } from "vue";
  
  export default {
    components: { Calendar },
    setup() {
      const date = ref(new Date());
  
      const onDateSelected = (selectedDate) => {
        date.value = selectedDate;
      };
  
      return {
        date,
        onDateSelected,
      };
    },
  };
  </script>
  