<template>
  <AdminLayout>
    <Head title="Webhook ログ" />
    <template #header>Webhook ログブラウザ</template>

    <div class="max-w-7xl mx-auto py-6">

      <!-- 検索・フィルター -->
      <div class="mb-6 bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col sm:flex-row gap-4 justify-between items-center">
        <div class="flex gap-2 w-full sm:w-auto">
          <select v-model="filters.status" @change="search" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
            <option value="all">すべてのステータス</option>
            <option value="pending">処理待ち (Pending)</option>
            <option value="processed">処理済 (Processed)</option>
            <option value="failed">失敗 (Failed)</option>
            <option value="ignored">無視 (Ignored)</option>
          </select>
        </div>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input type="text" v-model="filters.search" @keyup.enter="search" placeholder="Event ID または Type..."
                   class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
          </div>
      </div>

      <!-- テーブル -->
      <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">受信日時</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">プロバイダ</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">イベントタイプ</th>
              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">ステータス</th>
              <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">詳細</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 bg-white">
            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 transition">
              <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-slate-800 font-mono sm:pl-6">{{ log.created_at }}</td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                  {{ log.provider }}
                </span>
              </td>
              <td class="px-3 py-4 text-sm text-slate-800 font-mono flex flex-col">
                <span>{{ log.event_type }}</span>
                <span class="text-xs text-slate-400 truncate w-48">{{ log.event_id }}</span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm">
                <span v-if="log.status === 'processed'" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Processed</span>
                <span v-else-if="log.status === 'failed'" class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Failed</span>
                <span v-else-if="log.status === 'ignored'" class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Ignored</span>
                <span v-else class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Pending</span>
              </td>
              <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                <button @click="showDetails(log.id)" class="text-primary-600 hover:text-primary-900">詳細</button>
              </td>
            </tr>
            <tr v-if="logs.data.length === 0">
              <td colspan="5" class="py-12 text-center text-slate-500">ログが見つかりません</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ペジネーション -->
      <div v-if="logs.links.length > 3" class="mt-6 flex justify-center">
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <template v-for="(link, i) in logs.links" :key="i">
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

    <!-- 詳細モーダル -->
    <div v-if="isModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" @click="isModalOpen = false"></div>
      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl max-h-[90vh] flex flex-col">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
              <h3 class="text-lg font-bold text-slate-900">ログ詳細: {{ activeLog?.event_type }}</h3>
              <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <div class="p-6 overflow-y-auto bg-slate-50" v-if="activeLog">
              <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div><dt class="text-sm text-slate-500">Event ID</dt><dd class="text-sm font-mono text-slate-900">{{ activeLog.event_id }}</dd></div>
                  <div><dt class="text-sm text-slate-500">Provider</dt><dd class="text-sm text-slate-900">{{ activeLog.provider }}</dd></div>
                  <div><dt class="text-sm text-slate-500">Status</dt><dd class="text-sm font-bold" :class="activeLog.status === 'processed' ? 'text-green-600' : 'text-red-600'">{{ activeLog.status }}</dd></div>
                  <div><dt class="text-sm text-slate-500">受信日時</dt><dd class="text-sm text-slate-900">{{ activeLog.created_at }}</dd></div>
                  <div class="sm:col-span-2" v-if="activeLog.error_message"><dt class="text-sm text-slate-500">Error</dt><dd class="text-sm text-red-600 p-2 bg-red-50 rounded">{{ activeLog.error_message }}</dd></div>
                </dl>
              </div>

              <h4 class="text-sm font-bold text-slate-700 mb-2">Payload (JSON)</h4>
              <div class="bg-slate-900 text-slate-300 rounded-xl p-4 overflow-x-auto shadow-inner">
                <pre class="text-xs font-mono whitespace-pre-wrap">{{ JSON.stringify(activeLog.payload, null, 2) }}</pre>
              </div>
            </div>
            
            <div class="bg-white px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
              <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto" @click="isModalOpen = false">閉じる</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';

const props = defineProps({
    logs: Object,
    filters: Object,
});

const filters = reactive({
    status: props.filters.status || 'all',
    search: props.filters.search || '',
});

const search = () => {
    router.get(route('admin.webhooks.index'), {
        status: filters.status,
        search: filters.search,
    }, { preserveState: true, preserveScroll: true });
};

// モーダル管理
const isModalOpen = ref(false);
const activeLog = ref(null);

const showDetails = async (uuid) => {
    try {
        const response = await axios.get(route('admin.webhooks.show', uuid));
        activeLog.value = response.data;
        isModalOpen.value = true;
    } catch (e) {
        alert('ログ詳細の取得に失敗しました。');
    }
};
</script>
