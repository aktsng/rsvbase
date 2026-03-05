<template>
  <AdminLayout>
    <Head title="予約一覧" />
    <template #header>予約管理</template>

    <div class="mb-6 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
      <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
        <!-- 予約番号 -->
        <div class="lg:col-span-1">
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 px-1">予約番号</label>
          <input v-model="form.code" type="text" placeholder="R-..."
                 class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
        </div>

        <!-- オーナー名 -->
        <div class="lg:col-span-1">
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 px-1">オーナー</label>
          <select v-model="form.owner"
                  class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 text-slate-700 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
            <option value="">すべて</option>
            <option v-for="owner in owners" :key="owner.id" :value="owner.id">{{ owner.name }}</option>
          </select>
        </div>

        <!-- 施設名 -->
        <div class="lg:col-span-1">
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 px-1">施設</label>
          <select v-model="form.facility"
                  class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 text-slate-700 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
            <option value="">すべて</option>
            <option v-for="facility in facilities" :key="facility.uuid" :value="facility.uuid">{{ facility.name }}</option>
          </select>
        </div>

        <!-- 受付日 -->
        <div class="lg:col-span-2">
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 px-1">予約受付日</label>
          <div class="flex items-center gap-2">
            <input v-model="form.booked_from" type="date"
                   class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
            <span class="text-slate-400">〜</span>
            <input v-model="form.booked_to" type="date"
                   class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
          </div>
        </div>

        <!-- 宿泊日 -->
        <div class="lg:col-span-2">
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1 px-1">宿泊日（滞在中）</label>
          <div class="flex items-center gap-2">
            <input v-model="form.stay_from" type="date"
                   class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
            <span class="text-slate-400">〜</span>
            <input v-model="form.stay_to" type="date"
                   class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
          </div>
        </div>

        <div class="lg:col-span-6 flex justify-end gap-2 mt-2">
          <button type="button" @click="resetSearch" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-xl transition">
            クリア
          </button>
          <button type="submit" class="px-6 py-2 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition shadow-sm">
            検索する
          </button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-500 font-medium">
            <tr>
              <th class="px-6 py-4">予約日</th>
              <th class="px-6 py-4">オーナー / 施設</th>
              <th class="px-6 py-4">ゲスト</th>
              <th class="px-6 py-4">宿泊期間</th>
              <th class="px-6 py-4">料金・決済</th>
              <th class="px-6 py-4">ステータス</th>
              <th class="px-6 py-4">アクション</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="res in reservations.data" :key="res.uuid" class="hover:bg-slate-50/50 transition">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="font-mono text-primary-600 font-bold block mb-0.5">{{ res.reservation_code }}</span>
                <span class="text-xs text-slate-400">{{ res.created_at }}</span>
              </td>
              <td class="px-6 py-4">
                <div class="font-bold text-slate-800">{{ res.facility_name }}</div>
                <div class="text-xs text-slate-500 leading-tight mt-0.5">{{ res.owner_name }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="font-medium text-slate-800">{{ res.guest_name }}</div>
                <div class="text-xs text-slate-400 leading-tight mt-0.5">{{ res.guest_email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-slate-800">{{ res.check_in_date }} 〜 {{ res.check_out_date }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ res.room_name }}</div>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="font-bold text-slate-800 text-base">¥{{ res.total_amount.toLocaleString() }}</div>
                <div class="text-xs mt-1 font-medium">
                  <span v-if="res.payment_method === 'stripe'" class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">オンライン決済</span>
                  <span v-else class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100">現地決済</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="res.status === 'confirmed'" class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                  確定
                </span>
                <span v-else-if="['canceled', 'cancelled', 'refunded'].includes(res.status)" class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-500">
                  キャンセル / 返金済
                </span>
                <span v-else class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800">
                  {{ res.status === 'pending' ? '仮予約' : res.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <Link :href="route('admin.reservations.show', res.uuid)" class="text-primary-600 hover:text-primary-900 font-bold block sm:inline-block">詳細</Link>
              </td>

            </tr>
            <tr v-if="reservations.data.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                予約が見つかりませんでした。条件を変えて検索してください。
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ページネーション -->
      <div v-if="reservations.links && reservations.links.length > 3" class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-center">
        <div class="flex gap-1">
          <template v-for="(link, i) in reservations.links" :key="i">
            <Link
              v-if="link.url"
              :href="link.url"
              v-html="link.label"
              class="px-3 py-1 text-sm rounded-lg transition"
              :class="link.active ? 'bg-primary-600 text-white font-bold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
            />
            <span
              v-else
              v-html="link.label"
              class="px-3 py-1 text-sm text-slate-300 bg-slate-50 border border-slate-100 rounded-lg cursor-not-allowed"
            />
          </template>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    reservations: Object,
    owners: Array,
    facilities: Array,
    filters: Object,
});

const form = useForm({
    code: props.filters.code || '',
    owner: props.filters.owner || '',
    facility: props.filters.facility || '',
    booked_from: props.filters.booked_from || '',
    booked_to: props.filters.booked_to || '',
    stay_from: props.filters.stay_from || '',
    stay_to: props.filters.stay_to || '',
});

const search = () => {
    form.get(route('admin.reservations.index'), {
        preserveState: true,
        replace: true,
    });
};

const resetSearch = () => {
    router.get(route('admin.reservations.index'));
};
</script>
