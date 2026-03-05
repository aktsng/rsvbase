<script setup>
import { Head, Link } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { ref, onMounted, nextTick } from 'vue';

const activeSection = ref('dashboard');

const sections = [
    { id: 'dashboard', label: 'ダッシュボード', icon: '📊' },
    { id: 'reservations', label: '予約管理', icon: '📅' },
    { id: 'facility', label: '施設設定', icon: '⚙️' },
    { id: 'rooms', label: '部屋管理', icon: '🛏️' },
    { id: 'stripe', label: 'Stripe連携', icon: '💳' },
    { id: 'announcements', label: 'お知らせ', icon: '🔔' },
];

const scrollTo = (id) => {
    activeSection.value = id;
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activeSection.value = entry.target.id;
                }
            });
        },
        { rootMargin: '-20% 0px -70% 0px' }
    );
    sections.forEach((s) => {
        const el = document.getElementById(s.id);
        if (el) observer.observe(el);
    });
});
</script>

<template>
  <OwnerLayout>
    <Head title="操作マニュアル" />
    <template #header>操作マニュアル</template>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 flex gap-8">
      <!-- 左：セクションナビ（sticky） -->
      <nav class="hidden lg:block w-52 shrink-0">
        <div class="sticky top-24 space-y-1">
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-3">目次</p>
          <button v-for="s in sections" :key="s.id"
                  @click="scrollTo(s.id)"
                  :class="[
                    'w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                    activeSection === s.id
                      ? 'bg-primary-50 text-primary-700 font-bold shadow-sm'
                      : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                  ]">
            <span class="mr-2">{{ s.icon }}</span>{{ s.label }}
          </button>
        </div>
      </nav>

      <!-- 右：マニュアル本体 -->
      <div class="flex-1 min-w-0 space-y-16">
        <!-- イントロ -->
        <div class="bg-gradient-to-br from-primary-50 to-blue-50 rounded-2xl p-6 border border-primary-100">
          <h2 class="text-xl font-black text-slate-800 mb-2">📖 RsvBase 操作マニュアル</h2>
          <p class="text-sm text-slate-600 leading-relaxed">
            RsvBase の各機能の使い方をスクリーンショット付きで解説します。<br>
            左の目次から各セクションにジャンプできます。
          </p>
        </div>

        <!-- ======= 1. ダッシュボード ======= -->
        <section id="dashboard" class="scroll-mt-24">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">📊</span>
            <h2 class="text-xl font-black text-slate-800">ダッシュボード</h2>
          </div>
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <img src="/images/manual/dashboard.png" alt="ダッシュボード画面" class="w-full" />
          </div>
          <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
            <h3 class="font-bold text-slate-700 text-sm">主な機能</h3>
            <div class="space-y-3 text-sm text-slate-600">
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">1</span>
                <div><span class="font-bold text-slate-800">統計カード</span> — 今月の予約件数と売上をリアルタイムで確認できます。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">2</span>
                <div><span class="font-bold text-slate-800">カレンダー</span> — 部屋ごとの予約状況を月間カレンダーで一覧表示。色付きブロックは予約済みの日を示します。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">3</span>
                <div><span class="font-bold text-slate-800">お知らせ</span> — 運営からの重要なお知らせがダッシュボード上部に表示されます。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">4</span>
                <div><span class="font-bold text-slate-800">最近の予約</span> — 直近の予約が一覧表示されます。クリックすると予約の詳細ページに遷移します。</div>
              </div>
            </div>
          </div>
        </section>

        <!-- ======= 2. 予約管理 ======= -->
        <section id="reservations" class="scroll-mt-24">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">📅</span>
            <h2 class="text-xl font-black text-slate-800">予約管理</h2>
          </div>
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <img src="/images/manual/reservations.png" alt="予約管理画面" class="w-full" />
          </div>
          <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
            <h3 class="font-bold text-slate-700 text-sm">予約一覧の使い方</h3>
            <div class="space-y-3 text-sm text-slate-600">
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">1</span>
                <div><span class="font-bold text-slate-800">検索・絞り込み</span> — 上部のフォームからゲスト名、日付範囲、部屋、ステータスで予約を絞り込めます。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">2</span>
                <div><span class="font-bold text-slate-800">予約詳細</span> — 予約コードをクリックすると、料金明細や決済情報などの詳細を確認できます。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">3</span>
                <div><span class="font-bold text-slate-800">手動予約</span> — 「手動予約作成」ボタンから電話予約などを手動で登録できます。部屋・日程・ゲスト情報を入力して作成します。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">4</span>
                <div><span class="font-bold text-slate-800">キャンセル</span> — 予約詳細画面の「キャンセル」ボタンから予約を取り消すことができます。オンライン決済の場合は自動的に返金処理が行われます。</div>
              </div>
            </div>
          </div>
        </section>

        <!-- ======= 3. 施設設定 ======= -->
        <section id="facility" class="scroll-mt-24">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">⚙️</span>
            <h2 class="text-xl font-black text-slate-800">施設設定</h2>
          </div>
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <img src="/images/manual/facility.png" alt="施設設定画面" class="w-full" />
          </div>
          <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
            <h3 class="font-bold text-slate-700 text-sm">設定項目の説明</h3>
            <div class="space-y-3 text-sm text-slate-600">
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">1</span>
                <div><span class="font-bold text-slate-800">基本情報</span> — 施設名、住所、電話番号、メールアドレスを設定します。予約確認メールなどに反映されます。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">2</span>
                <div><span class="font-bold text-slate-800">チェックイン・チェックアウト時間</span> — ゲストに表示される標準的なチェックイン・アウト時間を設定します。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">3</span>
                <div><span class="font-bold text-slate-800">カスタムURL</span> — 予約ページのURLを「sample-ryokan」のようなカスタム文字列に設定できます。一度設定すると変更できません。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">4</span>
                <div><span class="font-bold text-slate-800">公開設定</span> — 「公開」にすると予約ページがゲストに公開されます。「非公開」の場合はURLにアクセスしても予約ページは表示されません。</div>
              </div>
            </div>
          </div>
        </section>

        <!-- ======= 4. 部屋管理 ======= -->
        <section id="rooms" class="scroll-mt-24">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">🛏️</span>
            <h2 class="text-xl font-black text-slate-800">部屋管理</h2>
          </div>
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <img src="/images/manual/rooms.png" alt="部屋管理画面" class="w-full" />
          </div>
          <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
            <h3 class="font-bold text-slate-700 text-sm">部屋管理の使い方</h3>
            <div class="space-y-3 text-sm text-slate-600">
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">1</span>
                <div><span class="font-bold text-slate-800">部屋の追加</span> — 「部屋を追加」ボタンから新しい部屋を登録できます。部屋名・定員・基本料金・説明を入力します。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">2</span>
                <div><span class="font-bold text-slate-800">特定日料金</span> — 各部屋の「特定日料金」をクリックすると、祝日や繁忙期の料金を日付を指定して個別設定できます。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">3</span>
                <div><span class="font-bold text-slate-800">カレンダーブロック</span> — 「カレンダー」から特定の日を手動でブロック（予約不可）にすることができます。クリックでブロック／解除を切り替えます。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">4</span>
                <div><span class="font-bold text-slate-800">曜日別料金</span> — 部屋の編集画面から曜日ごとに異なる料金を設定できます。週末や金曜日の料金を高めに設定する際に便利です。</div>
              </div>
            </div>
          </div>
        </section>

        <!-- ======= 5. Stripe連携 ======= -->
        <section id="stripe" class="scroll-mt-24">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">💳</span>
            <h2 class="text-xl font-black text-slate-800">Stripe連携</h2>
          </div>
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <img src="/images/manual/stripe.png" alt="Stripe連携画面" class="w-full" />
          </div>
          <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
            <h3 class="font-bold text-slate-700 text-sm">Stripe決済の設定手順</h3>
            <div class="space-y-3 text-sm text-slate-600">
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">1</span>
                <div><span class="font-bold text-slate-800">Stripeアカウント作成</span> — 「Stripe連携」ページの「Stripeアカウントを接続」ボタンをクリックすると、Stripeのアカウント作成画面に遷移します。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">2</span>
                <div><span class="font-bold text-slate-800">本人確認</span> — Stripe側で本人確認書類のアップロードや銀行口座の登録を完了させてください。完了するとオンライン決済の受付が自動的に有効になります。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">3</span>
                <div><span class="font-bold text-slate-800">決済手数料</span> — オンライン決済では、宿泊料金の5%がプラットフォーム手数料として差し引かれます（Stripe決済手数料3.6% + システム利用料1.4%）。</div>
              </div>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-700">
              <strong>⚠️ 注意：</strong>Stripe連携が完了するまで、ゲストのオンライン決済は利用できません。現地決済のみでの運用となります。
            </div>
          </div>
        </section>

        <!-- ======= 6. お知らせ ======= -->
        <section id="announcements" class="scroll-mt-24">
          <div class="flex items-center gap-3 mb-4">
            <span class="text-2xl">🔔</span>
            <h2 class="text-xl font-black text-slate-800">お知らせ</h2>
          </div>
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <img src="/images/manual/announcements.png" alt="お知らせ画面" class="w-full" />
          </div>
          <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
            <h3 class="font-bold text-slate-700 text-sm">お知らせの見方</h3>
            <div class="space-y-3 text-sm text-slate-600">
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">1</span>
                <div><span class="font-bold text-slate-800">カテゴリ</span> — お知らせは「お知らせ（青）」「メンテナンス（黄）」「新機能（緑）」「重要（赤）」の4つのカテゴリで色分けされています。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">2</span>
                <div><span class="font-bold text-slate-800">詳細の確認</span> — タイトルをクリックすると、お知らせの詳細本文が展開されます。</div>
              </div>
              <div class="flex gap-3">
                <span class="bg-primary-100 text-primary-700 font-bold rounded-full w-6 h-6 flex items-center justify-center shrink-0 text-xs">3</span>
                <div><span class="font-bold text-slate-800">ダッシュボード通知</span> — 特に重要なお知らせはダッシュボード上部に自動的に表示されます。</div>
              </div>
            </div>
          </div>
        </section>

        <!-- フッター -->
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 text-center">
          <p class="text-sm text-slate-500">
            ご不明な点がございましたら、サポートまでお気軽にお問い合わせください。
          </p>
        </div>
      </div>
    </div>
  </OwnerLayout>
</template>
