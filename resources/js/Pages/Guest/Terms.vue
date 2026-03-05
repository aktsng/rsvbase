<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BookingLayout from '@/Layouts/BookingLayout.vue';
</script>

<template>
  <BookingLayout>
    <Head title="利用規約 - RsvBase" />

    <div class="py-24 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-16 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <span class="inline-block px-4 py-1.5 bg-teal-50 text-teal-700 font-bold text-xs tracking-[0.2em] rounded-full uppercase mb-6">Terms of Service</span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight mb-8">利用規約</h1>
            <div class="h-1.5 w-24 bg-teal-600 rounded-full"></div>
        </div>
        
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-700">
          <div class="p-8 sm:p-12 lg:p-16">
            <div class="prose prose-slate max-w-none">
              <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100 italic text-slate-600 mb-12 leading-relaxed">
                この利用規約（以下，「本規約」といいます。）は，RsvBase（以下，「当サービス」といいます。）が提供する宿泊予約管理システム（以下，「本システム」といいます。）およびこれに付随するサービス（以下，あわせて「本サービス」といいます。）の利用条件を定めるものです。
              </div>
    
              <div class="space-y-16">
                <!-- Section 1 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">01.</span>
                    第1条（適用および定義）
                  </h2>
                  <div class="text-slate-600 space-y-4 leading-relaxed pl-10">
                    <p>1. 本規約は，ユーザーと当サービスとの間の本サービスの利用に関わる一切の関係に適用されるものとします。</p>
                    <p>2. 本規約において，以下の用語は以下の意味を有するものとします。</p>
                    <ul class="list-disc space-y-2 pl-5">
                      <li>「オーナー」：本システムを利用して宿泊施設を運営・販売する事業者。</li>
                      <li>「ゲスト」：本システムを通じて宿泊予約および決済を行う利用者。</li>
                      <li>「システム利用料」：本システム提供の対価としてオーナーが支払う手数料。</li>
                    </ul>
                  </div>
                </section>
    
                <!-- Section 2 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">02.</span>
                    第2条（システム利用料および決済）
                  </h2>
                  <div class="text-slate-600 space-y-4 leading-relaxed pl-10">
                    <p>1. 本サービスを通じた予約・決済は，Stripe, Inc.が提供するStripe Connectサービスを利用します。オーナーはStripeの定める利用規約に同意するものとします。</p>
                    <p>2. システム利用料は原則として決済金額の5.0%（消費税抜）とし，宿泊代金の決済時に自動的に差し引かれます。</p>
                    <p>3. 予約キャンセル等に伴いゲストへの返金が発生した場合，以下の通り取り扱います。</p>
                    <ul class="list-disc space-y-2 pl-5 text-sm">
                      <li>Stripe決済手数料相当分（決済金額の3.6%）：原則として返金対象外となり，返還されません。</li>
                      <li>システム手数料相当分（決済金額の1.4%）：ゲストへの返金額に比例して計算された金額が，オーナーへ払い戻されます。</li>
                    </ul>
                  </div>
                </section>

                <!-- Section 3 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">03.</span>
                    第3条（アカウント管理）
                  </h2>
                  <div class="text-slate-600 space-y-4 leading-relaxed pl-10">
                    <p>1. ユーザーは，自己の責任において本サービスのログイン情報を管理するものとします。</p>
                    <p>2. 第三者による不正利用等によりユーザーに生じた損害について，当サービスは一切の責任を負いません。また，ユーザーは自身の不注意により生じた損害を補償するものとします。</p>
                  </div>
                </section>
    
                <!-- Section 4 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">04.</span>
                    第4条（禁止事項・サービスの停止）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10 space-y-4">
                    <p>ユーザーは以下の行為を行ってはなりません。</p>
                    <ul class="list-disc space-y-2 pl-5">
                      <li>法令，公序良俗に違反する行為。</li>
                      <li>虚偽の情報を用いて予約またはアカウント登録を行う行為。</li>
                      <li>本システムのネットワークまたはシステムを妨害する行為。</li>
                      <li>他のユーザーの情報を不正に取得・利用する行為。</li>
                    </ul>
                    <p>当サービスは，これらに違反したユーザーに対し，予告なく本サービスの提供を停止またはアカウントを削除できるものとします。</p>
                  </div>
                </section>
    
                <!-- Section 5 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">05.</span>
                    第5条（免責および損害賠償）
                  </h2>
                  <div class="text-slate-600 space-y-4 leading-relaxed pl-10">
                    <p>1. 当サービスは，本システムに致命的な瑕疵がないことを保証するものではなく，通信障害やメンテナンスによる一時的な停止およびそれにより生じた損害について責任を負いません。</p>
                    <p>2. オーナーとゲスト間のトラブルは当事者間で解決するものとし，当サービスはいかなる仲裁・補償も行いません。</p>
                    <p>3. 当サービスが損害賠償責任を負う場合，賠償額は当該損害の原因となった予約に係るシステム利用料の額を上限とします。</p>
                  </div>
                </section>

                <!-- Section 6 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">06.</span>
                    第6条（知的財産権）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10">
                    <p>本サービスに関する著作権，商標権その他の知的財産権は，すべて当サービスまたは権利を有する第三者に帰属します。</p>
                  </div>
                </section>

                <!-- Section 7 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">07.</span>
                    第7条（規約の変更）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10">
                    <p>当サービスは，必要と判断した場合には，ユーザーに通知することなくいつでも本規約を変更することができるものとします。変更後の規約は，本サービス上に掲示した時点で効力を生じるものとします。</p>
                  </div>
                </section>

                <!-- Section 8 -->
                <section>
                  <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-baseline gap-4">
                    <span class="text-teal-500 font-mono text-lg">08.</span>
                    第8条（準拠法・管轄裁判所）
                  </h2>
                  <div class="text-slate-600 leading-relaxed pl-10">
                    <p>1. 本規約の解釈にあたっては，日本法を準拠法とします。</p>
                    <p>2. 本サービスに関して紛争が生じた場合には，当サービスの所在地を管轄する裁判所（東京地方裁判所）を第一審の専属的合意管轄裁判所とします。</p>
                  </div>
                </section>
              </div>
    
              <div class="pt-16 mt-16 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-8">
                <p class="text-sm text-slate-400 font-medium tracking-wide tracking-widest uppercase">Last Updated: 2026.03.01</p>
                <Link :href="route('home')" class="inline-flex items-center gap-2 text-teal-700 font-bold hover:gap-3 transition-all group">
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
