<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    reservation: Object,
    platform_fee_rate: Number,
});

const memoForm = useForm({
    owner_memo: props.reservation?.owner_memo || '',
});

// サーバー側でオーナーメモが更新されたら同期する
watch(() => props.reservation?.owner_memo, (newMemo) => {
    memoForm.owner_memo = newMemo || '';
});

const saveMemo = () => {
    memoForm.put(route('owner.reservations.update', props.reservation?.uuid), {
        preserveScroll: true,
    });
};

// モーダル制御
const showModal = ref(false);
const processingCancel = ref(false);
const refundAmount = ref(Number(props.reservation?.total_amount) || 0);
const cancelWithoutRefund = ref(false);

const stripeFee = computed(() => {
    if (!props.reservation) return 0;
    const total = Number(props.reservation.total_amount) || 0;
    return Math.ceil(total * 0.036);
});

const platformFeeReturn = computed(() => {
    const rate = Number(props.reservation?.platform_fee_rate) || 0;
    const systemRate = Math.max(0, rate - 0.036); // 1.4%相当（手数料5%の場合）
    const refund = Number(refundAmount.value) || 0;
    return Math.floor(refund * systemRate);
});

const ownerRemaining = computed(() => {
    if (!props.reservation) return 0;
    const a = Number(props.reservation.total_amount) || 0;
    const b = Number(stripeFee.value) || 0;
    const c = Number(platformFeeReturn.value) || 0;
    const refund = Number(refundAmount.value) || 0;
    return a - refund - b + c;
});

const openCancelModal = () => {
    refundAmount.value = Number(props.reservation?.total_amount) || 0;
    showModal.value = true;
};

const closeModal = () => {
    if (processingCancel.value) return;
    showModal.value = false;
    cancelWithoutRefund.value = false;
};

const executeCancel = () => {
    if (!props.reservation) return;
    processingCancel.value = true;
    router.post(route('owner.reservations.cancel', props.reservation.uuid), {
        refund_amount: Number(refundAmount.value) || 0,
        cancel_without_refund: cancelWithoutRefund.value
    }, {
        onFinish: () => {
            processingCancel.value = false;
            closeModal();
        }
    });
};

// 編集モーダル制御
const showEditModal = ref(false);
const editForm = useForm({
    guest_name: props.reservation?.guest_name || '',
    guest_email: props.reservation?.guest_email || '',
    guest_phone: props.reservation?.guest_phone || '',
    number_of_adults: props.reservation?.number_of_adults || 1,
    number_of_child_a: props.reservation?.number_of_child_a || 0,
    number_of_child_b: props.reservation?.number_of_child_b || 0,
    check_in_time: props.reservation?.check_in_time || '未定',
    transportation: props.reservation?.transportation || '未定',
    remarks: '',
});

const openEditModal = () => {
    editForm.guest_name = props.reservation?.guest_name || '';
    editForm.guest_email = props.reservation?.guest_email || '';
    editForm.guest_phone = props.reservation?.guest_phone || '';
    editForm.number_of_adults = props.reservation?.number_of_adults || 1;
    editForm.number_of_child_a = props.reservation?.number_of_child_a || 0;
    editForm.number_of_child_b = props.reservation?.number_of_child_b || 0;
    editForm.check_in_time = props.reservation?.check_in_time || '未定';
    editForm.transportation = props.reservation?.transportation || '未定';
    editForm.remarks = '';
    showEditModal.value = true;
};

