<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import { useI18n } from '@/composables/useI18n.js';

const props = defineProps({
    reservations: Object,
    rooms: Array,
    filters: Object,
    statusLabels: Object,
    platform_fee_rate: Number,
});

const searchQuery = ref(props.filters?.search || '');

const searchSubmit = () => {
    router.get(route('owner.reservations.index'), {
        status: props.filters?.status || 'all',
        search: searchQuery.value,
    }, { preserveState: true });
};

// モーダル制御
const showModal = ref(false);
const showRegisterModal = ref(false);
const targetReservation = ref(null);
const processingCancel = ref(false);
const refundAmount = ref(0);
const cancelWithoutRefund = ref(false);

const formatDateHelper = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
};
const todayStr = formatDateHelper(new Date());
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const tomorrowStr = formatDateHelper(tomorrow);

// 新規予約フォーム
const registerForm = useForm({
    room_id: '',
    check_in_date: todayStr,
    check_out_date: tomorrowStr,
    number_of_guests: 1,
    number_of_adults: 1,
    number_of_child_a: 0,
    number_of_child_b: 0,
    guest_name: '',
    guest_email: '',
    guest_phone: '',
    guest_remarks: '',
    owner_memo: '',
    check_in_time: '未定',
    transportation: '未定',
});

const { t, isEn } = useI18n();

const dateRange = ref({
    checkIn: registerForm.check_in_date,
    checkOut: registerForm.check_out_date,
});

// dateRange が変わったら registerForm に反映
watch(dateRange, (newVal) => {
    if (newVal.checkIn) registerForm.check_in_date = newVal.checkIn;
    if (newVal.checkOut) registerForm.check_out_date = newVal.checkOut;
}, { deep: true });

// モーダルが開いたときや初期化時に dateRange を同期
watch(() => showRegisterModal.value, (newVal) => {
    if (newVal) {
        dateRange.value = {
            checkIn: registerForm.check_in_date,
            checkOut: registerForm.check_out_date,
        };
    }
});

// 選択された部屋の情報
const selectedRoomForRegister = computed(() => {
    if (!registerForm.room_id) return null;
    return props.rooms?.find(r => r.id === registerForm.room_id) || null;
});

// 人数内訳が変更されたら number_of_guests を自動計算
watch([() => registerForm.number_of_adults, () => registerForm.number_of_child_a, () => registerForm.number_of_child_b], () => {
    registerForm.number_of_guests = registerForm.number_of_adults + registerForm.number_of_child_a + registerForm.number_of_child_b;
});

// 登録モード: 'input' (入力) or 'confirm' (確認)
const registerStep = ref('input');
const calculatedPrice = ref(null);
const calculating = ref(false);

// 部屋ごとの空き状況
const roomAvailability = ref({});

const fetchAvailability = async (roomId) => {
    if (!roomId) {
        roomAvailability.value = {};
        return;
    }
    try {
        const room = props.rooms.find(r => r.id === roomId);
        if (!room) return;
        const response = await axios.get(route('owner.rooms.availability', room.uuid));
        roomAvailability.value = response.data;
    } catch (error) {
        console.error('Failed to fetch availability:', error);
    }
};

// 部屋が選択されたら空き状況を取得
watch(() => registerForm.room_id, (newVal) => {
    fetchAvailability(newVal);
});

const goToConfirm = async () => {
    calculating.value = true;
    try {
        const response = await axios.post(route('owner.reservations.calculate-price'), {
            room_id: registerForm.room_id,
            check_in_date: registerForm.check_in_date,
            check_out_date: registerForm.check_out_date,
            number_of_guests: registerForm.number_of_guests,
            number_of_adults: registerForm.number_of_adults,
            number_of_child_a: registerForm.number_of_child_a,
            number_of_child_b: registerForm.number_of_child_b,
        });
        calculatedPrice.value = response.data;
        registerStep.value = 'confirm';
        registerForm.clearErrors();
    } catch (error) {
        console.error('Price calculation failed:', error);
        if (error.response?.data?.errors) {
            registerForm.setError(error.response.data.errors);
        } else if (error.response?.data?.error) {
            // 単一エラーメッセージ
            alert(error.response.data.error);
        }
    } finally {
        calculating.value = false;
    }
};

