<template>
  <div class="space-y-3">
    <!-- 選択済み日付の表示 -->
    <div class="flex items-center gap-2">
      <div class="flex-1 px-3 py-2 rounded-xl text-sm font-medium transition cursor-pointer"
           :class="isSelectingCheckIn ? 'bg-primary-50 border-2 border-primary-400 text-primary-700' : 'bg-slate-50 border border-slate-200 text-slate-700'"
           @click="isSelectingCheckIn = true">
        <div class="text-[10px] font-bold uppercase tracking-wider" :class="isSelectingCheckIn ? 'text-primary-500' : 'text-slate-400'">{{ checkInLabel }}</div>
        <div class="mt-0.5" :class="modelValue.checkIn ? '' : 'text-slate-400'">{{ modelValue.checkIn ? formatDisplayDate(modelValue.checkIn) : '—' }}</div>
      </div>
      <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
      </svg>
      <div class="flex-1 px-3 py-2 rounded-xl text-sm font-medium transition cursor-pointer"
           :class="!isSelectingCheckIn ? 'bg-primary-50 border-2 border-primary-400 text-primary-700' : 'bg-slate-50 border border-slate-200 text-slate-700'"
           @click="modelValue.checkIn && (isSelectingCheckIn = false)">
        <div class="text-[10px] font-bold uppercase tracking-wider" :class="!isSelectingCheckIn ? 'text-primary-500' : 'text-slate-400'">{{ checkOutLabel }}</div>
        <div class="mt-0.5" :class="modelValue.checkOut ? '' : 'text-slate-400'">{{ modelValue.checkOut ? formatDisplayDate(modelValue.checkOut) : '—' }}</div>
      </div>
    </div>

    <!-- カレンダー本体 -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden select-none">
      <!-- ヘッダー：月ナビゲーション -->
      <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-100">
        <button type="button" @click="prevMonth" :disabled="isPrevDisabled"
                class="p-1.5 rounded-lg hover:bg-white hover:shadow-sm transition disabled:opacity-30 disabled:cursor-not-allowed">
          <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <span class="text-sm font-bold text-slate-800">{{ monthLabel }}</span>
        <button type="button" @click="nextMonth"
                class="p-1.5 rounded-lg hover:bg-white hover:shadow-sm transition">
          <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- 曜日ヘッダー -->
      <div class="grid grid-cols-7 text-center border-b border-slate-100">
        <div v-for="(day, i) in weekDays" :key="i"
             class="py-2 text-[10px] font-bold uppercase tracking-wider"
             :class="i === 0 ? 'text-red-400' : (i === 6 ? 'text-blue-400' : 'text-slate-400')">
          {{ day }}
        </div>
      </div>

      <!-- 日付グリッド -->
      <div class="grid grid-cols-7">
        <div v-for="(cell, i) in calendarCells" :key="i" class="relative">
          <!-- 空セル -->
          <div v-if="!cell" class="h-10"></div>
          <!-- 日付セル -->
          <button v-else type="button"
                  :disabled="cell.disabled"
                  @click="selectDate(cell.dateStr)"
                  @mouseenter="hoverDate = cell.dateStr"
                  @mouseleave="hoverDate = null"
                  class="w-full h-10 flex items-center justify-center text-sm transition-all relative">
            <!-- 範囲ハイライト背景（帯） -->
            <div v-if="isInRange(cell.dateStr) && !isEndpoint(cell.dateStr)" 
                 class="absolute inset-0 bg-primary-50"></div>
            <div v-if="isInRange(cell.dateStr) && cell.dateStr === modelValue.checkIn" 
                 class="absolute inset-y-0 right-0 left-1/2 bg-primary-50"></div>
            <div v-if="isInRange(cell.dateStr) && (cell.dateStr === modelValue.checkOut || (!modelValue.checkOut && !isSelectingCheckIn && hoverDate === cell.dateStr))" 
                 class="absolute inset-y-0 left-0 right-1/2 bg-primary-50"></div>
            <!-- チェックイン/チェックアウト丸マーカー -->
            <span v-if="cell.dateStr === modelValue.checkIn || cell.dateStr === modelValue.checkOut"
                  class="absolute w-9 h-9 rounded-full bg-primary-600 z-0"></span>
            <!-- 今日マーカー -->
            <span v-else-if="cell.isToday && !isInRange(cell.dateStr)"
                  class="absolute w-9 h-9 rounded-full border-2 border-primary-300 z-0"></span>
            
            <!-- 予約状況・ブロック表示 -->
            <div v-if="availability[cell.dateStr] && cell.dateStr !== modelValue.checkIn && cell.dateStr !== modelValue.checkOut"
                 class="absolute bottom-1 left-1/2 -translate-x-1/2 flex gap-0.5">
              <span v-if="availability[cell.dateStr].type === 'reserved'" 
                    class="w-1 h-1 rounded-full bg-emerald-500"></span>
              <span v-if="availability[cell.dateStr].type === 'blocked'" 
                    class="w-1 h-1 rounded-full bg-slate-400"></span>
            </div>

            <span class="relative z-10" :class="cellClass(cell)">{{ cell.day }}</span>
          </button>
        </div>
      </div>

      <!-- 泊数表示 -->
      <div v-if="nightsCount > 0" class="px-4 py-2 bg-primary-50 border-t border-primary-100 text-center">
        <span class="text-xs font-bold text-primary-700">{{ nightsCount }} {{ nightsLabel }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useI18n } from '@/composables/useI18n.js';

