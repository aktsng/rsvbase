<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BookingLayout from '@/Layouts/BookingLayout.vue';
</script>

<template>
  <BookingLayout>
    <Head title="プライバシーポリシー - RsvBase" />

    <div class="py-24 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-16 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <span class="inline-block px-4 py-1.5 bg-teal-50 text-teal-700 font-bold text-xs tracking-[0.2em] rounded-full uppercase mb-6">Privacy Policy</span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight mb-8">プライバシーポリシー</h1>
            <div class="h-1.5 w-24 bg-teal-600 rounded-full"></div>
        </div>
        
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-700">
          <div class="p-8 sm:p-12 lg:p-16">
            <div class="prose prose-slate max-w-none">
              <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100 italic text-slate-600 mb-12 leading-relaxed">
                RsvBase（以下，「当サービス」といいます。）は，本サービス上で提供する宿泊予約管理システムにおける，ユーザーの個人情報の取扱いについて，以下のとおりプライバシーポリシーを定めます。
              </div>
    
              <div class="space-y-16">
                <!-- Section 1 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">01.</span>
                    第1条（個人情報の収集方法）
                  </h2>
                  <div class="text-slate-600 space-y-4 leading-relaxed pl-10">
                    <p>当サービスは，ユーザーが予約を行う際に，氏名，住所，電話番号，メールアドレス，クレジットカード情報などの個人情報をお尋ねすることがあります。また，決済に関する情報を決済代行業者（Stripe, Inc.等）から収集することがあります。</p>
                  </div>
                </section>
    
                <!-- Section 2 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">02.</span>
                    第2条（個人情報を収集・利用する目的）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10">
                    <p class="mb-4">当サービスが個人情報を収集・利用する目的は，以下のとおりです。</p>
                    <ul class="list-disc space-y-3 pl-5">
                      <li>本サービスの提供・運営のため</li>
                      <li>オーナーとゲスト間の宿泊予約・決済を成立させるため</li>
                      <li>ユーザーからのお問い合わせに回答するため（本人確認を行うことを含む）</li>
                      <li>本サービスの利用状況の分析および機能改善のため</li>
                      <li>システムの保守，メンテナンスおよび重要なお知らせの通知のため</li>
                    </ul>
                  </div>
                </section>
 
                <!-- Section 3 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">03.</span>
                    第3条（個人情報の第三者提供および委託）
                  </h2>
                  <div class="text-slate-600 space-y-4 leading-relaxed pl-10">
                    <p>1. 当サービスは，決済代行業者（Stripe, Inc.等）に対し，決済処理の目的で個人情報を提供します。</p>
                    <p>2. 当サービスは，宿泊予約の履行のため，オーナーに対し，当該予約に必要なゲストの個人情報を提供します。</p>
                    <p>3. 当サービスは，利用目的の達成に必要な範囲において，個人情報の取扱いの全部または一部をクラウドインフラ事業者等の外部業者に委託することがあります。</p>
                    <p>4. 法令に基づき開示が必要な場合を除き，上記以外の第三者に同意なく個人情報を提供することはありません。</p>
                  </div>
                </section>
    
                <!-- Section 4 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">04.</span>
                    第4条（安全管理措置）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10">
                    <p>当サービスは，個人情報の漏洩，滅失または毀損の防止その他の個人情報の安全管理のために必要かつ適切な措置を講じます。クレジットカード情報はカード業界のセキュリティ基準（PCI DSS等）に準拠した決済システム上で直接処理され，当システムのサーバー内には保持いたしません。</p>
                  </div>
                </section>

                <!-- Section 5 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">05.</span>
                    第5条（Cookieおよび解析ツールの利用）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10">
                    <p>本サービスでは，利便性の向上および利用状況の解析のため，Cookie（クッキー）を利用しています。ユーザーはブラウザの設定によりCookieを無効にすることができますが，その場合，サービスの一部が正しく機能しない場合があります。</p>
                  </div>
                </section>

                <!-- Section 6 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">06.</span>
                    第6条（個人情報の照会・訂正・削除）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10">
                    <p>ユーザーから自身の個人情報の開示，訂正，利用停止，消去等の請求があった場合には，本人確認を行った上で，速やかに対応いたします。具体的な手続きについては下記のお問い合わせ窓口までご連絡ください。</p>
                  </div>
                </section>

                <!-- Section 7 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">07.</span>
                    第7条（お問い合わせ窓口）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10">
                    <p>本ポリシーに関するお問い合わせは，RsvBaseサポート窓口までお願いいたします。</p>
                    <p class="mt-2 font-bold text-slate-900">Email: support@rsvbase.com</p>
                  </div>
                </section>
              </div>
    
              <div class="pt-16 mt-16 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-8">
                <p class="text-sm text-slate-400 font-medium tracking-wide tracking-widest uppercase">Effective Date: 2026.03.01</p>
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
    </div>
  </BookingLayout>
</template>