const updateReservation = () => {
    editForm.put(route('owner.reservations.update', props.reservation?.uuid), {
        onSuccess: () => {
            showEditModal.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<template>
  <OwnerLayout>
    <Head title="予約詳細" />
    <template #header>
      <div class="flex items-center gap-4">
        <Link :href="route('owner.reservations.index')" class="p-2 bg-white rounded-full text-slate-400 hover:text-slate-600 shadow-sm border border-slate-100 transition">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </Link>
        <span>予約詳細: {{ props.reservation?.reservation_code }}</span>
      </div>
    </template>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6">
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- 左側：予約・ゲスト情報 -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- 基本情報 -->
          <div v-if="props.reservation" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 text-slate-800">
              <h3 class="font-bold whitespace-nowrap">予約・ゲスト情報</h3>
              <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <button @click="openEditModal" class="px-3 py-1.5 bg-white border border-slate-200 text-xs font-bold text-primary-600 rounded-lg hover:bg-primary-50 hover:border-primary-100 transition flex items-center gap-1.5 shadow-sm">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  編集
                </button>
                <div class="flex flex-wrap gap-2 items-center">
                  <span v-if="props.reservation.payment_method === 'stripe'" class="px-2 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-tighter whitespace-nowrap">オンライン決済</span>
                  <span v-else-if="props.reservation.payment_method === 'onsite'" class="px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase tracking-tighter whitespace-nowrap">現地決済</span>
                  
                  <span v-if="props.reservation.status === 'confirmed'" class="px-3 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-800 whitespace-nowrap">確定済み</span>
                  <span v-else-if="props.reservation.status === 'cancelled'" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 whitespace-nowrap">取消</span>
                  <span v-else-if="props.reservation.status === 'refunded'" class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 whitespace-nowrap">返金済み</span>
                </div>
              </div>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                <div>
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">ゲスト名</dt>
                  <dd class="text-slate-900 font-bold">{{ props.reservation.guest_name }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">メールアドレス</dt>
                  <dd class="text-slate-900">{{ props.reservation.guest_email || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">電話番号</dt>
                  <dd class="text-slate-900">{{ props.reservation.guest_phone }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">利用人数</dt>
                  <dd class="text-slate-900">
                    <template v-if="props.reservation.number_of_child_a > 0 || props.reservation.number_of_child_b > 0">
                      大人 {{ props.reservation.number_of_adults }} 名
                      <template v-if="props.reservation.number_of_child_a > 0">
                        / {{ props.reservation.child_a_label || '子供A' }} {{ props.reservation.number_of_child_a }} 名
                      </template>
                      <template v-if="props.reservation.number_of_child_b > 0">
                        / {{ props.reservation.child_b_label || '子供B' }} {{ props.reservation.number_of_child_b }} 名
                      </template>
                    </template>
                    <template v-else>
                      {{ props.reservation.number_of_guests }} 名
                    </template>
                  </dd>
                </div>
                <div class="sm:col-span-2 border-t border-slate-50 pt-4">
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">宿泊プラン・部屋</dt>
                  <dd class="text-primary-900 font-bold text-lg">{{ props.reservation.room_name }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">チェックイン</dt>
                  <dd class="text-slate-900 font-bold">{{ props.reservation.check_in_date }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">チェックアウト</dt>
                  <dd class="text-slate-900 font-bold">{{ props.reservation.check_out_date }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">到着予定時刻</dt>
                  <dd class="text-slate-900 font-bold">{{ props.reservation.check_in_time }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">交通手段</dt>
                  <dd class="text-slate-900 font-bold">{{ props.reservation.transportation }}</dd>
                </div>
                <div v-if="props.reservation.guest_remarks" class="sm:col-span-2 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                  <dt class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">ゲストからの備考</dt>
                  <dd class="text-slate-700 text-sm whitespace-pre-line leading-relaxed italic">
                    {{ props.reservation.guest_remarks }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- 支払い明細 -->
          <div v-if="props.reservation" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50">
              <h3 class="font-bold text-slate-800">支払い明細</h3>
            </div>
            <div class="p-6">
              <table class="w-full text-sm text-slate-600">
                <tbody class="divide-y divide-slate-50">
                  <template v-for="(detail, date) in props.reservation.booked_price_details" :key="date">
                    <tr>
                      <td class="py-3">
                        <div class="font-bold text-slate-700">宿泊代金 {{ detail.date || date }}</div>
                        <div class="text-[10px] text-slate-400">{{ detail.label }}</div>
                      </td>
                      <td class="py-3 text-right font-medium">
                        ¥{{ (Number(detail.dayTotal || detail.price) || 0).toLocaleString() }}
                      </td>
                    </tr>
                    <tr v-if="props.reservation.pricing_type === 'person'" class="bg-slate-50/30 text-[11px]">
                      <td colspan="2" class="px-4 py-2 space-y-1 border-b border-slate-50">
                        <div class="flex justify-between text-slate-500">
                          <span>大人 {{ props.reservation.number_of_adults }}名 × ¥{{ (Number(detail.price || 0)).toLocaleString() }}</span>
                          <span>¥{{ (Number(detail.price || 0) * (Number(props.reservation.number_of_adults) || 1)).toLocaleString() }}</span>
                        </div>
                        <div v-if="props.reservation.number_of_child_a > 0" class="flex justify-between text-slate-500">
                          <span>{{ props.reservation.child_a_label || '子供A' }} {{ props.reservation.number_of_child_a }}名 × ¥{{ (Number(detail.addChildAFee || 0)).toLocaleString() }}</span>
                          <span>¥{{ (Number(detail.addChildAFee || 0) * Number(props.reservation.number_of_child_a || 0)).toLocaleString() }}</span>
                        </div>
                        <div v-if="props.reservation.number_of_child_b > 0" class="flex justify-between text-slate-500">
                          <span>{{ props.reservation.child_b_label || '子供B' }} {{ props.reservation.number_of_child_b }}名 × ¥{{ (Number(detail.addChildBFee || 0)).toLocaleString() }}</span>
                          <span>¥{{ (Number(detail.addChildBFee || 0) * Number(props.reservation.number_of_child_b || 0)).toLocaleString() }}</span>
                        </div>
                      </td>
                    </tr>
                  </template>
                  <tr v-if="props.reservation.booked_cleaning_fee > 0">
                    <td class="py-3">清掃費</td>
                    <td class="py-3 text-right font-medium">¥{{ (Number(props.reservation.booked_cleaning_fee) || 0).toLocaleString() }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="border-t-2 border-slate-100">
                    <th class="py-4 text-left font-bold text-slate-900">合計金額 (税込)</th>
                    <td class="py-4 text-right font-extrabold text-2xl text-slate-900 tracking-tight">
                      ¥{{ (Number(props.reservation.total_amount) || 0).toLocaleString() }}
                    </td>
                  </tr>
                </tfoot>
              </table>

              <!-- 返金詳細 (返金済みの場合のみ表示) -->
              <div v-if="props.reservation.status === 'refunded'" class="mt-8 pt-6 border-t border-slate-100">
                <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                  <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                  返金・精算内容
                </h4>
                <div class="bg-amber-50/50 rounded-2xl p-5 space-y-3 border border-amber-100/50">
                  <div class="flex justify-between text-xs">
                    <span class="text-slate-500">顧客への返金額</span>
                    <span class="font-bold text-red-600">-¥{{ (Number(props.reservation.refunded_amount) || 0).toLocaleString() }}</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-slate-500">Stripe決済手数料 (3.6%・返還不可)</span>
                    <span class="font-medium text-slate-600">-¥{{ (Number(props.reservation.stripe_fee) || 0).toLocaleString() }}</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-slate-500">システム利用料の戻り</span>
                    <span class="font-medium text-emerald-600">+¥{{ (Number(props.reservation.platform_fee_refund_amount) || 0).toLocaleString() }}</span>
                  </div>
                  <div class="pt-3 mt-1 border-t border-amber-200/50 flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-700">オーナー収支差引額</span>
                    <span class="text-lg font-black" :class="(Number(props.reservation.owner_net_amount) || 0) >= 0 ? 'text-primary-700' : 'text-red-700'">
                      ¥{{ (Number(props.reservation.owner_net_amount) || 0).toLocaleString() }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="mt-4 pt-4 border-t border-slate-50 flex flex-col sm:flex-row justify-between items-start sm:items-center text-[10px] text-slate-400 gap-2">
                <div class="flex gap-4">
                  <span v-if="props.reservation.stripe_payment_intent_id">決済ID: {{ props.reservation.stripe_payment_intent_id }}</span>
                  <span v-if="props.reservation.booking_source === 'manual'" class="font-bold text-emerald-600">手動登録</span>
                </div>
                <span>予約作成日: {{ props.reservation.created_at }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 右側：オーナーメモ -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white rounded-3xl shadow-lg border border-primary-100 overflow-hidden sticky top-8">
            <div class="px-6 py-4 border-b border-primary-50 bg-primary-50/30 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              <h3 class="font-bold text-slate-800">オーナーメモ</h3>
            </div>
            <div class="p-6">
              <form @submit.prevent="saveMemo" class="space-y-4">
                <label class="block text-xs font-bold text-primary-600 mb-2">メモ内容</label>
                <textarea v-model="memoForm.owner_memo" rows="10" 
                          placeholder="この予約に関するメモを残せます（ゲストの好みや対応履歴など）。"
                          class="block w-full px-4 py-2 border border-primary-100 rounded-2xl leading-5 bg-primary-50/10 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 text-sm transition"></textarea>
                
                <div v-if="memoForm.recentlySuccessful" class="text-xs text-emerald-600 font-bold flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  メモを保存しました
                </div>

                <button type="submit" :disabled="memoForm.processing"
                        class="w-full py-3 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-200 disabled:opacity-50">
                  <span v-if="memoForm.processing">保存中...</span>
                  <span v-else>メモを保存する</span>
                </button>
              </form>
            </div>
          </div>

          <!-- 取消ボタン（補助） -->
          <div v-if="props.reservation?.status === 'confirmed'" class="p-6 bg-red-50 rounded-3xl border border-red-100">
            <h4 class="text-sm font-bold text-red-800 mb-2">予約の取消</h4>
            <p v-if="props.reservation.payment_method === 'stripe'" class="text-xs text-red-600 mb-4 leading-relaxed">この予約を取り消し、Stripe経由でゲストに返金します。この操作は取り消せません。</p>
            <p v-else class="text-xs text-red-600 mb-4 leading-relaxed">この予約を取り消します。この操作は取り消せません。</p>
            <button @click="openCancelModal" class="w-full py-2 bg-white text-red-600 font-bold text-xs border border-red-200 rounded-xl hover:bg-red-100 transition">
              {{ props.reservation.payment_method === 'stripe' ? '取消・返金を実行' : '予約の取消を実行' }}
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- キャンセル確認モーダル (Standard Tailwind Pattern) -->
    <div v-if="showModal && props.reservation" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <!-- 背景オーバーレイ -->
      <div class="fixed inset-0 bg-slate-900 bg-opacity-50 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <!-- モーダルコンテンツ -->
          <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100">
            <div class="bg-white px-6 pt-6 pb-4">
              <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-50">
                  <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900" id="modal-title">予約の取消</h3>
              </div>

              <div class="space-y-4">
                <p class="text-sm text-slate-500 leading-relaxed">
                  <span v-if="props.reservation.payment_method === 'stripe'">
                    ゲストへの金銭的な返金内容を決定してください。この操作によりStripe経由で返金が実行されます。
                    <span v-if="$page.props.auth.is_stripe_connected">ゲストに案内が送信されます。</span>
                    <span v-else class="text-amber-600 font-bold">プレビューモード中のため、メールは送信されません。</span>
                  </span>
                  <span v-else>
                    現地決済の予約を取り消します。返金処理は不要です。
                    <span v-if="$page.props.auth.is_stripe_connected">ゲストに取消メールが送信されます。</span>
                    <span v-else class="text-amber-600 font-bold">プレビューモード中のため、メールは送信されません。</span>
                  </span>
                </p>

                <!-- 返金なしチェックボックス (Stripeのみ) -->
                <div v-if="props.reservation.payment_method === 'stripe'" class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                  <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative flex items-center">
                      <input type="checkbox" v-model="cancelWithoutRefund" @change="cancelWithoutRefund && (refundAmount = 0)"
                             class="h-5 w-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 transition cursor-pointer" />
                    </div>
                    <div class="flex flex-col">
                      <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition">返金せずに取消（キャンセル料100%）</span>
                      <span class="text-[10px] text-slate-400">※Stripe経由の返金処理を行わず、予約のみを取り消します</span>
                    </div>
                  </label>
                </div>

                <!-- 返金額入力 -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200" :class="{'opacity-50': cancelWithoutRefund}">
                  <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">返金する金額 (円)</label>
                  <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-bold">¥</span>
                    <input type="number" v-model="refundAmount" :max="Number(props.reservation.total_amount) || 0" min="0" :disabled="cancelWithoutRefund"
                           class="block w-full pl-8 pr-4 py-3 bg-white border border-slate-200 rounded-xl font-bold text-lg text-slate-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition disabled:bg-slate-100" />
                  </div>
                  <p class="text-[10px] text-slate-400 mt-2">※最大: ¥{{ (Number(props.reservation.total_amount) || 0).toLocaleString() }}</p>
                </div>

                <!-- 計算プレビュー (Stripeのみ) -->
                <div v-if="props.reservation.payment_method === 'stripe'" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-2 border-l-4 border-l-primary-500">
                  <div class="flex justify-between text-xs">
                    <span class="text-slate-500">(A) 予約時の決済額</span>
                    <span class="font-medium text-slate-700">¥{{ (Number(props.reservation.total_amount) || 0).toLocaleString() }}</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-slate-500">(B) Stripe決済手数料 <span class="text-[10px]">(3.6%・返還不可)</span></span>
                    <span class="font-medium text-red-500">-¥{{ (Number(stripeFee) || 0).toLocaleString() }}</span>
                  </div>
                  <div class="flex justify-between text-xs">
                    <span class="text-slate-500">(C) システム利用料の戻り <span class="text-[10px]">({{ (Math.max(0, (Number(props.reservation?.platform_fee_rate) || 0) - 0.036) * 100).toFixed(1) }}%)</span></span>
                    <span class="font-medium text-emerald-500">+¥{{ (Number(platformFeeReturn) || 0).toLocaleString() }}</span>
                  </div>
                  <div class="flex justify-between text-xs font-bold pt-2 border-t border-slate-100">
                    <span class="text-slate-600">返金額 (Stripeから顧客へ)</span>
                    <span class="text-red-600">-¥{{ (Number(refundAmount) || 0).toLocaleString() }}</span>
                  </div>
                  
                  <div class="mt-4 p-3 rounded-xl flex justify-between items-center transition"
                       :class="(Number(ownerRemaining) || 0) >= 0 ? 'bg-primary-50/50' : 'bg-red-50'">
                    <span class="text-sm font-bold" :class="(Number(ownerRemaining) || 0) >= 0 ? 'text-primary-900' : 'text-red-900'">
                      今回の取消でオーナーの口座に残る最終金額
                    </span>
                    <span class="text-lg font-black" :class="(Number(ownerRemaining) || 0) >= 0 ? 'text-primary-600' : 'text-red-600'">
                      ¥{{ (Number(ownerRemaining) || 0).toLocaleString() }}
                    </span>
                  </div>

                  <div v-if="(Number(ownerRemaining) || 0) < 0" class="flex gap-2 items-start mt-2 p-3 bg-red-100/50 rounded-xl text-[11px] text-red-700 leading-tight">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>
                      最終金額がマイナスです。この差額分は、次回の入金サイクルにおいてオーナーの売上から差し引かれます。
                    </span>
                  </div>
                </div>
                <div v-else class="bg-amber-50 p-4 rounded-2xl border border-amber-100 flex items-start gap-3">
                  <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="text-xs text-amber-700 leading-normal">
                    現地決済の予約です。売上管理はオーナー様にて行ってください。予約を取り消すと、ゲストに通知が送信されます。
                  </p>
                </div>
              </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 flex flex-col gap-2">
              <button type="button" @click="executeCancel" :disabled="processingCancel || (Number(refundAmount) > (Number(props.reservation.total_amount) || 0))"
                      class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg shadow-red-200 px-4 py-3 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm disabled:opacity-50 transition">
                <span v-if="processingCancel">処理中...</span>
                <span v-else>{{ props.reservation.payment_method === 'stripe' ? '返金を確定して予約を取り消す' : '予約の取消を確定する' }}</span>
              </button>
              <button type="button" @click="closeModal" :disabled="processingCancel"
                      class="w-full inline-flex justify-center rounded-xl border border-slate-200 px-4 py-3 bg-white text-base font-medium text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:text-sm transition">
                キャンセルせずに閉じる
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 予約内容編集モーダル -->
    <div v-if="showEditModal" class="relative z-50">
      <div class="fixed inset-0 bg-slate-900 bg-opacity-50 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-100">
            <div class="bg-white px-8 pt-8 pb-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">予約内容の変更</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 transition p-2 hover:bg-slate-100 rounded-full">
                  <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>

              <form @submit.prevent="updateReservation" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <!-- ゲスト情報 -->
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">ゲスト名 *</label>
                    <input type="text" v-model="editForm.guest_name" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 transition" />
                    <p v-if="editForm.errors.guest_name" class="mt-1 text-xs text-red-500">{{ editForm.errors.guest_name }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">メールアドレス</label>
                    <input type="email" v-model="editForm.guest_email" class="block w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 transition" />
                    <p v-if="editForm.errors.guest_email" class="mt-1 text-xs text-red-500">{{ editForm.errors.guest_email }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">電話番号 *</label>
                    <input type="tel" v-model="editForm.guest_phone" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 transition" />
                    <p v-if="editForm.errors.guest_phone" class="mt-1 text-xs text-red-500">{{ editForm.errors.guest_phone }}</p>
                  </div>

                  <!-- 人数セクション -->
                  <div class="sm:col-span-2 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">宿泊人数</label>
                        <span class="text-[10px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded">※人数を変更しても料金は変わりません</span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <!-- 大人カウンター -->
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-2">大人</label>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="editForm.number_of_adults = Math.max(1, editForm.number_of_adults - 1)"
                                        class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                                        :disabled="editForm.number_of_adults <= 1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </button>
                                <span class="w-6 text-center font-bold text-slate-800">{{ editForm.number_of_adults }}</span>
                                <button type="button" @click="editForm.number_of_adults++"
                                        class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                        <!-- 子供Aカウンター -->
                        <div v-if="props.reservation.child_a_label">
                            <label class="block text-[10px] font-bold text-slate-400 mb-2">{{ props.reservation.child_a_label }}</label>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="editForm.number_of_child_a = Math.max(0, editForm.number_of_child_a - 1)"
                                        class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                                        :disabled="editForm.number_of_child_a <= 0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </button>
                                <span class="w-6 text-center font-bold text-slate-800">{{ editForm.number_of_child_a }}</span>
                                <button type="button" @click="editForm.number_of_child_a++"
                                        class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                        <!-- 子供Bカウンター -->
                        <div v-if="props.reservation.child_b_label">
                            <label class="block text-[10px] font-bold text-slate-400 mb-2">{{ props.reservation.child_b_label }}</label>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="editForm.number_of_child_b = Math.max(0, editForm.number_of_child_b - 1)"
                                        class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                                        :disabled="editForm.number_of_child_b <= 0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </button>
                                <span class="w-6 text-center font-bold text-slate-800">{{ editForm.number_of_child_b }}</span>
                                <button type="button" @click="editForm.number_of_child_b++"
                                        class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                  </div>

                  <!-- 到着・交通手段 -->
                  <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">到着予定時刻 *</label>
                    <select v-model="editForm.check_in_time" class="block w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 transition">
                        <option value="未定">未定</option>
                        <option v-for="h in Array.from({length: 10}, (_, i) => i + 15)" :key="h" :value="`${h}:00`">{{ h }}:00</option>
                        <option v-for="h in Array.from({length: 10}, (_, i) => i + 15)" :key="`${h}-30`" :value="`${h}:30`">{{ h }}:30</option>
                        <option value="00:00">00:00</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">交通手段 *</label>
                    <select v-model="editForm.transportation" class="block w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 transition">
                        <option value="未定">未定</option>
                        <option value="車">車</option>
                        <option value="電車・バス">電車・バス</option>
                        <option value="その他">その他</option>
                    </select>
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">変更の備考（オーナーメモに追記されます）</label>
                    <textarea v-model="editForm.remarks" rows="3" 
                              placeholder="変更の理由などを入力してください（任意）"
                              class="block w-full px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 transition text-sm"></textarea>
                  </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex flex-col gap-3">
                  <button type="submit" :disabled="editForm.processing"
                          class="w-full py-4 bg-primary-600 text-white font-black rounded-2xl hover:bg-primary-700 transition shadow-xl shadow-primary-200 disabled:opacity-50">
                    <span v-if="editForm.processing">保存中...</span>
                    <span v-else>変更内容を保存する</span>
                  </button>
                  <button type="button" @click="showEditModal = false" :disabled="editForm.processing"
                          class="w-full py-3 bg-white text-slate-400 text-sm font-bold hover:text-slate-600 transition">
                    キャンセル
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </OwnerLayout>
</template>
