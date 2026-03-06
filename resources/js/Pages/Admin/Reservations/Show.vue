<template>
  <AdminLayout>
    <Head :title="`予約詳細 - ${reservation.reservation_code}`" />
    <template #header>
      <div class="flex items-center gap-4">
        <Link :href="route('admin.reservations.index')" class="text-slate-400 hover:text-slate-600 transition">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </Link>
        <span>予約詳細</span>
      </div>
    </template>

    <div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">

      <!-- ヘッダーカード -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-2xl font-bold font-mono text-primary-600 mb-1">{{ reservation.reservation_code }}</h2>
          <div class="text-slate-500 text-sm">受付日時: {{ reservation.created_at }}</div>
        </div>
        <div class="flex flex-wrap gap-2">
          <span v-if="reservation.status === 'confirmed'" class="px-3 py-1 rounded-full text-sm font-bold bg-emerald-100 text-emerald-800">
            確定
          </span>
          <span v-else-if="['canceled', 'cancelled', 'refunded'].includes(reservation.status)" class="px-3 py-1 rounded-full text-sm font-bold bg-slate-100 text-slate-500">
            キャンセル / 返金済
          </span>
          <span v-else class="px-3 py-1 rounded-full text-sm font-bold bg-amber-100 text-amber-800">
            {{ reservation.status === 'pending' ? '仮予約' : reservation.status }}
          </span>

          <span v-if="reservation.payment_method === 'stripe'" class="px-3 py-1 rounded-full text-sm font-bold border border-blue-200 bg-blue-50 text-blue-700">
            オンライン決済
          </span>
          <span v-else class="px-3 py-1 rounded-full text-sm font-bold border border-amber-200 bg-amber-50 text-amber-700">
            現地決済
          </span>
        </div>
      </div>

      <!-- Stripe Payment Intent ID (オンライン決済の場合) -->
      <div v-if="reservation.payment_method === 'stripe' && reservation.stripe_payment_intent_id" class="bg-blue-50/50 rounded-2xl p-4 shadow-sm border border-blue-100 text-sm flex items-center justify-between">
        <div>
          <span class="text-blue-700 font-bold mr-2">Stripe決済ID:</span>
          <span class="font-mono text-blue-900">{{ reservation.stripe_payment_intent_id }}</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- ゲスト情報 -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">ゲスト情報</h3>
          <dl class="space-y-3 text-sm">
            <div class="grid grid-cols-3">
              <dt class="text-slate-500">氏名</dt>
              <dd class="col-span-2 font-medium text-slate-900">{{ reservation.guest_name }}</dd>
            </div>
            <div class="grid grid-cols-3">
              <dt class="text-slate-500">メールアドレス</dt>
              <dd class="col-span-2 text-slate-900">{{ reservation.guest_email || '-' }}</dd>
            </div>
            <div class="grid grid-cols-3">
              <dt class="text-slate-500">電話番号</dt>
              <dd class="col-span-2 text-slate-900">{{ reservation.guest_phone || '-' }}</dd>
            </div>
            <div class="grid grid-cols-3">
              <dt class="text-slate-500">交通手段</dt>
              <dd class="col-span-2 font-medium text-slate-900">{{ reservation.transportation || '-' }}</dd>
            </div>
            <div class="grid grid-cols-3 pt-2 border-t border-slate-50">
              <dt class="text-slate-500">宿泊人数</dt>
              <dd class="col-span-2 text-slate-900">
                <template v-if="reservation.number_of_child_a > 0 || reservation.number_of_child_b > 0">
                  大人 {{ reservation.number_of_adults ?? reservation.number_of_guests }} 名
                  <template v-if="reservation.number_of_child_a > 0"> / {{ reservation.child_a_label || '子供A' }} {{ reservation.number_of_child_a }} 名</template>
                  <template v-if="reservation.number_of_child_b > 0"> / {{ reservation.child_b_label || '子供B' }} {{ reservation.number_of_child_b }} 名</template>
                </template>
                <template v-else>
                  {{ reservation.number_of_guests }} 名
                </template>
              </dd>
            </div>
          </dl>
          
          <div v-if="reservation.guest_remarks" class="mt-4 pt-4 border-t border-slate-50 text-sm">
            <div class="text-slate-500 mb-1">ゲストからのメッセージ:</div>
            <div class="bg-slate-50 p-3 rounded-xl text-slate-700 whitespace-pre-wrap">{{ reservation.guest_remarks }}</div>
          </div>
        </div>

        <!-- 施設・部屋情報 -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">施設・部屋情報</h3>
          <dl class="space-y-3 text-sm">
            <div class="grid grid-cols-3">
              <dt class="text-slate-500">オーナー</dt>
              <dd class="col-span-2 font-bold text-slate-900">{{ reservation.owner_name }}</dd>
            </div>
            <div class="grid grid-cols-3">
              <dt class="text-slate-500">施設名</dt>
              <dd class="col-span-2 font-medium text-slate-900">
                <Link v-if="reservation.facility_uuid" :href="route('admin.facilities.show', reservation.facility_uuid)" class="text-primary-600 hover:underline">
                  {{ reservation.facility_name }}
                </Link>
                <span v-else>{{ reservation.facility_name }}</span>
              </dd>
            </div>
            <div class="grid grid-cols-3">
              <dt class="text-slate-500">部屋名</dt>
              <dd class="col-span-2 text-slate-900">{{ reservation.room_name }}</dd>
            </div>
            <div class="grid grid-cols-3 pt-2 border-t border-slate-50">
              <dt class="text-slate-500">チェックイン</dt>
              <dd class="col-span-2 font-bold text-slate-900">{{ reservation.check_in_date }} (予定: {{ reservation.check_in_time || '-' }})</dd>
            </div>
            <div class="grid grid-cols-3">
              <dt class="text-slate-500">チェックアウト</dt>
              <dd class="col-span-2 font-bold text-slate-900">{{ reservation.check_out_date }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- 料金・手数料詳細 -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">料金・手数料の詳細 <span class="text-xs font-normal text-slate-400 ml-2">※Stripe手数料は概算</span></h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          
          <!-- ゲスト支払額 -->
          <div>
            <h4 class="font-bold text-slate-700 mb-3 flex items-center gap-2">
              <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
              ゲスト支払総額
            </h4>
            <div class="bg-slate-50/50 p-4 rounded-xl space-y-2 text-sm">
              <template v-for="(price, date) in (reservation.booked_price_details || {})" :key="date">
                <div class="flex justify-between text-slate-600">
                  <span>宿泊代金 {{ price.date || date }} <span class="text-slate-400 text-xs">{{ price.label }}</span></span>
                  <span>¥{{ (price.dayTotal || price.price).toLocaleString() }}</span>
                </div>
                <div v-if="reservation.pricing_type === 'person'" class="pl-4 space-y-1 text-xs text-slate-500 pb-2 mb-2 border-b border-slate-100 border-dashed">
                  <div class="flex justify-between">
                    <span>└ 大人 {{ reservation.number_of_adults }}名 × ¥{{ (Number(price.price || 0)).toLocaleString() }}</span>
                    <span>¥{{ (Number(price.price || 0) * (Number(reservation.number_of_adults) || 1)).toLocaleString() }}</span>
                  </div>
                  <div v-if="reservation.number_of_child_a > 0" class="flex justify-between">
                    <span>└ {{ reservation.child_a_label || '子供A' }} {{ reservation.number_of_child_a }}名 × ¥{{ (Number(price.addChildAFee || 0)).toLocaleString() }}</span>
                    <span>¥{{ (Number(price.addChildAFee || 0) * (Number(reservation.number_of_child_a) || 0)).toLocaleString() }}</span>
                  </div>
                  <div v-if="reservation.number_of_child_b > 0" class="flex justify-between">
                    <span>└ {{ reservation.child_b_label || '子供B' }} {{ reservation.number_of_child_b }}名 × ¥{{ (Number(price.addChildBFee || 0)).toLocaleString() }}</span>
                    <span>¥{{ (Number(price.addChildBFee || 0) * (Number(reservation.number_of_child_b) || 0)).toLocaleString() }}</span>
                  </div>
                </div>
              </template>
              <div class="flex justify-between text-slate-600" v-if="reservation.booked_cleaning_fee > 0">
                <span>清掃費</span>
                <span>¥{{ reservation.booked_cleaning_fee.toLocaleString() }}</span>
              </div>
              
              <div class="flex justify-between font-bold text-lg text-slate-900 pt-2 border-t border-slate-200 mt-2">
                <span>合計金額</span>
                <span>¥{{ reservation.total_amount.toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- オーナー受取額とシステム手数料 -->
          <div>
            <h4 class="font-bold text-slate-700 mb-3 flex items-center gap-2">
              <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
              内訳（システム・オーナー）
            </h4>
            
            <div class="bg-slate-50/50 p-4 rounded-xl space-y-2 text-sm">
              <div class="flex justify-between text-slate-600 pb-2 border-b border-slate-200 border-dashed">
                <span>合計金額</span>
                <span>¥{{ reservation.total_amount.toLocaleString() }}</span>
              </div>
              
              <!-- プラットフォーム手数料合計 (5%) -->
              <div class="flex justify-between text-red-600 font-medium">
                <span>プラットフォーム手数料 ({{ (reservation.platform_fee_rate * 100).toFixed(1) }}%)</span>
                <span>- ¥{{ (reservation.platform_fee || 0).toLocaleString() }}</span>
              </div>
              <!-- 内訳: Stripe + システム -->
              <div v-if="reservation.payment_method === 'stripe'" class="pl-4 space-y-1 text-xs text-slate-500">
                <div class="flex justify-between">
                  <span>└ Stripe決済手数料 (約3.6%)</span>
                  <span>¥{{ (reservation.stripe_fee || 0).toLocaleString() }}</span>
                </div>
                <div class="flex justify-between">
                  <span>└ システム利用料 (約{{ ((reservation.platform_fee_rate - 0.036) * 100).toFixed(1) }}%)</span>
                  <span>¥{{ ((reservation.platform_fee || 0) - (reservation.stripe_fee || 0)).toLocaleString() }}</span>
                </div>
              </div>
              
              <div class="flex justify-between font-bold text-lg text-primary-700 pt-2 border-t border-slate-200 mt-2">
                <span>オーナー受取額</span>
                <span>¥{{ (reservation.owner_net_amount || reservation.total_amount).toLocaleString() }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- キャンセル・返金情報 -->
      <div v-if="['canceled', 'cancelled', 'refunded'].includes(reservation.status)" class="bg-red-50 rounded-2xl p-6 shadow-sm border border-red-100">
        <h3 class="text-lg font-bold text-red-800 mb-4 border-b border-red-200 pb-2 flex items-center gap-2">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          キャンセル・返金情報
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
          <div>
            <div class="text-red-700 mb-1">ゲストへの返金額</div>
            <div class="text-2xl font-bold text-red-900">¥{{ (reservation.refunded_amount || 0).toLocaleString() }}</div>
          </div>
          <div>
            <div class="text-red-700 mb-1">システム利用料の返還（オーナー側へ戻し）</div>
            <div class="text-xl font-bold text-red-900">¥{{ (reservation.platform_fee_refund_amount || 0).toLocaleString() }}</div>
          </div>
          <div v-if="reservation.payment_method === 'stripe'">
            <div class="text-red-700 mb-1">Stripe返金ID</div>
            <div class="font-mono text-red-900">{{ reservation.stripe_refund_id || '-' }}</div>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    reservation: Object,
});
</script>
