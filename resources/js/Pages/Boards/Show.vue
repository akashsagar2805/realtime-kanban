<script setup>
import AuthenticatedLayout from '~/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import Column from '~/Components/Kanban/Column.vue';
import MembersModal from '~/Components/MembersModal.vue';
import { ref, computed, watch } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    board: Object,
});

const columns = ref([...props.board.columns]);

watch(() => props.board.columns, (val) => {
    columns.value = Array.isArray(val) ? [...val] : [];
});

const updateColumnOrder = () => {
    const orderPayload = columns.value.map(col => col.id);

    router.patch(route('boards.columns.reorder', props.board.id), {
        order: orderPayload,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const page = usePage();
const authUser = computed(() => page.props.auth.user);

const currentUserRole = computed(() => {
    const member = props.board.users.find(u => u.id === authUser.value.id);
    return member ? member.pivot.role : null;
});

const showNewColumnForm = ref(false);
const showMembersModal = ref(false);

const form = useForm({
    name: '',
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
</script>

<template>

    <Head :title="board.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ board.name }}</h2>
                <div class="flex items-center space-x-4">
                    <Link v-if="currentUserRole === 'admin' || currentUserRole === 'editor'"
                        :href="route('boards.edit', board.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Edit Board
                    </Link>

                    <button @click="showMembersModal = true"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Members
                    </button>
                </div>
            </div>
        </template>

        <div class="p-8 flex flex-nowrap space-x-6">
            <draggable v-model="columns" item-key="id" group="columns" direction="horizontal"
                class="flex flex-nowrap space-x-6" ghost-class="ghost-column" chosen-class="dragging-column"
                handle=".handle" animation="200" @end="updateColumnOrder">
                <template #item="{ element }">
                    <div class="transition-column">
                        <Column :column="element" :role="currentUserRole" />
                    </div>
                </template>
            </draggable>



            <div v-if="currentUserRole !== 'viewer'" class="w-80 flex-shrink-0">
                <form v-if="showNewColumnForm" @submit.prevent="addColumn">
                    <input type="text" v-model="form.name" placeholder="Enter column name"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2" />
                    <div class="flex space-x-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add
                            Column</button>
                        <button type="button" @click="showNewColumnForm = false"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
                <button v-else @click="showNewColumnForm = true"
                    class="w-full h-16 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg shadow-sm">
                    + Add another list
                </button>
            </div>
        </div>

        <MembersModal :show="showMembersModal" :board="board" @close="showMembersModal = false" />

    </AuthenticatedLayout>
</template>