const { t, isEn } = useI18n();

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ checkIn: null, checkOut: null }),
  },
  checkInLabel: { type: String, default: '' },
  checkOutLabel: { type: String, default: '' },
  nightsLabel: { type: String, default: '' },
  availability: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['update:modelValue']);

const isSelectingCheckIn = ref(true);
const hoverDate = ref(null);

// 日付文字列 YYYY-MM-DD
function formatDateStr(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

// 表示用の日付フォーマット
function formatDisplayDate(dateStr) {
  if (!dateStr) return '';
  const [y, m, d] = dateStr.split('-');
  const date = new Date(parseInt(y), parseInt(m) - 1, parseInt(d));
  const dow = date.getDay();
  if (isEn.value) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    return `${months[parseInt(m) - 1]} ${parseInt(d)} (${days[dow]})`;
  }
  const days = ['日', '月', '火', '水', '木', '金', '土'];
  return `${parseInt(m)}/${parseInt(d)} (${days[dow]})`;
}

// カレンダー表示月
const today = new Date();
today.setHours(0, 0, 0, 0);
const todayStr = formatDateStr(today);

const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());

// 曜日名
const weekDays = computed(() => {
  if (isEn.value) return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  return ['日', '月', '火', '水', '木', '金', '土'];
});

// 月表示ラベル
const monthLabel = computed(() => {
  if (isEn.value) {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    return `${months[currentMonth.value]} ${currentYear.value}`;
  }
  return `${currentYear.value}年${currentMonth.value + 1}月`;
});

// 前月に戻れるかどうか
const isPrevDisabled = computed(() => {
  return currentYear.value === today.getFullYear() && currentMonth.value === today.getMonth();
});

const prevMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
};

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
};

// チェックイン後に選択可能な最後の日付を計算
const firstReservedDateAfterCheckIn = computed(() => {
    const ci = props.modelValue.checkIn;
    if (!ci || isSelectingCheckIn.value) return null;
    
    // 現在のチェックイン日より後の予約・ブロック日をすべて取得
    const futureReservedDates = Object.keys(props.availability)
        .filter(d => d >= ci && (props.availability[d].type === 'reserved' || props.availability[d].type === 'blocked'))
        .sort();
    
    return futureReservedDates.length > 0 ? futureReservedDates[0] : null;
});

// カレンダーセルの生成
const calendarCells = computed(() => {
  const year = currentYear.value;
  const month = currentMonth.value;
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  
  const cells = [];
  
  // 空セル（月初の曜日オフセット）
  for (let i = 0; i < firstDay; i++) {
    cells.push(null);
  }
  
  // 日付セル
  for (let d = 1; d <= daysInMonth; d++) {
    const date = new Date(year, month, d);
    const dateStr = formatDateStr(date);
    const dow = date.getDay();
    const isReserved = props.availability[dateStr]?.type === 'blocked' || props.availability[dateStr]?.type === 'reserved';
    
    let isDisabled = date < today;
    
    if (isSelectingCheckIn.value) {
      // チェックイン選択時は、予約済みの日は選択不可
      isDisabled = isDisabled || isReserved;
    } else {
      // チェックアウト選択時
      const ci = props.modelValue.checkIn;
      if (dateStr <= ci) {
        // チェックイン日以前は不可
        isDisabled = true;
      } else if (firstReservedDateAfterCheckIn.value) {
        // 途中に予約がある場合、その予約開始日より後のチェックアウトは不可
        // (例: 5日が予約済みなら、5日チェックアウトはOKだが6日以降はNG)
        isDisabled = dateStr > firstReservedDateAfterCheckIn.value;
      }
    }

    cells.push({
      day: d,
      dateStr,
      disabled: isDisabled,
      isToday: dateStr === todayStr,
      isSunday: dow === 0,
      isSaturday: dow === 6,
    });
  }
  
  return cells;
});

