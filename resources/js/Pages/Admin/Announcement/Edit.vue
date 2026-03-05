<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    announcement: Object,
});

const form = useForm({
    title: props.announcement.title,
    body: props.announcement.body,
    category: props.announcement.category,
    is_published: props.announcement.is_published,
    is_pinned_dashboard: props.announcement.is_pinned_dashboard,
    published_at: props.announcement.published_at || '',
});

const submit = () => {
    form.put(route('admin.announcements.update', props.announcement.id));
};
</script>

<template>
  <AdminLayout>
    <Head title="お知らせ編集" />
    <template #header>
      <div class="flex items-center gap-4">
        <Link :href="route('admin.announcements.index')" class="p-2 bg-white rounded-full text-slate-400 hover:text-slate-600 shadow-sm border border-slate-100 transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </Link>
        <span>お知らせ編集</span>
      </div>
    </template>

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-6">
          <!-- タイトル -->
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">タイトル *</label>
            <input type="text" v-model="form.title" required
                   class="block w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition"
                   placeholder="お知らせのタイトル" />
            <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
          </div>

          <!-- カテゴリ -->
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">カテゴリ *</label>
            <select v-model="form.category"
                    class="block w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition">
              <option value="info">お知らせ</option>
              <option value="maintenance">メンテナンス</option>
              <option value="feature">新機能</option>
              <option value="important">重要</option>
            </select>
          </div>

          <!-- 本文 -->
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">本文 *</label>
            <textarea v-model="form.body" rows="10" required
                      class="block w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition"
                      placeholder="お知らせの本文を入力してください" />
            <p v-if="form.errors.body" class="mt-1 text-xs text-red-500">{{ form.errors.body }}</p>
          </div>

          <!-- 公開日時 -->
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">公開日時</label>
            <input type="datetime-local" v-model="form.published_at"
                   class="block w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 transition" />
            <p class="mt-1 text-xs text-slate-400">空欄の場合、公開時に現在日時が自動設定されます</p>
          </div>

          <!-- フラグ -->
          <div class="flex flex-col sm:flex-row gap-6 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-3 cursor-pointer group">
              <input type="checkbox" v-model="form.is_published"
                     class="h-5 w-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 transition" />
              <div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900">公開する</span>
                <p class="text-[10px] text-slate-400">オーナーのお知らせ一覧に表示されます</p>
              </div>
            </label>
            <label class="flex items-center gap-3 cursor-pointer group">
              <input type="checkbox" v-model="form.is_pinned_dashboard"
                     class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition" />
              <div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900">ダッシュボードに掲載</span>
                <p class="text-[10px] text-slate-400">オーナーダッシュボードの上部に目立つ形で表示されます</p>
              </div>
            </label>
          </div>
        </div>

        <button type="submit" :disabled="form.processing"
                class="w-full py-3.5 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-200 disabled:opacity-50">
          <span v-if="form.processing">保存中...</span>
          <span v-else>お知らせを更新する</span>
        </button>
      </form>
    </div>
  </AdminLayout>
</template>
