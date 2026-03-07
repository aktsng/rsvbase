<template>
  <OwnerLayout>
    <Head title="特定日料金設定" />
    <template #header>
      <div class="flex items-center gap-1.5 text-sm sm:text-base font-medium overflow-hidden whitespace-nowrap flex-nowrap">
        <Link :href="route('owner.rooms.index')" class="text-slate-400 hover:text-slate-600 transition shrink-0">部屋一覧</Link>
        <span class="text-slate-300 shrink-0">/</span>
        <Link :href="route('owner.rooms.edit', room.uuid)" 
              class="text-slate-400 hover:text-slate-600 transition truncate max-w-[80px] sm:max-w-none">
          {{ room.name }}
        </Link>
        <span class="text-slate-300 shrink-0">/</span>
        <span class="text-slate-800 shrink-0">特定料金</span>
      </div>
    </template>

    <div class="max-w-5xl mx-auto py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- 左側：新規追加フォーム -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
            <h2 class="text-lg font-bold text-slate-800 mb-4">特別料金の追加</h2>
            <p class="text-sm text-slate-500 mb-6">年末年始、GW、お盆などの特別期間やイベント開催時の料金を設定します。特定日料金は基本料金・週末料金よりも優先されます。</p>

            <form @submit.prevent="submit" class="space-y-4">
              <div>
                <label for="label" class="block text-sm font-medium text-slate-700 mb-1">ラベル <span class="text-red-500">*</span></label>
                <input id="label" v-model="form.label" type="text" required
                       class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"
                       placeholder="例：年末年始">
                <p v-if="form.errors.label" class="mt-1 text-sm text-red-500">{{ form.errors.label }}</p>
              </div>

              <div class="space-y-4 pt-2 pb-2">
                <DateRangePicker
                  v-model="dateRange"
                  check-in-label="開始日"
                  check-out-label="終了日"
                  nights-label="日間"
                  :allow-same-day="true"
                />
                <p v-if="form.errors.start_date || form.errors.end_date" class="mt-1 text-sm text-red-500">期間が正しくありません</p>
              </div>

              <div>
                <label for="price_per_night" class="block text-sm font-medium text-slate-700 mb-1">1泊あたりの料金 <span class="text-red-500">*</span></label>
                 <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-slate-500 sm:text-sm">¥</span>
                  </div>
                  <input id="price_per_night" v-model="form.price_per_night" type="number" min="0" required
                         class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"
                         placeholder="20000">
                </div>
                <p v-if="form.errors.price_per_night" class="mt-1 text-sm text-red-500">{{ form.errors.price_per_night }}</p>
              </div>

              <div class="pt-2">
                <button type="submit" :disabled="form.processing"
                        class="w-full py-2.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition disabled:opacity-50">
                  <span v-if="form.processing">追加中...</span>
                  <span v-else>設定を追加</span>
                </button>
              </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100">
              <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">現在の通常料金</h4>
              <div class="flex justify-between items-center text-sm py-1">
                <span class="text-slate-600">基本料金</span>
                <span class="font-medium text-slate-800">¥{{ room.base_price_per_night.toLocaleString() }}</span>
              </div>
              <div class="flex justify-between items-center text-sm py-1">
                <span class="text-slate-600">週末料金</span>
                <span class="font-medium text-slate-800">
                  {{ room.weekend_price_per_night ? `¥${room.weekend_price_per_night.toLocaleString()}` : '設定なし' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- 右側：設定済みリスト -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
              <h2 class="text-lg font-bold text-slate-800">設定済みの特定日料金</h2>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-medium whitespace-nowrap">
                  <tr>
                    <th class="px-6 py-3 min-w-[150px]">期間</th>
                    <th class="px-6 py-3 min-w-[150px]">ラベル</th>
                    <th class="px-6 py-3 min-w-[100px]">1泊料金</th>
                    <th class="px-6 py-3 text-right">操作</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="sp in specialPrices" :key="sp.id" class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 font-medium text-slate-800 whitespace-nowrap">
                      {{ formatDateWithDay(sp.start_date) }} <span class="text-slate-400 font-normal">〜</span> {{ formatDateWithDay(sp.end_date) }}
                    </td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                        {{ sp.label }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-slate-800 font-medium whitespace-nowrap">
                      ¥{{ sp.price_per_night.toLocaleString() }}
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                      <button @click="confirmDelete(sp.id)" class="text-red-500 hover:text-red-700 font-medium text-sm transition">
                        削除
                      </button>
                    </td>
                  </tr>
                  <tr v-if="specialPrices.length === 0">
                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                      <p class="font-medium text-slate-600">特定日料金は設定されていません</p>
                      <p class="text-sm mt-1">左側のフォームから特別期間の料金を追加してください。</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- スクロールヒント -->
            <div class="px-4 pb-4 text-center text-[10px] text-slate-400 font-medium lg:hidden flex justify-center items-center gap-1.5 opacity-60">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
              <span>横にスクロールしてご確認いただけます</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';

const props = defineProps({
    room: Object,
    specialPrices: Array,
});

const form = useForm({
    start_date: '',
    end_date: '',
    price_per_night: '',
    label: '',
});

const dateRange = ref({
    checkIn: '',
    checkOut: '',
});

watch(dateRange, (newVal) => {
    form.start_date = newVal.checkIn;
    form.end_date = newVal.checkOut;
}, { deep: true });

const submit = () => {
    form.post(route('owner.rooms.special-prices.store', props.room.uuid), {
        onSuccess: () => {
            form.reset();
            dateRange.value = { checkIn: '', checkOut: '' };
        },
    });
};

const formatDateWithDay = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const days = ['日', '月', '火', '水', '木', '金', '土'];
    const day = days[date.getDay()];
    return `${y}/${m}/${d}(${day})`;
};

const confirmDelete = (id) => {
    if (confirm('この特定日料金の設定を削除してもよろしいですか？')) {
        router.delete(route('owner.special-prices.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>