const submitRegister = () => {
    console.log('Submitting manual reservation:', registerForm.data());
    registerForm.post(route('owner.reservations.store'), {
        onSuccess: () => {
            console.log('Reservation created successfully');
            showRegisterModal.value = false;
            registerForm.reset();
            registerStep.value = 'input';
            calculatedPrice.value = null;
        },
        onError: (errors) => {
            console.error('Reservation creation failed:', errors);
            registerStep.value = 'input';
        }
    });
};

const closeRegisterModal = () => {
    showRegisterModal.value = false;
    setTimeout(() => {
        registerForm.reset();
        registerStep.value = 'input';
        calculatedPrice.value = null;
        roomAvailability.value = {};
    }, 300);
};

const stripeFee = computed(() => {
    if (!targetReservation.value) return 0;
    const total = Number(targetReservation.value.total_amount) || 0;
    return Math.ceil(total * 0.036);
});

const platformFeeReturn = computed(() => {
    // 予約個別のレートを優先し、なければトップレベルのプロップを参照
    const rate = Number(targetReservation.value?.platform_fee_rate || props.platform_fee_rate) || 0;
    const systemRate = Math.max(0, rate - 0.036); // 1.4%相当
    const refund = Number(refundAmount.value) || 0;
    return Math.floor(refund * systemRate);
});

const ownerRemaining = computed(() => {
    if (!targetReservation.value) return 0;
    const a = Number(targetReservation.value.total_amount) || 0;
    const b = Number(stripeFee.value) || 0;
    const c = Number(platformFeeReturn.value) || 0;
    const refund = Number(refundAmount.value) || 0;
    return a - refund - b + c;
});

const openCancelModal = (reservation) => {
    targetReservation.value = reservation;
    refundAmount.value = Number(reservation?.total_amount) || 0;
    showModal.value = true;
};

const closeModal = () => {
    if (processingCancel.value) return;
    showModal.value = false;
    setTimeout(() => { 
        targetReservation.value = null; 
        refundAmount.value = 0;
        cancelWithoutRefund.value = false;
    }, 300);
};

const executeCancel = () => {
    if (!targetReservation.value) return;
    
    processingCancel.value = true;
    router.post(route('owner.reservations.cancel', targetReservation.value.uuid), {
        refund_amount: Number(refundAmount.value) || 0,
        cancel_without_refund: cancelWithoutRefund.value
    }, {
        onFinish: () => {
            processingCancel.value = false;
            closeModal();
        }
    });
};
</script>

