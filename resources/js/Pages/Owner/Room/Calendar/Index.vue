<template>
  <OwnerLayout>
    <Head title="カレンダー" />
    <template #header>
      <div class="flex items-center gap-1.5 text-sm sm:text-base font-medium overflow-hidden whitespace-nowrap flex-nowrap">
        <Link :href="route('owner.rooms.index')" class="text-slate-400 hover:text-slate-600 transition shrink-0">部屋一覧</Link>
        <span class="text-slate-300 shrink-0">/</span>
        <Link :href="route('owner.rooms.edit', room.uuid)" 
              class="text-slate-400 hover:text-slate-600 transition truncate max-w-[80px] sm:max-w-none">
          {{ room.name }}
        </Link>
        <span class="text-slate-300 shrink-0">/</span>
        <span class="text-slate-800 shrink-0">カレンダー管理</span>
      </div>
    </template>

    <div class="max-w-5xl mx-auto py-6">
      <div class="mb-6 p-4 bg-primary-50 border border-primary-100 text-primary-800 rounded-2xl flex gap-3">
        <svg class="w-6 h-6 shrink-0 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-sm font-medium">カレンダーの日付をタップすると、その日の予約受付（ブロック）を停止または再開できます。</p>
      </div>
      
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <!-- カレンダーヘッダー -->
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <h2 class="text-lg font-bold text-slate-800">
            {{ room.name }} <span class="text-slate-400 font-normal ml-2">空室管理</span>
          </h2>
          <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4">
            <div class="flex items-center bg-slate-100 rounded-xl p-1">
              <Link :href="route('owner.rooms.calendar', { room: room.uuid, month: prevMonth })" 
                    preserve-scroll
                    class="p-2 hover:bg-white hover:shadow-sm rounded-lg transition text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </Link>
              <span class="px-4 text-xs sm:text-sm font-bold text-slate-700 min-w-[80px] sm:min-w-[100px] text-center">
                {{ formattedMonth }}
              </span>
              <Link :href="route('owner.rooms.calendar', { room: room.uuid, month: nextMonth })"
                    preserve-scroll
                    class="p-2 hover:bg-white hover:shadow-sm rounded-lg transition text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
            </div>
            <Link :href="route('owner.rooms.calendar', { room: room.uuid })" preserve-scroll class="px-3 py-2 bg-slate-50 text-slate-500 rounded-xl text-xs font-bold hover:bg-slate-100 transition whitespace-nowrap">
              当月
            </Link>
          </div>
        </div>

        <!-- カレンダー本体 -->
        <div class="overflow-x-auto p-4 sm:p-6">
          <table class="w-full border-collapse border border-slate-100 min-w-[600px]">
            <thead>
              <tr>
                <th v-for="dayName in ['日', '月', '火', '水', '木', '金', '土']" :key="dayName" 
                    class="bg-slate-50 border border-slate-100 py-3 text-center text-xs font-bold"
                    :class="dayName === '日' ? 'text-red-600' : (dayName === '土' ? 'text-blue-600' : 'text-slate-500')">
                  {{ dayName }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(week, wIndex) in calendarWeeks" :key="wIndex">
                <td v-for="(day, dIndex) in week" :key="dIndex"
                    class="border border-slate-100 p-0 text-center h-20 sm:h-24 relative"
                    :class="[
                      day ? (day.is_past ? 'bg-slate-50/50' : 'bg-white') : 'bg-slate-50/20',
                      day && day.is_today ? 'bg-primary-50/30' : ''
                    ]">
                  <template v-if="day">
                    <!-- 日付番号 -->
                    <div class="absolute top-2 left-2 flex items-center gap-1.5 z-10">
                      <span class="text-sm font-bold leading-none"
                            :class="{
                              'text-primary-600 w-6 h-6 rounded-full bg-primary-50 flex items-center justify-center': day.is_today,
                              'text-red-600': !day.is_today && dIndex === 0,
                              'text-blue-600': !day.is_today && dIndex === 6,
                              'text-slate-700': !day.is_today && dIndex !== 0 && dIndex !== 6,
                              'opacity-30': day.is_past
                            }">
                        {{ day.day_number }}
                      </span>
                      <span v-if="processingDate === day.date" class="block w-3 h-3 rounded-full border-2 border-slate-200 border-t-primary-600 animate-spin"></span>
                    </div>

                    <!-- セル全体をボタンに -->
                    <div @click="toggleDay(day)"
                         class="w-full h-full flex items-center justify-center pt-6 transition relative group"
                         :class="[ 
                           day.is_past || day.is_reserved ? 'cursor-not-allowed' : 'cursor-pointer hover:bg-slate-50/80',
                           day.is_blocked ? 'bg-red-50/30' : ''
                         ]">
                      
                      <!-- 予約あり表示 (ダッシュボードスタイル) -->
                      <div v-if="day.is_reserved" class="flex items-center justify-center w-full h-full relative">
                        <!-- 接続線 -->
                        <div v-if="day.reservation_position && day.reservation_position !== 'single'" 
                             class="absolute top-1/2 -translate-y-1/2 h-0.5 bg-emerald-200"
                             :class="[
                               day.reservation_position === 'start' ? 'left-1/2 right-0' : '',
                               day.reservation_position === 'middle' ? 'left-0 right-0' : '',
                               day.reservation_position === 'end' ? 'left-0 right-1/2' : '',
                             ]">
                        </div>
                        <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center shadow-sm"
                             :class="day.reservation_day_number === 1 ? 'bg-emerald-500 text-white' : 'bg-white border-2 border-emerald-500 text-emerald-600 font-bold'">
                          <svg v-if="day.reservation_day_number === 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                          </svg>
                          <span v-else class="text-xs">{{ day.reservation_day_number }}</span>
                        </div>
                      </div>

                      <!-- ブロック中表示 -->
                      <div v-else-if="day.is_blocked" class="text-red-400">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <circle cx="12" cy="12" r="9" />
                          <line x1="18.36" y1="5.64" x2="5.64" y2="18.36" />
                        </svg>
                      </div>

                      <!-- 空室 (タップ可能エリア) -->
                      <div v-else-if="!day.is_past" class="text-slate-200 font-bold text-2xl group-hover:text-primary-300 transition">
                        ◯
                      </div>
                    </div>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 凡例 -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-wrap gap-4 text-xs text-slate-500">
          <div class="flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center">
              <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span>予約あり</span>
          </div>
          <div class="flex items-center gap-1.5">
            <div class="flex items-center">
              <div class="w-4 h-4 rounded-full border border-emerald-500 bg-white flex items-center justify-center text-[8px] text-emerald-600 font-bold">2</div>
              <div class="w-3 h-0.5 bg-emerald-200"></div>
              <div class="w-4 h-4 rounded-full border border-emerald-500 bg-white flex items-center justify-center text-[8px] text-emerald-600 font-bold">3</div>
            </div>
            <span>連泊（2日目〜）</span>
          </div>
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="9" />
              <line x1="18.36" y1="5.64" x2="5.64" y2="18.36" />
            </svg>
            <span>ブロック中（クリックで解除）</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="font-bold text-slate-200 text-sm">◯</span>
            <span>空き（クリックでブロック）</span>
          </div>
          <div class="flex items-center gap-1.5 ml-auto">
            <span class="inline-block w-3 h-3 bg-primary-50 border border-primary-200 rounded-sm"></span>
            <span>本日</span>
          </div>
        </div>
      </div>
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

// カレンダーを週ごとの二次元配列に変換
const calendarWeeks = computed(() => {
    const weeks = [];
    if (props.days.length === 0) return weeks;

    let currentWeek = Array(7).fill(null);
    const firstDay = new Date(props.days[0].date);
    const firstDayOfWeek = firstDay.getDay();

    // 最初の週の空白を埋める
    let dayCount = firstDayOfWeek;
    
    props.days.forEach(day => {
        currentWeek[dayCount] = day;
        dayCount++;
        
        if (dayCount === 7) {
            weeks.push(currentWeek);
            currentWeek = Array(7).fill(null);
            dayCount = 0;
        }
    });

    if (dayCount > 0) {
        weeks.push(currentWeek);
    }

    return weeks;
});

// Y-mから「YYYY年M月」に整形
const formattedMonth = computed(() => {
    const [year, month] = props.currentMonth.split('-');
    return `${year}年 ${parseInt(month)}月`;
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
