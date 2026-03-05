<template>
  <AdminLayout>
    <Head title="施設管理" />
    <template #header>施設管理</template>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- 検索・フィルター -->
      <div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="w-full sm:w-96 relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input type="text" v-model="searchQuery" @keyup.enter="handleSearch" placeholder="施設名、オーナー名、メールで検索..." 
                 class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
        </div>
        <Link :href="route('admin.facilities.create')" 
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 transition">
          新規施設を登録
        </Link>
      </div>

      <!-- 施設一覧テーブル -->
      <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-2xl overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">施設情報</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">オーナー</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900 text-center">部屋数</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Stripe status</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">公開状態</th>
              <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                <span class="sr-only">操作</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-for="facility in facilities.data" :key="facility.uuid" class="hover:bg-slate-50 transition">
              <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                <div class="flex flex-col">
                  <div class="font-bold text-slate-900">{{ facility.name }}</div>
                  <div class="text-xs text-slate-500 font-mono">{{ facility.uuid }}</div>
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                <div class="flex flex-col">
                  <div class="text-slate-900 font-medium">{{ facility.owner_name }}</div>
                  <div class="text-xs">{{ facility.owner_email }}</div>
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 text-center">
                {{ facility.rooms_count }}
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm">
                <span v-if="facility.stripe_status === 'active'" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                <span v-else-if="facility.stripe_status === 'pending'" class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Pending</span>
                <span v-else class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">{{ facility.stripe_status || 'N/A' }}</span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm">
                <span v-if="facility.is_published" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">公開中</span>
                <span v-else class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">非公開</span>
              </td>
              <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                <Link :href="route('admin.facilities.show', facility.uuid)" class="text-primary-600 hover:text-primary-900">
                  詳細 <span class="sr-only">, {{ facility.name }}</span>
                </Link>
              </td>
            </tr>
            <tr v-if="facilities.data.length === 0">
              <td colspan="6" class="py-12 text-center text-slate-500">施設が見つかりません</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div v-if="facilities.links.length > 3" class="mt-6 flex justify-center">
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <template v-for="(link, i) in facilities.links" :key="i">
            <Link
              v-if="link.url"
              :href="link.url"
              v-html="link.label"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-slate-300 focus:z-20 focus:outline-offset-0"
              :class="[
                link.active ? 'z-10 bg-primary-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600' : 'text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50',
              ]"
            />
            <span
              v-else
              v-html="link.label"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-slate-200 text-slate-300 cursor-not-allowed bg-slate-50"
            />
          </template>
        </nav>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    facilities: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');

const handleSearch = () => {
    router.get(route('admin.facilities.index'), {
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};
</script>
