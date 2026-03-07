<template>
  <OwnerLayout>
    <Head title="部屋一覧" />
    <template #header>部屋の管理</template>

    <div class="max-w-5xl mx-auto py-6">
      <div v-if="!facility_is_published" class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl flex gap-3">
        <svg class="w-6 h-6 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
          <p class="text-sm">施設が非公開のため、ゲストは現在部屋を予約できません。公開状態は<Link :href="route('owner.facility.edit')" class="font-semibold underline">施設設定</Link>から変更できます。</p>
        </div>
      </div>

      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">登録済みの部屋</h2>
        <Link :href="route('owner.rooms.create')" 
              class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-medium text-sm rounded-lg hover:bg-primary-700 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          新しい部屋を追加
        </Link>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 font-medium">
              <tr>
                <th class="sticky left-0 z-20 bg-slate-50 px-6 py-4 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">部屋名</th>
                <th class="px-6 py-4 min-w-[80px]">定員</th>
                <th class="px-6 py-4 min-w-[120px]">基本料金</th>
                <th class="px-6 py-4 min-w-[100px]">清掃費</th>
                <th class="px-6 py-4 min-w-[100px]">ステータス</th>
                <th class="px-6 py-4 text-right min-w-[200px]">操作</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="room in rooms" :key="room.uuid" class="hover:bg-slate-50 transition">
                <td class="sticky left-0 z-10 bg-white px-6 py-4 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                  <div class="font-bold text-slate-800 whitespace-nowrap">{{ room.name }}</div>
                </td>
                <td class="px-6 py-4">{{ room.capacity }} 名</td>
                <td class="px-6 py-4 text-slate-800 font-medium">¥{{ room.base_price_per_night.toLocaleString() }}</td>
                <td class="px-6 py-4 text-slate-800 font-medium">¥{{ room.cleaning_fee.toLocaleString() }}</td>
                <td class="px-6 py-4">
                  <span v-if="room.is_active" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 shrink-0">公開</span>
                  <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 shrink-0">非公開</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2 sm:gap-4">
                    <Link :href="route('owner.rooms.calendar', room.uuid)" 
                          class="inline-flex items-center justify-center p-2 sm:p-0 bg-primary-50 sm:bg-transparent text-primary-600 hover:text-primary-900 rounded-lg sm:rounded-none transition-colors group">
                      <span class="text-[10px] sm:text-sm font-bold leading-tight sm:leading-normal text-center w-5 sm:w-auto">空室管理</span>
                    </Link>
                    <Link :href="route('owner.rooms.special-prices.index', room.uuid)" 
                          class="inline-flex items-center justify-center p-2 sm:p-0 bg-primary-50 sm:bg-transparent text-primary-600 hover:text-primary-900 rounded-lg sm:rounded-none transition-colors group">
                      <span class="text-[10px] sm:text-sm font-bold leading-tight sm:leading-normal text-center w-5 sm:w-auto">特定料金</span>
                    </Link>
                    <Link :href="route('owner.rooms.edit', room.uuid)" 
                          class="inline-flex items-center justify-center p-2 sm:p-0 bg-slate-50 sm:bg-transparent text-slate-500 hover:text-slate-800 rounded-lg sm:rounded-none transition-colors group">
                      <span class="text-[10px] sm:text-sm font-bold leading-tight sm:leading-normal text-center w-5 sm:w-auto">編集</span>
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-if="rooms.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                  <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                  <p class="font-medium text-slate-600">まだ部屋が登録されていません</p>
                  <p class="text-sm mt-1">「新しい部屋を追加」ボタンから最初の部屋を登録してください。</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 pb-4 text-center text-[10px] text-slate-400 font-medium lg:hidden flex justify-center items-center gap-1.5 opacity-60">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
          <span>横にスクロールしてご確認いただけます</span>
        </div>
      </div>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';

defineProps({
    rooms: Array,
    facility_is_published: Boolean,
});
</script>
