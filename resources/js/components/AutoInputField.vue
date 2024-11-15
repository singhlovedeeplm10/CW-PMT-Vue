<template>
    <div>
        <input 
            type="text" 
            :class="['form-control', inputClass]" 
            v-model="query" 
            @input="onInput" 
            @blur="onBlur" 
            :placeholder="placeholder" 
        />
        <ul v-if="showSuggestions && suggestions.length" class="autocomplete-suggestions">
            <li 
                v-for="(suggestion, index) in suggestions" 
                :key="index" 
                @click="selectSuggestion(suggestion)" 
                class="suggestion-item"
            >
                {{ suggestion }}
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: "AutoInputField",
    props: {
        inputClass: {
            type: String,
            default: ""
        },
        placeholder: {
            type: String,
            default: "Search..."
        },
        suggestionsList: {
            type: Array,
            required: true
        },
        modelValue: {
            type: String,
            default: ""
        }
    },
    data() {
        return {
            query: this.modelValue,
            suggestions: [],
            showSuggestions: false
        };
    },
    watch: {
        modelValue(newValue) {
            this.query = newValue;
        }
    },
    methods: {
        onInput() {
            this.suggestions = this.suggestionsList.filter(item => 
                item.toLowerCase().includes(this.query.toLowerCase())
            );
            this.showSuggestions = this.suggestions.length > 0;
            this.$emit('update:modelValue', this.query);
        },
        selectSuggestion(suggestion) {
            this.query = suggestion;
            this.showSuggestions = false;
            this.$emit('update:modelValue', this.query);
        },
        onBlur() {
            // Delay hiding to allow click event on suggestions
            setTimeout(() => this.showSuggestions = false, 150);
        }
    }
};
</script>

<style>
.autocomplete-suggestions {
    list-style: none;
    margin: 0;
    padding: 0;
    border: 1px solid #ccc;
    max-height: 200px;
    overflow-y: auto;
    background-color: white;
    position: absolute;
    z-index: 1000;
    width: 100%;
}

.suggestion-item {
    padding: 10px;
    cursor: pointer;
}

.suggestion-item:hover {
    background-color: #f0f0f0;
}
</style>
