<template>
  <OwnerLayout>
    <Head title="施設の初期設定" />
    <template #header>施設の初期設定</template>

    <div class="max-w-3xl mx-auto py-10">
      <div class="bg-white shadow rounded-xl p-8 text-center">
        <div class="w-16 h-16 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">ようこそ RsvBase へ</h2>
        <p class="text-slate-600 mb-8">
          予約の受付を開始するには、まず最初の施設を登録する必要があります。<br>
          施設の基本情報、部屋タイプ、料金設定を行ってください。
        </p>

        <Link :href="route('owner.facility.create')" as="button"
              class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          施設を登録する
        </Link>
      </div>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
</script>
