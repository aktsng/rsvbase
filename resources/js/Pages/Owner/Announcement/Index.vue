<script setup>
import { Head, Link } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    announcements: Object,
});

const categoryColors = {
    blue: { bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-100', badge: 'bg-blue-100 text-blue-700 border-blue-200' },
    amber: { bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-100', badge: 'bg-amber-100 text-amber-700 border-amber-200' },
    emerald: { bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-100', badge: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    red: { bg: 'bg-red-50', text: 'text-red-800', border: 'border-red-100', badge: 'bg-red-100 text-red-700 border-red-200' },
    slate: { bg: 'bg-slate-50', text: 'text-slate-600', border: 'border-slate-100', badge: 'bg-slate-100 text-slate-600 border-slate-200' },
};

const expandedId = ref(null);
const toggle = (id) => {
    expandedId.value = expandedId.value === id ? null : id;
};
</script>

<template>
  <OwnerLayout>
    <Head title="お知らせ" />
    <template #header>お知らせ</template>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6">
      <div class="space-y-4">
        <div v-for="a in announcements.data" :key="a.id"
             class="bg-white rounded-2xl shadow-sm border overflow-hidden transition-all duration-200"
             :class="expandedId === a.id ? 'border-primary-200 shadow-md' : 'border-slate-100 hover:border-slate-200'">
          
          <!-- ヘッダー（クリックで開閉） -->
          <button @click="toggle(a.id)" class="w-full px-6 py-4 text-left flex items-center gap-4 group">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span :class="(categoryColors[a.category_color] || categoryColors.slate).badge"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border">
                  {{ a.category_label }}
                </span>
                <span class="text-xs text-slate-400">{{ a.published_at }}</span>
              </div>
              <h3 class="font-bold text-slate-800 group-hover:text-primary-700 transition truncate">{{ a.title }}</h3>
            </div>
            <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0"
                 :class="expandedId === a.id ? 'rotate-180' : ''"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- 本文（展開時） -->
          <div v-if="expandedId === a.id" class="px-6 pb-5 border-t border-slate-50">
            <div class="pt-4 text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ a.body }}</div>
          </div>
        </div>

        <div v-if="announcements.data.length === 0"
             class="text-center py-16 text-slate-400">
          <svg class="w-12 h-12 mx-auto mb-4 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <p class="font-bold text-slate-500">お知らせはまだありません</p>
        </div>
      </div>

      <!-- ページネーション -->
      <div v-if="announcements.last_page > 1" class="mt-8 flex justify-center gap-2">
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
  </OwnerLayout>
</template>
