<template>
  <AdminLayout>
    <Head title="オーナー管理" />
    <template #header>オーナー管理</template>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="w-full sm:w-96 relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input type="text" v-model="search" @keyup.enter="handleSearch" placeholder="名前、メールアドレスで検索..." 
                 class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
        </div>
        <Link :href="route('admin.users.create')" 
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 transition">
          新規オーナー登録
        </Link>
      </div>

      <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-2xl overflow-hidden">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">オーナー名</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">メールアドレス</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">紐付施設数</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">登録日</th>
              <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">アクション</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-slate-900">{{ user.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-500">{{ user.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                {{ user.facilities_count }} 施設
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                {{ user.created_at }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <button @click="confirmImpersonate(user)" class="text-amber-600 hover:text-amber-900 font-medium">代理ログイン</button>
                <Link :href="route('admin.users.edit', user.id)" class="text-primary-600 hover:text-primary-900 font-bold">編集</Link>
                <button @click="confirmDelete(user)" class="text-red-600 hover:text-red-900 font-medium ml-2">削除</button>
              </td>
            </tr>
            <tr v-if="users.data.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-slate-400">オーナーが見つかりません</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div v-if="users.links.length > 3" class="mt-6 flex justify-center">
        <nav class="flex gap-1">
          <template v-for="(link, i) in users.links" :key="i">
            <Link
              v-if="link.url"
              :href="link.url"
              v-html="link.label"
              class="px-3 py-1 text-sm rounded-lg border transition"
              :class="link.active ? 'bg-primary-600 text-white border-primary-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            />
            <span
              v-else
              v-html="link.label"
              class="px-3 py-1 text-sm text-slate-300 bg-slate-50 border border-slate-100 rounded-lg cursor-not-allowed"
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
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search);

const handleSearch = () => {
    router.get(route('admin.users.index'), { search: search.value }, { preserveState: true });
};

const confirmDelete = (user) => {
    if (confirm(`${user.name} を削除しますか？`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

const confirmImpersonate = (user) => {
    if (confirm(`${user.name} として代理ログインしますか？`)) {
        router.post(route('admin.impersonate.start', user.id));
    }
};
</script>