// 日付選択ロジック
const selectDate = (dateStr) => {
  if (isSelectingCheckIn.value) {
    // チェックイン選択
    // すでに予約・ブロックされている日は選択不可（disabledでガードされているはずだが念のため）
    if (props.availability[dateStr]) return;

    emit('update:modelValue', { checkIn: dateStr, checkOut: null });
    isSelectingCheckIn.value = false;
  } else {
    // チェックアウト選択
    if (dateStr <= props.modelValue.checkIn) {
      // チェックイン以前の日付→チェックインをリセット
      if (props.availability[dateStr]) return;
      emit('update:modelValue', { checkIn: dateStr, checkOut: null });
      isSelectingCheckIn.value = false;
    } else {
      // 範囲内に予約・ブロックがあるかチェック
      const ci = props.modelValue.checkIn;
      const hasOverlap = Object.keys(props.availability).some(d => {
        // チェックイン日〜チェックアウト前日までに予約・ブロックがあるか
        return d >= ci && d < dateStr && (props.availability[d].type === 'reserved' || props.availability[d].type === 'blocked');
      });

      if (hasOverlap) {
        // 重複がある場合は選択させない（または警告を表示してリセット）
        return;
      }

      emit('update:modelValue', { checkIn: props.modelValue.checkIn, checkOut: dateStr });
      isSelectingCheckIn.value = true;
    }
  }
};

// 範囲内判定
const isInRange = (dateStr) => {
  const ci = props.modelValue.checkIn;
  const co = props.modelValue.checkOut;
  if (!ci) return false;
  
  if (co) {
    return dateStr >= ci && dateStr <= co;
  }
  
  // チェックアウト未選択時、ホバー中の日付までをハイライト
  if (!isSelectingCheckIn.value && hoverDate.value && hoverDate.value > ci) {
    return dateStr >= ci && dateStr <= hoverDate.value;
  }
  
  return dateStr === ci;
};

// 端点判定
const isEndpoint = (dateStr) => {
  return dateStr === props.modelValue.checkIn || dateStr === props.modelValue.checkOut;
};

// セルのCSSクラス
const cellClass = (cell) => {
  const classes = [];
  const ci = props.modelValue.checkIn;
  const co = props.modelValue.checkOut;
  const avail = props.availability[cell.dateStr];
  
  if (cell.disabled) {
    classes.push('text-slate-200 cursor-not-allowed');
    return classes;
  }
  
  // 予約・ブロック状況に応じた背景
  if (!isInRange(cell.dateStr) && avail && cell.dateStr !== ci && cell.dateStr !== co) {
    if (avail.type === 'reserved') classes.push('bg-emerald-50/50');
    if (avail.type === 'blocked') classes.push('bg-slate-50 opacity-60');
  }

  // チェックイン・チェックアウト日（選択済み）
  if (cell.dateStr === ci || cell.dateStr === co) {
    classes.push('text-white font-bold');
    return classes;
  }
  
  // 範囲内
  if (isInRange(cell.dateStr)) {
    classes.push('text-primary-700 font-medium');
  } else {
    // 通常
    if (cell.isSunday) classes.push('text-red-500');
    else if (cell.isSaturday) classes.push('text-blue-500');
    else classes.push('text-slate-700');
    
    classes.push('hover:bg-primary-50 hover:text-primary-700 hover:font-bold cursor-pointer');
  }
  
  if (cell.isToday && cell.dateStr !== ci && cell.dateStr !== co) {
    classes.push('font-bold border-b-2 border-primary-200 mb-[-2px]');
  }
  
  return classes;
};

// 泊数計算
const nightsCount = computed(() => {
  if (!props.modelValue.checkIn || !props.modelValue.checkOut) return 0;
  const ci = new Date(props.modelValue.checkIn);
  const co = new Date(props.modelValue.checkOut);
  return Math.round((co - ci) / (1000 * 60 * 60 * 24));
});

// チェックイン日が変わったら、その月にカレンダーを移動
watch(() => props.modelValue.checkIn, (newVal) => {
  if (newVal) {
    const d = new Date(newVal);
    currentMonth.value = d.getMonth();
    currentYear.value = d.getFullYear();
  }
});
</script>
