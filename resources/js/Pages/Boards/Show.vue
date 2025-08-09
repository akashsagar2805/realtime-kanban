<script setup>
import AuthenticatedLayout from '~/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Column from '~/Components/Kanban/Column.vue';
import { ref } from 'vue';

const props = defineProps({
    board: Object
});

const showNewColumnForm = ref(false);
const showInviteForm = ref(false);

const form = useForm({
    name: '',
});

const inviteForm = useForm({
    email: '',
    role: 'viewer', // Default role
});

const addColumn = () => {
    form.post(route('boards.columns.store', props.board.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showNewColumnForm.value = false;
        },
    });
};

const sendInvitation = () => {
    inviteForm.post(route('boards.invite', props.board.id), {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            showInviteForm.value = false;
        },
        onError: () => {
            // Handle errors, e.g., display them
        }
    });
};
</script>

<template>
    <Head :title="board.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ board.name }}</h2>
                <div class="flex items-center space-x-4">
                    <Link :href="route('boards.edit', board.id)"
                          class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Edit Board
                    </Link>

                    <button @click="showInviteForm = !showInviteForm"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Invite User
                    </button>
                </div>
            </div>

            <div v-if="showInviteForm" class="mt-4 p-4 bg-gray-100 rounded-lg">
                <h3 class="text-lg font-semibold mb-2">Invite New User</h3>
                <form @submit.prevent="sendInvitation" class="flex items-end space-x-2">
                    <div class="flex-grow">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" v-model="inviteForm.email"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                               placeholder="user@example.com" required>
                        <div v-if="inviteForm.errors.email" class="text-red-500 text-sm mt-1">{{ inviteForm.errors.email }}</div>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="role" v-model="inviteForm.role"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="viewer">Viewer</option>
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                        Send Invitation
                    </button>
                    <button type="button" @click="showInviteForm = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                </form>
            </div>
        </template>

        <div class="p-8 flex flex-nowrap space-x-6">
            <Column v-for="column in board.columns" :key="column.id" :column="column" />

            <div class="w-80 flex-shrink-0">
                <form v-if="showNewColumnForm" @submit.prevent="addColumn">
                    <input
                        type="text"
                        v-model="form.name"
                        placeholder="Enter column name"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
                    />
                    <div class="flex space-x-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Column</button>
                        <button type="button" @click="showNewColumnForm = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
                <button v-else @click="showNewColumnForm = true" class="w-full h-16 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg shadow-sm">
                    + Add another list
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>