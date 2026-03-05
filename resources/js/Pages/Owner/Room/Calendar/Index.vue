<template>
  <OwnerLayout>
    <Head title="カレンダー" />
    <template #header>
      <div class="flex items-center gap-2">
        <Link :href="route('owner.rooms.index')" class="text-slate-400 hover:text-slate-600 transition">部屋一覧</Link>
        <span class="text-slate-300">/</span>
        <Link :href="route('owner.rooms.edit', room.uuid)" class="text-slate-400 hover:text-slate-600 transition">{{ room.name }}</Link>
        <span class="text-slate-300">/</span>
        <span>カレンダー管理</span>
      </div>
    </template>

    <div class="max-w-4xl mx-auto py-6">
      
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <!-- カレンダーヘッダー -->
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link :href="route('owner.rooms.calendar', { room: room.uuid, month: prevMonth })" 
                  class="p-2 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </Link>
            <h2 class="text-xl font-bold text-slate-800">{{ formattedMonth }}</h2>
            <Link :href="route('owner.rooms.calendar', { room: room.uuid, month: nextMonth })" 
                  class="p-2 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>
          
          <div class="flex gap-4 text-xs font-medium text-slate-500">
            <div class="flex items-center gap-1.5">
              <div class="w-3 h-3 bg-white border border-slate-200 rounded-sm"></div> 空室
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-3 h-3 bg-red-50 border border-red-200 rounded-sm"></div> ブロック中
            </div>
            <div class="flex items-center gap-1.5">
              <div class="w-3 h-3 bg-primary-50 border border-primary-200 rounded-sm"></div> 予約済み
            </div>
          </div>
        </div>

        <!-- カレンダー本体 -->
        <div class="p-6">
          <div class="grid grid-cols-7 gap-px bg-slate-200 rounded-xl overflow-hidden shadow-inner border border-slate-200">
            <!-- 曜日ヘッダー -->
            <div v-for="dayName in ['日', '月', '火', '水', '木', '金', '土']" :key="dayName" 
                 class="bg-slate-50 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
              {{ dayName }}
            </div>

            <!-- 空白（月の最初の日の前まで） -->
            <div v-for="blank in blankDays" :key="'blank-'+blank" class="bg-slate-50 min-h-[100px]"></div>

            <!-- 日付セル -->
            <div v-for="day in days" :key="day.date" 
                 @click="toggleDay(day)"
                 class="relative min-h-[100px] p-2 transition group"
                 :class="{
                   'bg-white hover:bg-slate-50 cursor-pointer': !day.is_past && !day.is_reserved && !day.is_blocked,
                   'bg-red-50 hover:bg-red-100 border-red-200 cursor-pointer': !day.is_past && day.is_blocked,
                   'bg-primary-50 border-primary-200 cursor-not-allowed': day.is_reserved,
                   'bg-slate-50 opacity-50 cursor-not-allowed': day.is_past && !day.is_reserved && !day.is_blocked
                 }">
              <div class="flex justify-between items-start">
                <span class="text-sm font-semibold inline-flex items-center justify-center w-7 h-7 rounded-full"
                      :class="{
                        'bg-primary-600 text-white': day.is_today,
                        'text-slate-800': !day.is_today && !day.is_blocked && !day.is_reserved && !day.is_past,
                        'text-slate-400': day.is_past && !day.is_today,
                        'text-red-700': day.is_blocked,
                        'text-primary-800': day.is_reserved
                      }">
                  {{ day.day_number }}
                </span>
                
                <span v-if="processingDate === day.date" class="block w-4 h-4 rounded-full border-2 border-slate-300 border-t-primary-600 animate-spin"></span>
              </div>
              
              <div class="mt-2 flex flex-col gap-1">
                <span v-if="day.is_reserved" class="text-xs font-bold text-primary-700 bg-primary-100 px-1.5 py-0.5 rounded">予約あり</span>
                <span v-if="day.is_blocked" class="text-xs font-bold text-red-700 bg-red-100 px-1.5 py-0.5 rounded">ブロック中</span>
                <span v-if="!day.is_past && !day.is_reserved && !day.is_blocked" class="text-[10px] font-medium text-slate-400 hidden group-hover:block transition">タップでブロック</span>
                <span v-if="!day.is_past && day.is_blocked" class="text-[10px] font-medium text-red-500 hidden group-hover:block transition">タップで解除</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <p class="mt-4 text-sm text-slate-500 text-center">カレンダーの日付をタップすると、その日の予約受付（ブロック）を停止または再開できます。</p>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    room: Object,
    currentMonth: String,
    prevMonth: String,
    nextMonth: String,
    days: Array,
});

// Y-mから「YYYY年M月」に整形
const formattedMonth = computed(() => {
    const [year, month] = props.currentMonth.split('-');
    return `${year}年 ${parseInt(month)}月`;
});

// 月の最初の日の曜日を計算（0:日, 1:月...）して、空白セル数を決める
const blankDays = computed(() => {
    if (props.days.length === 0) return 0;
    const firstDay = new Date(props.days[0].date);
    return firstDay.getDay();
});

const processingDate = ref(null);

const toggleDay = (day) => {
    if (day.is_past || day.is_reserved || processingDate.value) {
        return;
    }

    processingDate.value = day.date;
    const action = day.is_blocked ? 'unblock' : 'block';

    router.post(route('owner.rooms.calendar.blocks.toggle', props.room.uuid), {
        date: day.date,
        action: action
    }, {
        preserveScroll: true,
        onFinish: () => {
            processingDate.value = null;
        }
    });
};
</script>
