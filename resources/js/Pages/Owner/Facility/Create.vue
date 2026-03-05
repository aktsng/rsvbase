<template>
  <OwnerLayout>
    <Head title="施設登録" />
    <template #header>新規施設登録</template>

    <div class="max-w-4xl mx-auto py-6">
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form @submit.prevent="submit" class="space-y-6">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 施設名 -->
            <div class="md:col-span-2">
              <label for="name" class="block text-sm font-medium text-slate-700 mb-1">施設名 <span class="text-red-500">*</span></label>
              <input id="name" v-model="form.name" type="text" required
                     class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition font-bold">
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- 住所 -->
            <div class="md:col-span-2">
              <label for="address" class="block text-sm font-medium text-slate-700 mb-1">住所 <span class="text-red-500">*</span></label>
              <input id="address" v-model="form.address" type="text" required
                     class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              <p v-if="form.errors.address" class="mt-1 text-sm text-red-500">{{ form.errors.address }}</p>
            </div>

            <!-- 電話番号 -->
            <div>
              <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">電話番号 <span class="text-red-500">*</span></label>
              <input id="phone" v-model="form.phone" type="text" required
                     class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">{{ form.errors.phone }}</p>
            </div>

            <!-- 予約締切 -->
            <div>
              <label for="booking_cutoff_hours" class="block text-sm font-medium text-slate-700 mb-1">予約受付の締切 <span class="text-red-500">*</span></label>
              <select id="booking_cutoff_hours" v-model="form.booking_cutoff_hours" required
                      class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                <option :value="0">当日まで（締切なし）</option>
                <option :value="24">チェックイン前日</option>
                <option :value="48">チェックイン2日前</option>
                <option :value="72">チェックイン3日前</option>
              </select>
            </div>

            <!-- チェックイン開始・終了 -->
            <div>
              <label for="check_in_time_start" class="block text-sm font-medium text-slate-700 mb-1">チェックイン時間（開始〜終了） <span class="text-red-500">*</span></label>
              <div class="flex items-center gap-2">
                <input id="check_in_time_start" v-model="form.check_in_time_start" type="time" required
                       class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                <span class="text-slate-500">〜</span>
                <input id="check_in_time_end" v-model="form.check_in_time_end" type="time" required
                       class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              </div>
            </div>

            <!-- チェックアウト時間 -->
            <div>
              <label for="check_out_time" class="block text-sm font-medium text-slate-700 mb-1">チェックアウト時間 <span class="text-red-500">*</span></label>
              <input id="check_out_time" v-model="form.check_out_time" type="time" required
                     class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
            </div>

            <!-- 施設説明 -->
            <div class="md:col-span-2">
              <label for="description" class="block text-sm font-medium text-slate-700 mb-1">施設の説明</label>
              <textarea id="description" v-model="form.description" rows="4"
                        class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
            </div>
            
            <!-- キャンセルポリシー -->
            <div class="md:col-span-2">
              <div class="flex items-center justify-between mb-1">
                <label for="cancellation_policy" class="block text-sm font-medium text-slate-700">キャンセルポリシー <span class="text-red-500">*</span></label>
                <button type="button" @click="applyCancellationTemplate" class="inline-flex items-center gap-1 text-xs font-bold text-primary-600 hover:text-primary-800 hover:bg-primary-50 px-2 py-1 rounded-lg transition group">
                  <svg class="w-3.5 h-3.5 transition group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  テンプレートをセット
                </button>
              </div>
              <textarea id="cancellation_policy" v-model="form.cancellation_policy" rows="5"
                        placeholder="例：宿泊日の3日前からキャンセル料100%が発生します。"
                        class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
            </div>

            <!-- 宿泊約款 -->
            <div class="md:col-span-2">
              <div class="flex items-center justify-between mb-1">
                <label for="terms_text" class="block text-sm font-medium text-slate-700">宿泊約款 <span class="text-red-500">*</span></label>
                <button type="button" @click="applyTermsTemplate" class="inline-flex items-center gap-1 text-xs font-bold text-primary-600 hover:text-primary-800 hover:bg-primary-50 px-2 py-1 rounded-lg transition group">
                  <svg class="w-3.5 h-3.5 transition group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  テンプレートをセット
                </button>
              </div>
              <textarea id="terms_text" v-model="form.terms_text" rows="6"
                        placeholder="宿泊約款を入力してください。"
                        class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
            </div>
          </div>

          <div class="flex justify-between pt-6 border-t border-slate-100">
            <Link :href="route('owner.dashboard')" class="px-6 py-3 text-slate-600 font-medium hover:bg-slate-50 rounded-xl transition">
              キャンセル
            </Link>
            <button type="submit" :disabled="form.processing"
                    class="px-8 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition disabled:opacity-50">
              <span v-if="form.processing">保存中...</span>
              <span v-else>施設を登録</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';

const CANCELLATION_POLICY_TEMPLATE = `【キャンセルポリシー】
・宿泊日の7日前まで：無料
・宿泊日の3〜6日前：宿泊料金の30%
・宿泊日の前日：宿泊料金の50%
・宿泊日の当日：宿泊料金の80%
・無連絡キャンセル：宿泊料金の100%

※キャンセルのご連絡は、お電話またはメールにてお願いいたします。`;

const applyCancellationTemplate = () => {
    if (form.cancellation_policy && form.cancellation_policy.trim() !== '') {
        if (!confirm('現在入力されている内容をテンプレートで上書きしますか？')) {
            return;
        }
    }
    form.cancellation_policy = CANCELLATION_POLICY_TEMPLATE;
};

const TERMS_TEMPLATE = `【宿泊約款】

第1条（宿泊契約の成立）
当施設が予約を承諾した時点で宿泊契約が成立するものとします。

第2条（チェックイン・チェックアウト）
チェックイン・チェックアウトの時間は施設の定める時間に従ってください。到着が遅れる場合は事前にご連絡ください。

第3条（利用規則の遵守）
ご宿泊のお客様は、施設内に掲示する利用規則に従っていただきます。

第4条（客室の利用）
お客様が客室をご利用いただける時間は、チェックインからチェックアウトまでとします。

第5条（禁止事項）
施設内では以下の行為を禁止いたします。
・火気の使用（所定の場所を除く）
・騒音等、他のお客様への迷惑行為
・施設の設備・備品の持ち出し
・ペットの持ち込み（許可がある場合を除く）
・施設敷地内での無断撮影（商用目的）

第6条（損害賠償）
お客様の故意または過失により施設が損害を被った場合は、相当額を賠償していただきます。

第7条（個人情報の取扱い）
ご提供いただいた個人情報は、宿泊サービスの提供および法令に基づく対応のために利用し、適切に管理いたします。

第8条（免責）
天災地変その他不可抗力により宿泊サービスの提供ができない場合、施設は責任を負いません。`;

const applyTermsTemplate = () => {
    if (form.terms_text && form.terms_text.trim() !== '') {
        if (!confirm('現在入力されている内容をテンプレートで上書きしますか？')) {
            return;
        }
    }
    form.terms_text = TERMS_TEMPLATE;
};

const form = useForm({
    name: '',
    address: '',
    phone: '',
    description: '',
    booking_cutoff_hours: 0,
    check_in_time_start: '15:00',
    check_in_time_end: '22:00',
    check_out_time: '10:00',
    cancellation_policy: '',
    terms_text: '',
});

const submit = () => {
    form.post(route('owner.facility.store'));
};
</script>