<template>
  <OwnerLayout>
    <Head title="予約一覧" />
    <template #header>予約一覧</template>

    <div class="max-w-6xl mx-auto py-6">
      
      <!-- 検索・フィルター -->
      <div v-if="props.filters" class="mb-6 bg-white rounded-xl shadow-sm border border-slate-100 p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0 scrollbar-hide flex-1">
          <Link :href="route('owner.reservations.index', { status: 'all', search: props.filters.search })" 
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition"
                :class="props.filters.status === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
            すべて
          </Link>
          <Link :href="route('owner.reservations.index', { status: 'confirmed', search: props.filters.search })" 
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition"
                :class="props.filters.status === 'confirmed' ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
            確定済み
          </Link>
          <Link :href="route('owner.reservations.index', { status: 'cancelled', search: props.filters.search })" 
                class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition"
                :class="props.filters.status === 'cancelled' ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
            {{ props.statusLabels?.cancelled || '取消' }}
          </Link>
        </div>

        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto items-center">
          <form @submit.prevent="searchSubmit" class="w-full md:w-64 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input type="text" v-model="searchQuery" placeholder="検索..." 
                   class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
          </form>

          <button @click="showRegisterModal = true" 
                  class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-2 bg-primary-600 text-white font-bold text-sm rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-100 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            新規予約を登録
          </button>
        </div>
      </div>

      <!-- テーブル -->
      <div v-if="props.reservations" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 font-medium">
              <tr>
                <th class="px-6 py-4 min-w-[150px]">予約番号 / 日時</th>
                <th class="px-6 py-4 min-w-[120px]">ゲスト名</th>
                <th class="px-6 py-4 min-w-[200px]">宿泊内容</th>
                <th class="px-6 py-4 text-right min-w-[100px]">料金</th>
                <th class="px-6 py-4 min-w-[100px]">ステータス</th>
                <th class="px-6 py-4 text-center min-w-[150px]">操作</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="res in props.reservations.data" :key="res.uuid" class="hover:bg-slate-50 transition group">
                <td class="px-6 py-4">
                  <div class="font-mono text-xs font-bold text-slate-800 bg-slate-100 inline-block px-2 py-1 rounded">{{ res.reservation_code }}</div>
                  <div class="text-xs text-slate-500 mt-2">{{ res.created_at }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-bold text-slate-800">{{ res.guest_name }}</div>
                  <div class="text-xs text-slate-500 mt-1">利用人数: {{ res.number_of_guests }}名</div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-medium text-primary-900">{{ res.room_name }}</div>
                  <div class="text-xs text-slate-500 mt-1 whitespace-nowrap">{{ res.check_in_date }} 〜 {{ res.check_out_date }}</div>
                  <div v-if="res.guest_remarks" class="text-[10px] text-slate-400 mt-1 line-clamp-1 italic" :title="res.guest_remarks">
                    備考: {{ res.guest_remarks }}
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="font-bold text-slate-800">¥{{ (Number(res.total_amount) || 0).toLocaleString() }}</div>
                </td>
                <td class="px-6 py-4">
                  <span v-if="res.status === 'confirmed'" class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-primary-100 text-primary-800">確 定</span>
                  <span v-else-if="res.status === 'cancelled'" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-500">{{ props.statusLabels?.cancelled || '取消' }}</span>
                  <span v-else-if="res.status === 'refunded'" class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800">返金済み</span>
                  <span v-else class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-800">{{ res.status }}</span>
                  
                  <!-- 決済方法バッジ -->
                  <div class="mt-1 flex gap-1">
                    <span v-if="res.payment_method === 'stripe'" class="text-[9px] font-bold text-slate-400 border border-slate-200 px-0.5 rounded">オンライン決済</span>
                    <span v-else-if="res.payment_method === 'onsite'" class="text-[9px] font-bold text-emerald-600 border border-emerald-200 px-0.5 rounded bg-emerald-50">現地決済</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center space-x-2">
                  <Link :href="route('owner.reservations.show', res.uuid)" 
                        class="inline-block px-3 py-1.5 text-xs font-semibold text-primary-600 border border-primary-200 rounded-lg hover:bg-primary-50 transition">
                    詳細
                  </Link>
                  <button v-if="res.status === 'confirmed'" @click="openCancelModal(res)" 
                          class="px-3 py-1.5 text-xs font-semibold text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                    {{ props.statusLabels?.cancelled || '取消' }}
                  </button>
                </td>
              </tr>
              <tr v-if="props.reservations.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                  該当する予約が見つかりませんでした。
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- ページネーション (簡易実装) -->
        <div v-if="props.reservations.links && props.reservations.links.length > 3" class="px-6 py-4 border-t border-slate-100 flex justify-center">
          <div class="flex flex-wrap gap-1">
            <template v-for="(link, i) in props.reservations.links" :key="i">
              <Link v-if="link.url" :href="link.url" class="px-3 py-1 rounded border text-sm transition"
                    :class="link.active ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    v-html="link.label"></Link>
              <span v-else class="px-3 py-1 rounded border border-slate-200 text-slate-400 text-sm bg-slate-50" v-html="link.label"></span>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- 取消確認モーダル (Standard Tailwind Pattern) -->
    <div v-if="showModal" class="relative z-50">
      <div class="fixed inset-0 bg-slate-900 bg-opacity-50 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
            <div class="bg-white px-6 pt-6 pb-4">
              <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-50">
                  <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">予約の{{ props.statusLabels?.cancelled || '取消' }}</h3>
              </div>
              <!-- ... (取消モーダルの既存内容、省略可能だが全体整合性のために維持) ... -->
                <div class="space-y-4 text-sm text-slate-500 leading-relaxed">
                  <p v-if="targetReservation?.payment_method === 'stripe'">
                    ゲストへの金銭的な返金内容を決定してください。この操作によりStripe経由で返金が実行されます。
                    <span v-if="$page.props.auth.is_stripe_connected">ゲストに案内が送信されます。</span>
                    <span v-else class="text-amber-600 font-bold">プレビューモード中のため、メールは送信されません。</span>
                  </p>
                  <p v-else>
                    現地決済の予約を取り消します。返金処理は不要です。
                    <span v-if="$page.props.auth.is_stripe_connected">ゲストに取消メールが送信されます。</span>
                    <span v-else class="text-amber-600 font-bold">プレビューモード中のため、メールは送信されません。</span>
                  </p>

                  <div v-if="targetReservation?.payment_method === 'stripe'" class="space-y-4">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                      <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" v-model="cancelWithoutRefund" @change="cancelWithoutRefund && (refundAmount = 0)"
                               class="h-5 w-5 rounded border-slate-300 text-primary-600 cursor-pointer" />
                        <div class="flex flex-col">
                          <span class="text-sm font-bold text-slate-700">返金せずに取消（キャンセル料100%）</span>
                        </div>
                      </label>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200" :class="{'opacity-50': cancelWithoutRefund}">
                      <label class="block text-xs font-bold text-slate-500 mb-2">返金する金額 (円)</label>
                      <input type="number" v-model="refundAmount" :max="targetReservation?.total_amount" min="0" :disabled="cancelWithoutRefund"
                             class="block w-full px-4 py-2 border-slate-200 rounded-xl font-bold" />
                    </div>
                  </div>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 flex flex-col gap-2">
              <button @click="executeCancel" :disabled="processingCancel"
                      class="w-full py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition disabled:opacity-50">
                <span v-if="processingCancel">処理中...</span>
                <span v-else>予約を確定して取り消す</span>
              </button>
              <button @click="closeModal" class="w-full py-3 bg-white text-slate-600 font-medium rounded-xl border border-slate-200 hover:bg-slate-50 transition">閉じる</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 手動予約登録モーダル -->
    <div v-if="showRegisterModal" class="relative z-50">
      <div class="fixed inset-0 bg-slate-900 bg-opacity-50 backdrop-blur-sm" @click="showRegisterModal = false"></div>
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-100">
            <div class="bg-white px-8 pt-8 pb-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ registerStep === 'input' ? '手動予約の登録' : '予約内容の確認' }}
                </h3>
                <button @click="closeRegisterModal" class="text-slate-400 hover:text-slate-600 transition p-2 hover:bg-slate-100 rounded-full">
                  <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>

              <!-- ステップ1: 入力画面 -->
              <form v-if="registerStep === 'input'" @submit.prevent="goToConfirm" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">宿泊する部屋 *</label>
                    <select v-model="registerForm.room_id" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" :class="{'border-red-500': registerForm.errors.room_id}">
                      <option value="" disabled>部屋を選択してください</option>
                      <option v-for="room in props.rooms" :key="room.uuid" :value="room.id">{{ room.name }} (定員:{{ room.capacity }}名)</option>
                    </select>
                    <p v-if="registerForm.errors.room_id" class="mt-1 text-xs text-red-500">{{ registerForm.errors.room_id }}</p>
                  </div>
                  <div class="sm:col-span-2">
                    <DateRangePicker
                      v-model="dateRange"
                      :check-in-label="t('check_in')"
                      :check-out-label="t('check_out')"
                      :nights-label="isEn ? 'night(s)' : '泊'"
                      :availability="roomAvailability"
                    />
                    <div v-if="registerForm.errors.check_in_date || registerForm.errors.check_out_date" class="mt-2 space-y-1">
                      <p v-if="registerForm.errors.check_in_date" class="text-xs text-red-500">{{ registerForm.errors.check_in_date }}</p>
                      <p v-if="registerForm.errors.check_out_date" class="text-xs text-red-500">{{ registerForm.errors.check_out_date }}</p>
                    </div>
                  </div>
                  <!-- 大人カウンター -->
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">大人</label>
                    <div class="flex items-center gap-3">
                      <button type="button" @click="registerForm.number_of_adults = Math.max(1, registerForm.number_of_adults - 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                              :disabled="registerForm.number_of_adults <= 1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                      </button>
                      <span class="w-8 text-center font-bold text-slate-800">{{ registerForm.number_of_adults }}</span>
                      <button type="button" @click="registerForm.number_of_adults = Math.min(10, registerForm.number_of_adults + 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                      </button>
                    </div>
                    <p v-if="registerForm.errors.number_of_guests" class="mt-1 text-xs text-red-500">{{ registerForm.errors.number_of_guests }}</p>
                  </div>
                  <!-- 子供Aカウンター -->
                  <div v-if="selectedRoomForRegister?.child_a_label">
                    <label class="block text-xs font-bold text-slate-500 mb-2">
                      {{ selectedRoomForRegister.child_a_label }}
                      <span v-if="selectedRoomForRegister.child_a_policy" class="text-xs text-slate-400 font-normal ml-1">{{ selectedRoomForRegister.child_a_policy }}</span>
                    </label>
                    <div class="flex items-center gap-3">
                      <button type="button" @click="registerForm.number_of_child_a = Math.max(0, registerForm.number_of_child_a - 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                              :disabled="registerForm.number_of_child_a <= 0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                      </button>
                      <span class="w-8 text-center font-bold text-slate-800">{{ registerForm.number_of_child_a }}</span>
                      <button type="button" @click="registerForm.number_of_child_a = Math.min(10, registerForm.number_of_child_a + 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                      </button>
                    </div>
                  </div>
                  <!-- 子供Bカウンター -->
                  <div v-if="selectedRoomForRegister?.child_b_label">
                    <label class="block text-xs font-bold text-slate-500 mb-2">
                      {{ selectedRoomForRegister.child_b_label }}
                      <span v-if="selectedRoomForRegister.child_b_policy" class="text-xs text-slate-400 font-normal ml-1">{{ selectedRoomForRegister.child_b_policy }}</span>
                    </label>
                    <div class="flex items-center gap-3">
                      <button type="button" @click="registerForm.number_of_child_b = Math.max(0, registerForm.number_of_child_b - 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                              :disabled="registerForm.number_of_child_b <= 0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                      </button>
                      <span class="w-8 text-center font-bold text-slate-800">{{ registerForm.number_of_child_b }}</span>
                      <button type="button" @click="registerForm.number_of_child_b = Math.min(10, registerForm.number_of_child_b + 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                      </button>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">ゲスト名</label>
                    <input type="text" v-model="registerForm.guest_name" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" />
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">メール</label>
                    <input type="email" v-model="registerForm.guest_email" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" :class="{'border-red-500': registerForm.errors.guest_email}" />
                    <p v-if="registerForm.errors.guest_email" class="mt-1 text-xs text-red-500">{{ registerForm.errors.guest_email }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">電話番号</label>
                    <input type="tel" v-model="registerForm.guest_phone" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" :class="{'border-red-500': registerForm.errors.guest_phone}" />
                    <p v-if="registerForm.errors.guest_phone" class="mt-1 text-xs text-red-500">{{ registerForm.errors.guest_phone }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">{{ t('arrival_time_label') }}</label>
                    <select v-model="registerForm.check_in_time" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                      <option value="未定">未定</option>
                      <option value="15:00">15:00</option>
                      <option value="15:30">15:30</option>
                      <option value="16:00">16:00</option>
                      <option value="16:30">16:30</option>
                      <option value="17:00">17:00</option>
                      <option value="17:30">17:30</option>
                      <option value="18:00">18:00</option>
                      <option value="18:30">18:30</option>
                      <option value="19:00">19:00</option>
                      <option value="19:30">19:30</option>
                      <option value="20:00">20:00</option>
                      <option value="20:30">20:30</option>
                      <option value="21:00">21:00</option>
                      <option value="21:30">21:30</option>
                      <option value="22:00">22:00</option>
                      <option value="22:30">22:30</option>
                      <option value="23:00">23:00</option>
                      <option value="23:30">23:30</option>
                      <option value="00:00">00:00</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">{{ t('transportation_label') }}</label>
                    <select v-model="registerForm.transportation" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                      <option value="未定">未定</option>
                      <option value="車">車</option>
                      <option value="電車・バス">電車・バス</option>
                      <option value="その他">その他</option>
                    </select>
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 mb-2">ゲスト備考（お客様へのメッセージ／メールに表示されます）</label>
                    <textarea v-model="registerForm.guest_remarks" rows="2" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-primary-600 mb-2">オーナーメモ (スタッフ用)</label>
                    <textarea v-model="registerForm.owner_memo" rows="2" class="block w-full px-4 py-2 border border-primary-100 rounded-xl leading-5 bg-primary-50/10 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-col gap-3">
                  <div v-if="registerForm.errors.length > 0 || $page.props.flash?.error" class="bg-red-50 p-4 rounded-2xl border border-red-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-red-700 leading-normal">{{ $page.props.flash.error || '入力内容を確認してください' }}</p>
                  </div>

                  <button type="submit" :disabled="calculating"
                          class="w-full py-4 bg-primary-600 text-white font-black rounded-2xl hover:bg-primary-700 transition shadow-xl shadow-primary-200 disabled:opacity-50">
                    <span v-if="calculating">料金計算中...</span>
                    <span v-else>確認画面へ</span>
                  </button>
                </div>
              </form>

              <!-- ステップ2: 確認画面 -->
              <div v-else class="space-y-6">
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 space-y-4">
                    <div class="flex justify-between items-start border-b border-slate-200 pb-4">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">宿泊期間</p>
                            <p class="font-bold text-slate-800">{{ registerForm.check_in_date }} 〜 {{ registerForm.check_out_date }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">人数</p>
                            <p class="font-bold text-slate-800">
                              大人 {{ registerForm.number_of_adults }}名
                              <template v-if="registerForm.number_of_child_a > 0"> / {{ selectedRoomForRegister?.child_a_label || '子供A' }} {{ registerForm.number_of_child_a }}名</template>
                              <template v-if="registerForm.number_of_child_b > 0"> / {{ selectedRoomForRegister?.child_b_label || '子供B' }} {{ registerForm.number_of_child_b }}名</template>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 py-4 border-b border-slate-200">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">ゲスト名</p>
                            <p class="font-bold text-slate-800">{{ registerForm.guest_name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">連絡先</p>
                            <p class="text-sm text-slate-800">{{ registerForm.guest_email || '(メール未入力)' }}</p>
                            <p class="text-sm text-slate-800">{{ registerForm.guest_phone }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">到着予定時刻</p>
                            <p class="font-bold text-slate-800">{{ registerForm.check_in_time }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">交通手段</p>
                            <p class="font-bold text-slate-800">{{ registerForm.transportation }}</p>
                        </div>
                    </div>

                    <div v-if="calculatedPrice" class="space-y-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">料金内訳</p>
                        <div v-for="day in calculatedPrice.daily_prices" :key="day.date" class="text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">{{ day.date }} ({{ day.label }})</span>
                                <span class="font-mono text-slate-800">¥{{ (day.dayTotal || day.price).toLocaleString() }}</span>
                            </div>
                            <div v-if="calculatedPrice.pricing_type === 'person'" class="ml-4 text-xs text-slate-400 space-y-0.5 mt-0.5">
                                <div class="flex justify-between">
                                    <span>大人 {{ calculatedPrice.adults }}名 × ¥{{ day.price.toLocaleString() }}</span>
                                    <span class="font-mono">¥{{ (calculatedPrice.adults * day.price).toLocaleString() }}</span>
                                </div>
                                <div v-if="calculatedPrice.child_a > 0 && calculatedPrice.add_child_a_fee > 0" class="flex justify-between">
                                    <span>{{ calculatedPrice.child_a_label || '子供A' }} {{ calculatedPrice.child_a }}名 × ¥{{ calculatedPrice.add_child_a_fee.toLocaleString() }}</span>
                                    <span class="font-mono">¥{{ (calculatedPrice.child_a * calculatedPrice.add_child_a_fee).toLocaleString() }}</span>
                                </div>
                                <div v-if="calculatedPrice.child_a > 0 && (calculatedPrice.add_child_a_fee || 0) === 0" class="flex justify-between">
                                    <span>{{ calculatedPrice.child_a_label || '子供A' }} {{ calculatedPrice.child_a }}名</span>
                                    <span class="font-mono text-emerald-500">無料</span>
                                </div>
                                <div v-if="calculatedPrice.child_b > 0 && calculatedPrice.add_child_b_fee > 0" class="flex justify-between">
                                    <span>{{ calculatedPrice.child_b_label || '子供B' }} {{ calculatedPrice.child_b }}名 × ¥{{ calculatedPrice.add_child_b_fee.toLocaleString() }}</span>
                                    <span class="font-mono">¥{{ (calculatedPrice.child_b * calculatedPrice.add_child_b_fee).toLocaleString() }}</span>
                                </div>
                                <div v-if="calculatedPrice.child_b > 0 && (calculatedPrice.add_child_b_fee || 0) === 0" class="flex justify-between">
                                    <span>{{ calculatedPrice.child_b_label || '子供B' }} {{ calculatedPrice.child_b }}名</span>
                                    <span class="font-mono text-emerald-500">無料</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="calculatedPrice.cleaning_fee > 0" class="flex justify-between text-sm pt-2 border-t border-slate-100">
                            <span class="text-slate-600">清掃費</span>
                            <span class="font-mono text-slate-800">¥{{ calculatedPrice.cleaning_fee.toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-slate-200">
                            <span class="font-black text-slate-900">合計金額</span>
                            <span class="text-2xl font-black text-primary-600 font-mono">¥{{ calculatedPrice.total_amount.toLocaleString() }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                  <button @click="registerStep = 'input'" 
                          class="py-4 bg-white text-slate-600 font-bold rounded-2xl border border-slate-200 hover:bg-slate-50 transition">
                    戻って修正
                  </button>
                  <button @click="submitRegister" :disabled="registerForm.processing"
                          class="py-4 bg-primary-600 text-white font-black rounded-2xl hover:bg-primary-700 transition shadow-xl shadow-primary-200 disabled:opacity-50">
                    <span v-if="registerForm.processing">登録中...</span>
                    <span v-else>確定して登録する</span>
                  </button>
                </div>

                <div v-if="$page.props.auth.is_stripe_connected" class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 text-[11px] text-emerald-700 leading-normal text-center">
                    現地決済として登録されます。<template v-if="registerForm.guest_email">予約確定メールがゲストに送信されます。</template>
                    <template v-else>メールアドレスが未入力のため、メールは送信されません。</template>
                </div>
                <div v-else class="bg-amber-50 p-4 rounded-2xl border border-amber-100 text-[11px] text-amber-700 leading-normal text-center">
                    現地決済として登録されます。プレビューモード中のため、メールは送信されません。
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </OwnerLayout>
</template>
