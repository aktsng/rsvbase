<template>
  <AdminLayout>
    <Head title="施設登録" />
    <template #header>施設登録</template>

    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <form @submit.prevent="submit" class="space-y-8 bg-white p-8 shadow-sm ring-1 ring-slate-200 rounded-2xl">
        <!-- 基本情報 -->
        <section>
          <h3 class="text-lg font-bold text-slate-900 mb-4 border-l-4 border-primary-600 pl-3">基本情報</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1">オーナーユーザー <span class="text-red-500">*</span></label>
              <select v-model="form.user_id" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                <option value="">選択してください</option>
                <option v-for="owner in owners" :key="owner.id" :value="owner.id">
                  {{ owner.name }} ({{ owner.email }})
                </option>
              </select>
              <div v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1">施設名 <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.name" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="例: 湘南シーサイドハウス">
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1">説明</label>
              <textarea v-model="form.description" rows="4" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="施設の魅力や周辺情報を入力してください"></textarea>
              <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
            </div>
          </div>
        </section>

        <!-- 連絡先・住所 -->
        <section>
          <h3 class="text-lg font-bold text-slate-900 mb-4 border-l-4 border-primary-600 pl-3">連絡先・住所</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">郵便番号</label>
              <input type="text" v-model="form.postal_code" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="123-4567">
              <div v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.postal_code }}</div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">電話番号</label>
              <input type="text" v-model="form.phone" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="03-1234-5678">
              <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1">住所</label>
              <input type="text" v-model="form.address" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="東京都渋谷区...">
              <div v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</div>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-slate-700 mb-1">施設連絡用メールアドレス</label>
              <input type="email" v-model="form.email" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="contact@example.com">
              <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>
          </div>
        </section>

        <!-- 運用設定 -->
        <section>
          <h3 class="text-lg font-bold text-slate-900 mb-4 border-l-4 border-primary-600 pl-3">運用設定</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">当日締切時間 <span class="text-red-500">*</span></label>
              <input type="time" v-model="form.same_day_booking_cutoff_time" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              <div v-if="form.errors.same_day_booking_cutoff_time" class="mt-1 text-sm text-red-600">{{ form.errors.same_day_booking_cutoff_time }}</div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">チェックイン開始 <span class="text-red-500">*</span></label>
              <input type="time" v-model="form.check_in_start_time" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              <div v-if="form.errors.check_in_start_time" class="mt-1 text-sm text-red-600">{{ form.errors.check_in_start_time }}</div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">チェックイン終了 <span class="text-red-500">*</span></label>
              <input type="time" v-model="form.check_in_end_time" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              <div v-if="form.errors.check_in_end_time" class="mt-1 text-sm text-red-600">{{ form.errors.check_in_end_time }}</div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">販売手数料率 (%) <span class="text-red-500">*</span></label>
              <div class="relative rounded-xl shadow-sm">
                <input type="number" step="0.1" min="0" max="100" v-model="platform_fee_percent" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition pr-12">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span class="text-slate-500 sm:text-sm">%</span>
                </div>
              </div>
              <div v-if="form.errors.platform_fee_rate" class="mt-1 text-sm text-red-600">{{ form.errors.platform_fee_rate }}</div>
            </div>
            <div class="md:col-span-2 flex items-center pt-6">
              <input type="checkbox" v-model="form.is_published" id="is_published" class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded-md transition">
              <label for="is_published" class="ml-3 text-sm font-semibold text-slate-700">公開する（Stripe準備完了後に有効にしてください）</label>
            </div>
          </div>
        </section>

        <!-- ボタン -->
        <div class="flex items-center justify-end gap-4 border-t border-slate-200 pt-6">
          <Link :href="route('admin.facilities.index')" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">キャンセル</Link>
          <button type="submit" :disabled="form.processing" 
                  class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 transition">
            <span v-if="form.processing">登録中...</span>
            <span v-else>登録する</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    owners: Array,
});

const form = useForm({
    user_id: '',
    name: '',
    description: '',
    postal_code: '',
    address: '',
    phone: '',
    email: '',
    same_day_booking_cutoff_time: '18:00',
    check_in_start_time: '15:00',
    check_in_end_time: '22:00',
    platform_fee_rate: 0.05,
    is_published: false,
});

// パーセント表示とデータベースの小数値を変換
const platform_fee_percent = computed({
    get: () => form.platform_fee_rate * 100,
    set: (val) => { form.platform_fee_rate = val / 100 }
});

const submit = () => {
    form.post(route('admin.facilities.store'));
};
</script>
