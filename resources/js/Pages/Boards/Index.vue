<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const { props } = usePage();
const boards = props.boards;
</script>

<template>
    <Head title="My Boards" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Boards</h2>
                <Link href="/boards/create"
                      class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    + New Board
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="boards.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="board in boards" :key="board.id"
                         class="bg-white overflow-hidden shadow-sm sm:rounded-lg transform transition-transform hover:scale-105">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">{{ board.name }}</h3>
                            <p class="mt-2 text-sm text-gray-500">Created at {{ new Date(board.created_at).toLocaleDateString() }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 flex justify-end">
                            <Link :href="`/boards/${board.id}`"
                                  class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Open Board</Link>
                        </div>
                    </div>
                </div>
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p>No boards yet. Create one to get started!</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>