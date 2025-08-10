<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click="$emit('close')">
    <div @click.stop class="relative w-full max-w-2xl p-6 mx-4 bg-white rounded-lg shadow-xl">
      <div class="flex items-start justify-between">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Board Members</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="mt-4">
        <!-- Invitation Form -->
        <div v-if="canInvite" class="p-4 mb-4 bg-gray-50 rounded-lg">
          <h4 class="text-md font-semibold mb-2">Invite New User</h4>
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
          </form>
        </div>

        <!-- Current Members -->
        <div>
          <h4 class="text-md font-semibold mb-2">Current Members</h4>
          <ul class="divide-y divide-gray-200">
            <li v-for="member in board.users" :key="member.id" class="py-3 flex justify-between items-center">
              <div class="flex items-center">
                <img class="h-8 w-8 rounded-full object-cover" :src="member.avatar_url || 'https://i.pravatar.cc/40?u=' + member.email" :alt="member.name">
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900">{{ member.name }}</p>
                  <p class="text-sm text-gray-500">{{ member.email }}</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <select v-model="member.pivot.role" @change="updateRole(member)" :disabled="!canManage(member)"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <option value="viewer">Viewer</option>
                  <option value="editor">Editor</option>
                  <option value="admin">Admin</option>
                </select>
                <button @click="removeMember(member)" v-if="canManage(member)" class="text-red-500 hover:text-red-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm-1 3a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="flex justify-end mt-6">
        <button @click="$emit('close')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  board: Object,
});

const emit = defineEmits(['close']);

const page = usePage();
const authUser = computed(() => page.props.auth.user);

const currentUserRole = computed(() => {
  const member = props.board.users.find(u => u.id === authUser.value.id);
  return member ? member.pivot.role : null;
});

const inviteForm = useForm({
  email: '',
  role: 'viewer',
});

const sendInvitation = () => {
  inviteForm.post(route('boards.invite', props.board.id), {
    preserveScroll: true,
    onSuccess: () => {
      inviteForm.reset();
    },
  });
};

const updateRole = (member) => {
  const form = useForm({ role: member.pivot.role });
  form.put(route('boards.members.update', { board: props.board.id, user: member.id }), {
    preserveScroll: true,
  });
};

const removeMember = (member) => {
  if (confirm('Are you sure you want to remove this member?')) {
    const form = useForm({});
    form.delete(route('boards.members.destroy', { board: props.board.id, user: member.id }), {
      preserveScroll: true,
    });
  }
};

const canInvite = computed(() => {
  return currentUserRole.value === 'admin' || currentUserRole.value === 'editor';
});

const canManage = (member) => {
  // Only admins can manage other members, and they can't remove themselves.
  return currentUserRole.value === 'admin' && member.id !== authUser.value.id;
};

</script>
