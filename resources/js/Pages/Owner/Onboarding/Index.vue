<template>
  <Head title="オンボーディング - RsvBase" />

  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
      <div class="flex justify-center mb-6">
        <RsvLogo className="w-12 h-12 shadow-lg shadow-primary-200" />
      </div>
      <h2 class="text-center text-3xl font-extrabold text-slate-900 tracking-tight">RsvBaseへようこそ</h2>
      <p class="mt-2 text-center text-sm text-slate-600">
        ご利用を開始する前に、以下の設定を完了してください。
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
      <div class="bg-white py-8 px-4 shadow-xl shadow-slate-200/50 sm:rounded-3xl sm:px-10 border border-slate-100">
        
        <!-- ステップ表示 -->
        <div class="mb-10">
          <div class="flex items-center justify-between relative px-10">
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
            <div class="absolute top-1/2 left-0 h-0.5 bg-primary-500 -translate-y-1/2 z-0 transition-all duration-500" :style="{ width: step === 1 ? '50%' : '100%' }"></div>
            
            <div class="relative z-10 flex flex-col items-center">
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-300 shadow-sm', step >= 1 ? 'bg-primary-600 text-white scale-110' : 'bg-slate-200 text-slate-500']">1</div>
              <span class="text-xs mt-2 font-medium text-slate-500">規約同意</span>
            </div>
            
            <div class="relative z-10 flex flex-col items-center">
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-300 shadow-sm', step >= 2 ? 'bg-primary-600 text-white scale-110' : 'bg-slate-200 text-slate-500']">2</div>
              <span class="text-xs mt-2 font-medium text-slate-500">パスワード設定</span>
            </div>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
          
          <!-- Step 1: 規約同意 -->
          <div v-show="step === 1" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
              <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                利用規約・プライバシーポリシー
              </h3>
              
              <div class="space-y-4 text-sm text-slate-600 leading-relaxed overflow-y-auto max-h-60 pr-2 custom-scrollbar border-b pb-4 mb-4">
                <p>本プラットフォーム「RsvBase」の利用にあたっては、以下の規約等への同意が必要です。内容を必ずご確認ください。</p>
                <ul class="list-disc pl-5 space-y-2">
                  <li><Link :href="route('terms')" target="_blank" class="text-primary-600 hover:underline font-bold">RsvBase SaaS利用規約</Link></li>
                  <li><Link :href="route('privacy')" target="_blank" class="text-primary-600 hover:underline font-bold">プライバシーポリシー</Link></li>
                  <li><Link :href="route('platform.legal')" target="_blank" class="text-primary-600 hover:underline font-bold">特定商取引法に基づく表記 (運営者用)</Link></li>
                </ul>
                <p class="text-slate-500 text-xs mt-4 bg-white p-3 rounded-lg border">
                    ※RsvBaseはSaaS提供者であり、宿泊施設と宿泊客の間の「宿泊契約」には直接関与いたしません。また、予約キャンセル時のシステム利用料は原則として返還されませんのでご注意ください。
                </p>
              </div>

              <div class="flex items-start gap-3">
                <input id="accept_terms" v-model="form.accept_terms" type="checkbox" 
                       class="mt-1 h-5 w-5 text-primary-600 border-slate-300 rounded focus:ring-primary-500 cursor-pointer">
                <label for="accept_terms" class="text-sm font-medium text-slate-700 cursor-pointer">
                  上記の利用規約、プライバシーポリシー、およびシステム利用料の取り扱いについて内容を確認し、同意します。
                </label>
              </div>
              <p v-if="form.errors.accept_terms" class="mt-2 text-sm text-red-500 font-bold">{{ form.errors.accept_terms }}</p>
            </div>

            <div class="flex justify-end">
              <button type="button" @click="nextStep" :disabled="!form.accept_terms"
                      class="px-8 py-3 bg-primary-600 text-white font-bold rounded-xl shadow-lg shadow-primary-200 hover:bg-primary-700 transition-all disabled:opacity-50 flex items-center gap-2">
                次へ進む
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Step 2: パスワード設定 -->
          <div v-show="step === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100">
              <h3 class="text-lg font-bold text-indigo-900 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                パスワードの設定
              </h3>
              <p class="text-sm text-indigo-700 mb-6">セキュリティのため、初期パスワードからの変更をお願いしています。</p>

              <div class="grid grid-cols-1 gap-6">
                <div>
                  <label for="password" class="block text-sm font-medium text-slate-700 mb-1">新しいパスワード</label>
                  <input id="password" v-model="form.password" type="password" 
                         class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"
                         placeholder="8文字以上">
                  <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                </div>

                <div>
                  <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">パスワード（確認用）</label>
                  <input id="password_confirmation" v-model="form.password_confirmation" type="password"
                         class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"
                         placeholder="もう一度入力してください">
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center">
              <button type="button" @click="step = 1" class="text-sm font-medium text-slate-500 hover:text-slate-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                戻る
              </button>
              <button type="submit" :disabled="form.processing"
                      class="px-8 py-3 bg-primary-600 text-white font-bold rounded-xl shadow-lg shadow-primary-200 hover:bg-primary-700 transition-all disabled:opacity-50 flex items-center gap-2">
                <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                設定を完了して開始する
              </button>
            </div>
          </div>

        </form>
      </div>
      
      <div class="mt-8 text-center px-4">
        <p class="text-xs text-slate-400">
          RsvBase は、Stripe Partner Ecosystem の加盟企業であり、安全な決済インフラを提供しています。
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import RsvLogo from '@/Components/RsvLogo.vue';

const props = defineProps({
    user: Object,
});

const step = ref(1);

const form = useForm({
    accept_terms: false,
    password: '',
    password_confirmation: '',
});

const nextStep = () => {
    if (form.accept_terms) {
        // パスワード変更が不要な場合はそのまま送信
        if (!props.user.needs_password_change) {
            submit();
        } else {
            step.value = 2;
        }
    }
};

const submit = () => {
    form.post(route('owner.onboarding.update'));
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
