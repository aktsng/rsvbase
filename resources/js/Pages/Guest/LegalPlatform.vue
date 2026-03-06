<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BookingLayout from '@/Layouts/BookingLayout.vue';
</script>

<template>
  <BookingLayout>
    <Head title="特定商取引法に基づく表記 - RsvBase" />

    <div class="py-24 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-20 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <span class="inline-block px-4 py-1.5 bg-slate-100 text-slate-600 font-bold text-xs tracking-[0.2em] rounded-full uppercase mb-6">Legal Information</span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight mb-8">特定商取引法に基づく表記</h1>
            <p class="text-slate-500 text-lg leading-relaxed max-w-2xl mx-auto font-medium">
              本サービス（RsvBase）をオーナー様が利用する際の法定事項です。<br class="hidden sm:block">一般利用者（ゲスト）様向けの各施設に関する表記は、各予約ページをご確認ください。
            </p>
        </div>
        
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-700">
          <div class="p-8 sm:p-12 lg:p-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-12">
              
              <!-- Item -->
              <div class="space-y-3 group">
                <h2 class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em] group-hover:translate-x-1 transition-transform inline-block">運営事業者</h2>
                <p class="text-lg font-bold text-slate-900 leading-snug">RsvBase</p>
              </div>

              <!-- Item -->
              <div class="space-y-3 group">
                <h2 class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em] group-hover:translate-x-1 transition-transform inline-block">運営責任者</h2>
                <div class="pt-1">
                  <img src="/images/legal_name.png" alt="運営責任者" class="w-auto object-contain brightness-95 contrast-125" />
                </div>
              </div>

              <!-- Item -->
              <div class="space-y-3 group">
                <h2 class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em] group-hover:translate-x-1 transition-transform inline-block">所在地</h2>
                <p class="text-slate-600 leading-relaxed font-medium">〒150-0021 東京都渋谷区恵比寿西2丁目4番8号ウィンド恵比寿ビル8F</p>
              </div>

              <!-- Item -->
              <div class="space-y-3 group">
                <h2 class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em] group-hover:translate-x-1 transition-transform inline-block">お問い合わせ先</h2>
                <div class="space-y-1">
                  <p class="text-slate-600 leading-relaxed font-medium">info@rsvbase.com</p>
                  <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider italic">※電話番号は請求に基づき遅滞なく開示いたします</p>
                </div>
              </div>

              <!-- Item -->
              <div class="space-y-3 group">
                <h2 class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em] group-hover:translate-x-1 transition-transform inline-block">サービス提供価格</h2>
                <p class="text-slate-600 leading-relaxed font-medium">
                  基本利用料：無料<br>
                  システム利用料：各予約成立代金の5%（決済手数料を含む）
                </p>
              </div>
 
              <!-- Item -->
              <div class="space-y-3 group">
                <h2 class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em] group-hover:translate-x-1 transition-transform inline-block">代金の支払方法・時期</h2>
                <div class="space-y-1">
                  <p class="text-slate-600 leading-relaxed font-medium">支払方法：宿泊代金との相殺</p>
                  <p class="text-slate-600 leading-relaxed font-medium">支払時期：予約の決済完了時に自動的に差し引かれます。</p>
                </div>
              </div>

              <!-- Item -->
              <div class="space-y-3 group">
                <h2 class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em] group-hover:translate-x-1 transition-transform inline-block">サービスの提供時期</h2>
                <p class="text-slate-600 leading-relaxed font-medium">利用登録完了後、管理画面の提供および予約の受付が可能になった時点から提供されます。</p>
              </div>

              <!-- Item -->
              <div class="space-y-3 group md:col-span-2 bg-slate-50 p-8 rounded-3xl border border-slate-100">
                <h2 class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em] mb-4 block">キャンセル・返金</h2>
                <p class="text-slate-600 leading-relaxed font-medium mb-4">
                  各施設が設定するキャンセルポリシーに基づきます。予約完了後のキャンセルは、各施設の規定に従いキャンセル料が発生する場合があります。
                </p>
                <div class="flex items-start gap-4 text-slate-700 bg-slate-50 p-6 rounded-3xl border border-slate-100 text-sm">
                  <svg class="w-6 h-6 text-teal-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div class="space-y-2">
                    <p class="font-bold text-slate-900">キャンセルに伴う返金と手数料について</p>
                    <p class="leading-relaxed text-slate-600">
                      宿泊代金が返金される場合、Stripe決済手数料（3.6%）は原則として返金対象外となります。一方で、システム利用料分（1.4%）に関しては、返金代金に比例してオーナー様へ払い戻されます。
                    </p>
                    <p class="text-[10px] text-slate-400 font-medium">※返金処理はStripeおよび当システムの仕様に基づき、適切に計算・実行されます。</p>
                  </div>
                </div>
              </div>
            </div>
    
            <div class="mt-20 pt-16 border-t border-slate-100 flex justify-center">
                <Link :href="route('home')" class="inline-flex items-center gap-2 text-teal-700 font-bold hover:gap-3 transition-all">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                  </svg>
                  トップページに戻る
                </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BookingLayout>
</template>
