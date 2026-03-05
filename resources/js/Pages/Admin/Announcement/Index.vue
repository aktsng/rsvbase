<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    announcements: Object,
});

const categoryColors = {
    blue: 'bg-blue-100 text-blue-700 border-blue-200',
    amber: 'bg-amber-100 text-amber-700 border-amber-200',
    emerald: 'bg-emerald-100 text-emerald-700 border-emerald-200',
    red: 'bg-red-100 text-red-700 border-red-200',
    slate: 'bg-slate-100 text-slate-600 border-slate-200',
};

const deleteAnnouncement = (id) => {
    if (!confirm('このお知らせを削除しますか？')) return;
    router.delete(route('admin.announcements.destroy', id));
};
</script>

<template>
  <AdminLayout>
    <Head title="お知らせ管理" />
    <template #header>お知らせ管理</template>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6">
      <!-- ヘッダー -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">お知らせ一覧</h2>
        <Link :href="route('admin.announcements.create')"
              class="px-5 py-2.5 bg-primary-600 text-white font-bold text-sm rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-200 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          新規作成
        </Link>
      </div>

      <!-- フラッシュメッセージ -->
      <div v-if="$page.props.flash?.success" class="mb-6 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl px-4 py-3 text-sm font-bold">
        {{ $page.props.flash.success }}
      </div>

      <!-- テーブル -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wider">
            <tr>
              <th class="px-6 py-3">タイトル</th>
              <th class="px-6 py-3 text-center">カテゴリ</th>
              <th class="px-6 py-3 text-center">公開</th>
              <th class="px-6 py-3 text-center">ダッシュボード</th>
              <th class="px-6 py-3">公開日時</th>
              <th class="px-6 py-3 text-center">操作</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="a in announcements.data" :key="a.id" class="hover:bg-slate-50/50 transition">
              <td class="px-6 py-4">
                <div class="font-bold text-slate-800">{{ a.title }}</div>
                <div class="text-xs text-slate-400 mt-0.5">作成: {{ a.created_at }}</div>
              </td>
              <td class="px-6 py-4 text-center">
                <span :class="categoryColors[a.category_color] || categoryColors.slate"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border">
                  {{ a.category_label }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span v-if="a.is_published" class="inline-flex items-center gap-1 text-emerald-600 font-bold text-xs">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  公開中
                </span>
                <span v-else class="text-slate-400 text-xs font-medium">非公開</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span v-if="a.is_pinned_dashboard" class="inline-flex items-center gap-1 text-blue-600 font-bold text-xs">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  掲載中
                </span>
                <span v-else class="text-slate-300 text-xs">—</span>
              </td>
              <td class="px-6 py-4 text-slate-600 text-sm">{{ a.published_at || '—' }}</td>
              <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <Link :href="route('admin.announcements.edit', a.id)"
                        class="px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition">
                    編集
                  </Link>
                  <button @click="deleteAnnouncement(a.id)"
                          class="px-3 py-1.5 bg-red-50 text-red-600 text-xs font-bold rounded-lg hover:bg-red-100 transition">
                    削除
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="announcements.data.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-400">お知らせはまだありません。</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div v-if="announcements.last_page > 1" class="mt-6 flex justify-center gap-2">
        <Link v-for="link in announcements.links" :key="link.label"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1.5 text-sm font-medium rounded-lg transition',
                link.active ? 'bg-primary-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50',
                !link.url ? 'opacity-40 pointer-events-none' : ''
              ]"
              v-html="link.label" />
      </div>
    </div>
  </AdminLayout>
</template>
