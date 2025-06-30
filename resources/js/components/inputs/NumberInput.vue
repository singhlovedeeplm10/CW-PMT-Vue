<template>
    <div class="mb-3">
        <label v-if="label" :for="id" class="form-label">{{ label }}</label>
        <input type="number" :id="id" :name="name" :placeholder="placeholder" :value="modelValue" :min="min" :max="max"
            :step="step" :disabled="disabled" class="form-control" :class="{ 'is-invalid': hasError }"
            @input="$emit('update:modelValue', $event.target.value)" @blur="$emit('input-blur', modelValue)" />
        <div v-if="hasError" class="invalid-feedback">{{ errorMessage }}</div>
    </div>
</template>

<script>
export default {
    name: "NumberInput",
    props: {
        modelValue: {
            type: [Number, String],
            default: "",
        },
        label: {
            type: String,
            default: "",
        },
        id: {
            type: String,
            default: null,
        },
        name: {
            type: String,
            default: null,
        },
        placeholder: {
            type: String,
            default: "",
        },
        min: {
            type: [Number, String],
            default: null,
        },
        max: {
            type: [Number, String],
            default: null,
        },
        step: {
            type: [Number, String],
            default: "1",
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        hasError: {
            type: Boolean,
            default: false,
        },
        errorMessage: {
            type: String,
            default: "",
        },
    },
    emits: ["update:modelValue", "input-blur"],
};
</script>

<style scoped>
.form-label {
    font-weight: 500;
    font-size: 18px;
}
</style>
