<template>
  <AdminLayout>
    <Head :title="`施設詳細: ${facility.name}`" />
    <template #header>
      <div class="flex justify-between items-center w-full">
        <span>施設詳細: {{ facility.name }}</span>
        <div class="flex gap-3">
          <Link :href="route('admin.facilities.edit', facility.uuid)" 
                class="inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-xl shadow-sm text-slate-700 bg-white hover:bg-slate-50 transition">
            編集する
          </Link>
          <button @click="confirmDelete" 
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-red-600 hover:bg-red-700 transition">
            削除
          </button>
        </div>
      </div>
    </template>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="mb-6">
        <Link :href="route('admin.facilities.index')" class="text-sm text-slate-500 hover:text-slate-800 flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          一覧へ戻る
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 基本情報 -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
              <h3 class="text-base font-bold text-slate-900">基本情報</h3>
              <span v-if="facility.is_published" class="bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full text-xs font-bold ring-1 ring-inset ring-blue-700/10">公開中</span>
              <span v-else class="bg-gray-50 text-gray-600 px-2.5 py-0.5 rounded-full text-xs font-bold ring-1 ring-inset ring-gray-500/10">非公開</span>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                <div><dt class="text-sm font-medium text-slate-500">施設名</dt><dd class="mt-1 text-sm text-slate-900 font-bold">{{ facility.name }}</dd></div>
                <div><dt class="text-sm font-medium text-slate-500">UUID</dt><dd class="mt-1 text-sm text-slate-900 font-mono">{{ facility.uuid }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm font-medium text-slate-500">説明</dt><dd class="mt-1 text-sm text-slate-900 whitespace-pre-wrap">{{ facility.description || 'なし' }}</dd></div>
                <div><dt class="text-sm font-medium text-slate-500">所在地</dt><dd class="mt-1 text-sm text-slate-900">{{ facility.address || 'なし' }}</dd></div>
                <div><dt class="text-sm font-medium text-slate-500">連絡先メール</dt><dd class="mt-1 text-sm text-slate-900">{{ facility.email || 'なし' }}</dd></div>
                <div><dt class="text-sm font-medium text-slate-500">電話番号</dt><dd class="mt-1 text-sm text-slate-900">{{ facility.phone || 'なし' }}</dd></div>
                <div><dt class="text-sm font-medium text-slate-500">登録日</dt><dd class="mt-1 text-sm text-slate-900">{{ facility.created_at }}</dd></div>
              </dl>
            </div>
          </div>

          <!-- 部屋リスト -->
          <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
              <h3 class="text-base font-bold text-slate-900">登録されている部屋 ({{ facility.rooms.length }})</h3>
            </div>
            <div class="divide-y divide-slate-100">
              <div v-for="room in facility.rooms" :key="room.uuid" class="px-6 py-4 group">
                <div class="flex justify-between items-center">
                  <div>
                    <div class="font-bold text-slate-900">{{ room.name }}</div>
                    <div class="text-xs text-slate-500 font-mono">{{ room.uuid }}</div>
                  </div>
                  <div class="font-bold text-slate-900">
                    ¥{{ room.base_price.toLocaleString() }} <span class="text-xs font-normal text-slate-400">/ 泊〜</span>
                  </div>
                </div>
              </div>
              <div v-if="facility.rooms.length === 0" class="px-6 py-8 text-center text-slate-400 text-sm">
                部屋が登録されていません。
              </div>
            </div>
          </div>
        </div>

        <!-- サイドバー情報 -->
        <div class="space-y-6">
          <!-- オーナー情報 -->
          <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 class="text-sm font-bold text-slate-900">オーナー情報</h3>
            </div>
            <div class="p-6 text-center">
              <div class="h-16 w-16 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                {{ facility.owner.name.charAt(0) }}
              </div>
              <div class="font-bold text-slate-900">{{ facility.owner.name }}</div>
              <div class="text-sm text-slate-500">{{ facility.owner.email }}</div>
            </div>
          </div>

          <!-- Stripe連携状況 -->
          <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
              <h3 class="text-sm font-bold text-slate-900">決済連携 (Stripe)</h3>
            </div>
            <div class="p-6">
              <dl class="space-y-4">
                <div>
                  <dt class="text-xs text-slate-500 font-medium uppercase tracking-wider">Status</dt>
                  <dd class="mt-1">
                    <span v-if="facility.stripe_status === 'active'" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active (連携済み)</span>
                    <span v-else-if="facility.stripe_status === 'pending'" class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Pending (手続き中)</span>
                    <span v-else class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">{{ facility.stripe_status || 'Unlinked' }}</span>
                  </dd>
                </div>
                <div v-if="facility.stripe_account_id">
                  <dt class="text-xs text-slate-500 font-medium uppercase tracking-wider">Account ID</dt>
                  <dd class="mt-1 text-sm font-mono text-slate-900 break-all bg-slate-50 p-2 rounded border border-slate-100">{{ facility.stripe_account_id }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    facility: Object,
});

const confirmDelete = () => {
    if (confirm('本当にこの施設を削除しますか？\n(論理削除されるため、管理画面からは消えますがデータベース上には残ります)')) {
        router.delete(route('admin.facilities.destroy', props.facility.uuid));
    }
};
</script>
