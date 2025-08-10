<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    card: Object,
    role: String
});

const showEditCardForm = ref(false);

const form = useForm({
    title: props.card.title,
});

const updateCard = () => {
    form.put(route('cards.update', props.card.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditCardForm.value = false;
        },
    });
};

const deleteCard = () => {
    if (confirm('Are you sure you want to delete this card?')) {
        form.delete(route('cards.destroy', props.card.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="bg-white mt-4 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer">
        <div v-if="!showEditCardForm">
            <h3 class="font-semibold text-gray-800 text-sm">{{ card.title }}</h3>
            <p v-if="card.description" class="text-xs text-gray-500 mt-1">{{ card.description }}</p>
            <div class="mt-3 flex justify-between items-center">
                <span v-if="card.tag" :class="card.tag.class" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ card.tag.label }}
                </span>
                <img v-if="card.assignee" class="h-6 w-6 rounded-full object-cover" :src="card.assignee.avatar" :alt="card.assignee.name">
            </div>
            <div v-if="role !== 'viewer'" class="flex justify-end space-x-2 mt-2">
                <button @click="showEditCardForm = true" class="text-gray-500 hover:text-gray-700 text-sm">Edit</button>
                <button @click="deleteCard" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
            </div>
        </div>
        <form v-else @submit.prevent="updateCard">
            <input
                type="text"
                v-model="form.title"
                class="w-full p-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
            />
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">Update</button>
                <button type="button" @click="showEditCardForm = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 text-sm">Cancel</button>
            </div>
        </form>
    </div>
</template>