<template>
  <AdminLayout>
    <Head title="SuperAdmin Dashboard" />
    <template #header>SuperAdmin ダッシュボード</template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-slate-500 text-sm font-medium mb-1">総施設数</h3>
        <p class="text-3xl font-bold text-slate-800">{{ stats.total_facilities }}</p>
      </div>
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-slate-500 text-sm font-medium mb-1">オーナーユーザー数</h3>
        <p class="text-3xl font-bold text-slate-800">{{ stats.total_owners }}</p>
      </div>
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-slate-500 text-sm font-medium mb-1">今月流通総額 (GMV)</h3>
        <p class="text-3xl font-bold text-slate-800">¥{{ stats.monthly_gmv.toLocaleString() }}</p>
      </div>
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-slate-500 text-sm font-medium mb-1">今月プラットフォーム手数料</h3>
        <p class="text-3xl font-bold text-success-500">¥{{ stats.monthly_platform_fees.toLocaleString() }}</p>
      </div>
    </div>

    <!-- ここにオーナー一覧や代理ログインボタンを追加 -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
      <h2 class="text-lg font-bold text-slate-800 mb-4">システムステータス</h2>
      <p class="text-slate-600">現在、システムは正常に稼働しています。</p>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    stats: Object,
});
</script>
