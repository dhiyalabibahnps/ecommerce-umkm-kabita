<script setup lang="ts">
import type { User } from '@/types/entities';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';

const props = defineProps<{
  users: Partial<User>[]
}>()

const emit = defineEmits<{
  (e: 'view', user: Partial<User>): void
  (e: 'edit', user: Partial<User>): void
  (e: 'ban', user: Partial<User>): void
}>()

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  }).format(date).replace('.', ':')
}
</script>

<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr
            class="border-b border-slate-100 text-slate-500 text-xs font-bold uppercase tracking-wider bg-slate-50/50">
            <th class="py-4 px-5 w-12">
              <Checkbox :binary="true" />
            </th>
            <th class="py-4 px-4">USER</th>
            <th class="py-4 px-4">ROLE</th>
            <th class="py-4 px-4">PHONE</th>
            <th class="py-4 px-4">STATUS</th>
            <th class="py-4 px-4">REGISTERED AT</th>
            <th class="py-4 px-5 text-right">ACTION</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-5">
              <Checkbox :binary="true" />
            </td>
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-3">
                <img :src="user.proof_image || `https://ui-avatars.com/api/?name=${user.name}&background=random`"
                  class="w-10 h-10 rounded-full object-cover border border-slate-200" />
                <div class="flex flex-col">
                  <span class="font-bold text-slate-800">{{ user.name }}</span>
                  <span class="text-xs text-slate-500">{{ user.email }}</span>
                </div>
              </div>
            </td>
            <td class="py-3.5 px-4">
              <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize"
                :class="user.role === 'buyer' ? 'bg-blue-50 text-blue-600' : (user.role === 'seller' ? 'bg-emerald-50 text-emerald-600' : 'bg-purple-50 text-purple-600')">
                {{ user.role }}
              </span>
            </td>
            <td class="py-3.5 px-4 text-slate-600 font-medium text-xs">{{ user.phone || '-' }}</td>
            <td class="py-3.5 px-4">
              <div
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[11px] font-bold capitalize"
                :class="{
                  'bg-emerald-50 border-emerald-100 text-emerald-700': user.status === 'active',
                  'bg-slate-100 border-slate-200 text-slate-600': user.status === 'inactive',
                }">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="user.status === 'active' ? 'bg-emerald-500' : (user.status === 'inactive' ? 'bg-slate-400' : 'bg-amber-500')"></span>
                {{ user.status === 'suspended' ? 'Pending' : user.status }}
              </div>
            </td>
            <td class="py-3.5 px-4 text-slate-500 text-xs">{{ formatDate(user.created_at) }}</td>
            <td class="py-3.5 px-5 text-right">
              <div class="flex items-center justify-end gap-1">
                <Button icon="pi pi-eye" text rounded severity="secondary"
                  class="w-8! h-8! p-0! text-slate-500! hover:text-blue-600!" @click="emit('view', user)" />
                <Button icon="pi pi-pencil" text rounded severity="secondary"
                  class="w-8! h-8! p-0! text-slate-500! hover:text-blue-600!" @click="emit('edit', user)" />
                <Button icon="pi pi-ban" text rounded severity="danger"
                  class="w-8! h-8! p-0! text-slate-500! hover:text-red-500!" @click="emit('ban', user)" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div
        class="flex items-center justify-between p-4 border-t border-slate-100 text-sm text-slate-500 bg-slate-50/30">
        <span>Menampilkan 1-{{ users.length }} dari 1,247 user</span>
        <div class="flex items-center gap-1">
          <Button label="< Previous" text size="small" class="text-slate-500! font-semibold! px-3!" />
          <Button label="1" class="bg-blue-600! border-blue-600! text-white! w-8! h-8! p-0! rounded-full! font-bold!" />
          <Button label="2" text class="text-slate-600! w-8! h-8! p-0! rounded-full! font-semibold!" />
          <Button label="3" text class="text-slate-600! w-8! h-8! p-0! rounded-full! font-semibold!" />
          <span class="px-1 text-slate-400">...</span>
          <Button label="125" text class="text-slate-600! w-8! h-8! p-0! rounded-full! font-semibold!" />
          <Button label="Next >" text size="small" class="text-slate-500! font-semibold! px-3!" />
        </div>
      </div>
    </div>
  </div>
</template>