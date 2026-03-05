<template>
  <AuthenticatedLayout>
    <template #sidebar>
      <!-- 施設セレクター -->
      <div v-if="managedFacilities.length > 0" class="px-4 py-4 border-b border-slate-100 mb-2">
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">管理施設</label>
        
        <div v-if="managedFacilities.length > 1" class="relative">
          <select 
            :value="currentFacility?.uuid" 
            @change="switchFacility($event.target.value)"
            class="block w-full text-sm border-slate-200 rounded-xl focus:ring-primary-500 focus:border-primary-500 transition py-2 pl-3 pr-10 appearance-none bg-slate-50 font-bold text-slate-700"
          >
            <option v-for="f in managedFacilities" :key="f.uuid" :value="f.uuid">
              {{ f.name }}
            </option>
          </select>
          <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
          </div>
        </div>
        
        <div v-else class="px-1 text-sm font-bold text-slate-700">
          {{ currentFacility?.name }}
        </div>
      </div>

      <SidebarLink :href="route('owner.dashboard')" :active="route().current('owner.dashboard')" icon="home">
        ダッシュボード
      </SidebarLink>
      <SidebarLink :href="route('owner.reservations.index')" :active="route().current('owner.reservations.*')" icon="calendar">
        予約管理
      </SidebarLink>

      <SidebarLink :href="route('owner.facility.edit')" :active="route().current('owner.facility.*')" icon="settings">
        施設設定
      </SidebarLink>
      <SidebarLink :href="route('owner.rooms.index')" :active="route().current('owner.rooms.*')" icon="bed">
        部屋管理
      </SidebarLink>
      <SidebarLink :href="route('owner.stripe.index')" :active="route().current('owner.stripe.*')" icon="credit-card">
        Stripe連携
      </SidebarLink>
      <SidebarLink :href="route('owner.announcements.index')" :active="route().current('owner.announcements.*')" icon="bell">
        お知らせ
      </SidebarLink>
      <SidebarLink :href="route('owner.manual.index')" :active="route().current('owner.manual.*')" icon="book">
        操作マニュアル
      </SidebarLink>
    </template>

    <template #header>
      <slot name="header">ダッシュボード</slot>
    </template>

    <!-- Go Live バナー (Stripe未連携時) -->
    <div v-if="$page.props.auth.is_stripe_connected === false"
         class="mb-6 bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 rounded-2xl p-5 shadow-lg shadow-indigo-200/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-start gap-3 text-white">
        <div class="p-2 bg-white/20 rounded-xl shrink-0">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-.274.857-.637 1.676-1.078 2.44m-2.387 2.803A9.96 9.96 0 0112 19c-4.478 0-8.268-2.943-9.542-7a17.88 17.88 0 011.914-3.346"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-sm">予約ページは現在プレビューモードです</p>
          <p class="text-xs text-white/80 mt-0.5">実際の予約受付を開始するには、売上受取用の口座を連携してください。</p>
        </div>
      </div>
      <Link :href="route('owner.stripe.index')"
            class="shrink-0 px-5 py-2.5 bg-white text-indigo-700 font-bold text-sm rounded-xl hover:bg-indigo-50 transition shadow-sm whitespace-nowrap">
        口座を連携する
      </Link>
    </div>

    <slot />
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SidebarLink from '@/Components/SidebarLink.vue';

const page = usePage();
const managedFacilities = computed(() => page.props.auth.managed_facilities || []);
const currentFacility = computed(() => page.props.auth.current_facility);

const switchFacility = (uuid) => {
    router.post(route('owner.facility.select'), { uuid }, {
        preserveScroll: true
    });
};
</script>
