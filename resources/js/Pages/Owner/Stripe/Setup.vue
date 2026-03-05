<template>
  <OwnerLayout>
    <Head title="決済設定 (Stripe)" />
    <template #header>決済設定 (Stripe Connect)</template>

    <div class="max-w-4xl mx-auto py-6">
      
      <!-- ステータス：完了 -->
      <div v-if="user.stripe_account_status === 'complete'" class="mb-8 bg-white rounded-2xl shadow-sm border border-emerald-200 overflow-hidden">
        <div class="bg-emerald-50 px-6 py-4 flex items-center justify-between border-b border-emerald-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-bold text-emerald-800">連携完了・決済受付中</h3>
              <p class="text-sm text-emerald-600">あなたの施設はゲストからの予約・クレジットカード決済を受け付けることができます。</p>
            </div>
          </div>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p class="text-sm text-slate-500 mb-1">Stripe Account ID</p>
              <p class="font-mono text-slate-800">{{ user.stripe_account_id }}</p>
            </div>
            <div v-if="facility">
              <p class="text-sm text-slate-500 mb-1">現在の施設の公開状態</p>
              <span v-if="facility.is_published" class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-primary-100 text-primary-800">公開中</span>
              <span v-else class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-slate-100 text-slate-800">非公開</span>
              <div v-if="!facility.is_published" class="mt-2">
                <Link :href="route('owner.facility.edit')" class="text-sm font-medium text-primary-600 underline">施設設定で公開する</Link>
              </div>
            </div>
          </div>
          <div class="mt-6 pt-6 border-t border-slate-100 flex gap-4">
            <a href="https://dashboard.stripe.com/" target="_blank" rel="noopener noreferrer" 
               class="px-6 py-2 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-900 transition">
              Stripeダッシュボードを開く
            </a>
          </div>
        </div>
      </div>

      <!-- ステータス：未完了/未連携 -->
      <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center max-w-2xl mx-auto mb-8">
        <div class="w-20 h-20 mx-auto bg-primary-50 rounded-full flex items-center justify-center mb-6">
          <svg class="w-10 h-10 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
        
        <h2 class="text-2xl font-bold text-slate-800 mb-4">決済システムとの連携が必要です</h2>
        <p class="text-slate-600 mb-8 leading-relaxed">
          RsvBase は、Stripe Connectを利用してゲストから宿泊料金を直接あなたのアカウントに送金します。
          安全に売上を受け取るために、Stripeとの連携設定（本人確認・銀行口座の登録）を完了してください。<br>
          <span class="text-sm text-red-500 font-bold">※連携が完了するまで、施設を公開（予約の受付を開始）できません。</span>
        </p>

        <form @submit.prevent="connect">
          <button type="submit" :disabled="processing"
                  class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-[#635BFF] text-white font-bold rounded-xl hover:bg-[#4B45D6] transition shadow-lg shadow-indigo-200 disabled:opacity-50">
            <template v-if="processing">
              <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Stripe に接続しています...
            </template>
            <template v-else>
              Stripe アカウントを設定する
            </template>
          </button>
        </form>

        <p class="mt-6 text-xs text-slate-400">「Stripe アカウントを設定する」をクリックすると、Stripe の安全な環境に移動し、設定を行います。</p>
      </div>

      <!-- 決済に関するよくある質問（FAQ） -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
          <div class="p-2 bg-indigo-50 rounded-xl text-indigo-500">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-800">売上入金・決済に関するよくある質問</h2>
            <p class="text-xs text-slate-400 mt-0.5">Stripe Connect の仕様に基づく情報です</p>
          </div>
        </div>

        <div class="divide-y divide-slate-100">
          <div v-for="(faq, index) in stripeFaqs" :key="index" class="group">
            <button 
              @click="toggleFaq(index)" 
              class="w-full px-6 py-4 flex items-center justify-between gap-4 text-left hover:bg-slate-50 transition"
            >
              <span class="text-sm font-bold text-slate-700 leading-relaxed">{{ faq.question }}</span>
              <svg 
                class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200" 
                :class="{ 'rotate-180': openFaqIndex === index }" 
                fill="none" viewBox="0 0 24 24" stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div 
              v-show="openFaqIndex === index"
              class="px-6 pb-5 text-sm text-slate-600 leading-relaxed animate-in fade-in slide-in-from-top-2 duration-200"
            >
              <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                {{ faq.answer }}
              </div>
            </div>
          </div>
        </div>

        <!-- Stripeサポートへのエスカレーション導線 -->
        <div class="px-6 py-5 bg-slate-50 border-t border-slate-100">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1">
              <p class="text-xs text-slate-500 leading-relaxed">
                審査状況の確認、本人確認書類の再アップロード、振込スケジュールの詳細設定など、Stripeアカウントに関するご不明点は、決済代行会社であるStripe社の日本語サポートへ直接お問い合わせください。
              </p>
            </div>
            <a href="https://support.stripe.com/contact/login" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#635BFF] text-white text-xs font-bold rounded-xl hover:bg-[#4B45D6] transition shadow-sm whitespace-nowrap shrink-0">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
              Stripe 日本語サポートへ問い合わせる
            </a>
          </div>
        </div>
      </div>

    </div>
  </OwnerLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
    facility: Object,
});

const processing = ref(false);
const form = useForm({});

const connect = () => {
    processing.value = true;
    form.post(route('owner.stripe.connect'), {
        onFinish: () => {
            processing.value = false;
        }
    });
};

// --- Stripe FAQ ---
const stripeFaqs = [
    {
        question: '売上はいつ、どのように銀行口座に入金されますか？',
        answer: '日本国内の銀行口座をご利用の場合、決済から最短4営業日後（T+4）に自動で振り込まれます。設定により「日次・週次・月次」のサイクルを選択できるほか、任意のタイミングでの「手動引き出し」も可能です。'
    },
    {
        question: '日々の運営資金（現金）を早く受け取りたいのですが？',
        answer: '宿泊費の決済分は最短4営業日での振込となります。手元に即時の現金が必要な場合は、食事やBBQなどの「追加オプション」のみを現地での現金精算に設定するハイブリッド運用を推奨しております。'
    },
    {
        question: '振込手数料はかかりますか？',
        answer: 'Stripeからお客様の銀行口座への振込手数料は「無料」です。売上からシステム手数料（5% ※決済手数料込）を差し引いた金額が、そのまま入金されます。'
    },
    {
        question: 'カード情報のセキュリティは大丈夫ですか？',
        answer: 'カード情報は世界最高水準のセキュリティ基準（PCI DSS Level 1）を満たすStripe社が直接管理します。オーナー様のシステムやRsvBase側にカード番号が保存されることは一切ありません。'
    }
];

const openFaqIndex = ref(null);
const toggleFaq = (index) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};
</script>
