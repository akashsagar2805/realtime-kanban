<script setup>
import Card from './Card.vue';
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    column: Object
});

const showNewCardForm = ref(false);
const showEditColumnForm = ref(false);

const form = useForm({
    title: '',
    column_id: props.column.id,
});

const editColumnForm = useForm({
    name: props.column.name,
});

const addCard = () => {
    form.post(route('cards.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showNewCardForm.value = false;
        },
    });
};

const updateColumn = () => {
    editColumnForm.put(route('boards.columns.update', { board: props.column.board_id, column: props.column.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showEditColumnForm.value = false;
        },
    });
};

const deleteColumn = () => {
    if (confirm('Are you sure you want to delete this column and all its cards?')) {
        editColumnForm.delete(route('boards.columns.destroy', { board: props.column.board_id, column: props.column.id }), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="bg-gray-100 rounded-lg shadow-sm w-80 flex-shrink-0 flex flex-col">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 v-if="!showEditColumnForm" class="text-lg font-bold text-gray-700">{{ column.name }}</h2>
            <form v-else @submit.prevent="updateColumn" class="flex-grow">
                <input
                    type="text"
                    v-model="editColumnForm.name"
                    class="w-full p-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </form>
            <div class="flex space-x-2 ml-2">
                <button v-if="!showEditColumnForm" @click="showEditColumnForm = true" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.38-2.828-2.828z" />
                    </svg>
                </button>
                <button v-else @click="updateColumn" class="text-green-500 hover:text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
                <button @click="deleteColumn" class="text-red-500 hover:text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm-1 3a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-4">
            <div class="space-y-4 overflow-y-auto max-h-96 scrollbar-hide">
                <Card v-for="card in column.cards" :key="card.id" :card="card" />
            </div>
        </div>
        <div class="p-4">
            <form v-if="showNewCardForm" @submit.prevent="addCard">
                <input
                    type="text"
                    v-model="form.title"
                    placeholder="Enter card title"
                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
                />
                <div class="flex space-x-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Card</button>
                    <button type="button" @click="showNewCardForm = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
                </div>
            </form>
            <button v-else @click="showNewCardForm = true" class="w-full flex items-center justify-center text-sm font-medium text-gray-600 hover:text-gray-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add a card
            </button>
        </div>
    </div>
</template>