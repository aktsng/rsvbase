<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive, onMounted } from 'vue';
import RsvLogo from '@/Components/RsvLogo.vue';

// スクロールアニメーション用のIntersectionObserver
const observer = ref(null);
const currentImageIndex = ref(0);
const heroImages = [
    'https://placehold.jp/36/0f172a/ffffff/450x950.png?text=予約管理%0A画面イメージ',
    'https://placehold.jp/36/0f172a/ffffff/450x950.png?text=カレンダー%0A設定イメージ',
    'https://placehold.jp/36/0f172a/ffffff/450x950.png?text=ゲスト予約%0A画面イメージ'
];

onMounted(() => {
    // スライダーの自動再生
    setInterval(() => {
        currentImageIndex.value = (currentImageIndex.value + 1) % heroImages.length;
    }, 5000);

    observer.value = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in', 'fade-in', 'slide-in-from-bottom-4', 'duration-1000', 'fill-mode-forwards');
                observer.value.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => {
        observer.value.observe(el);
    });
});

const features = [
    {
        title: '24時間働く自動販売機へ',
        subtitle: 'DMでの「空室確認」から解放されます。',
        description: 'あなたが寝ている間も、清掃している間も、システムが文句一つ言わずに予約受付と集金を済ませておいてくれます。',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        title: '「ドタキャン泣き寝入り」をゼロに',
        subtitle: '100%事前カード決済でリスクを排除。',
        description: '予約と同時にStripeによる即時決済が行われます。お客様が来なくても口座には全額入金されるため、ハラハラしながら待つ必要はありません。',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
    },
    {
        title: '機会損失を防ぐ1タップUX',
        subtitle: '「DMは面倒…」と諦めていた客を逃さない。',
        description: 'スマホから1タップで予約が完了する圧倒的なスムーズさ。夜中にInstagramを見て「今すぐ予約したい」と思ったフォロワーの熱を冷ましません。',
        icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'
    },
    {
        title: '究極の「引き算」システム',
        subtitle: 'ITが苦手でも大丈夫。',
        description: '複雑な機能を削ぎ落とし、設定は「1部屋（1棟）いくらか」または「大人・子供ごとの人数単価」のどちらかを選ぶだけ。分厚いマニュアルは不要です。電話予約もサッと手動登録でき、ネットも電話もこれ一つで一元管理できる直感的な画面にこだわりました。',
        icon: 'M20 12H4'
    },
    {
        title: 'ゲスト手数料「0円」',
        subtitle: 'お客様に余計な上乗せ費用を負担させません。',
        description: '大手OTA（予約サイト）では宿泊料に約12%のゲスト手数料が上乗せされますが、RsvBaseはゲスト手数料ゼロ。お客様にとって「一番お得に泊まれる窓口」になれるため、InstagramやSNSの「プロフィールリンクから直接予約」に最適です。',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        title: '英語のDM対応も、URLを１つ送るだけ。',
        subtitle: 'インバウンド予約も全自動。翻訳ツールはもう不要です。',
        description: '海外のお客様からInstagramに英語のDMが来ても、焦る必要はありません。「Please book here」と専用URLを返すだけで完了です。お客様のスマホの言語に合わせて、予約画面と決済画面が自動で英語に切り替わり、海外のクレジットカードでスムーズに事前決済が行われます。',
        icon: 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129'
    }
];

const faqs = [
    {
        question: '人数によって料金を変えること（大人〇円、子供〇円）はできますか？',
        answer: 'はい、可能です。「室単価（1部屋あたりの定額）」と「人数単価（大人・子供ごとの料金）」のどちらか、施設に合った方法を部屋ごとに選択いただけます。子供料金は2種類まで設定でき、独自のラベル（小学生、乳幼児など）を付けることも可能です。'
    },
    {
        question: '食事やBBQなどのオプション料金を追加できますか？',
        answer: 'システム上でのオプション決済には対応していません。まずは最も高額でキャンセルリスクの高い「宿泊費」のみをRsvBaseで事前カード決済していただき、追加オプションは現地精算（現金やPayPay等）にしていただく運用を推奨しております。'
    },
    {
        question: 'じゃらんや楽天トラベルとカレンダーの自動連動はできますか？',
        answer: '初期状態では自動連動機能はありません。予約が入った瞬間にオーナー様のスマホへ通知メールが届きますので、他サイトのカレンダーを手動でブロック（売止）していただく、シンプルな運用をお願いしております。'
    },
    {
        question: '海外のお客様（インバウンド）からの予約も受け付けられますか？',
        answer: 'はい、もちろんです！RsvBaseの予約画面および決済画面（Stripe）は、アクセスしたお客様のブラウザ設定に合わせて自動で英語に最適化されます。オーナー様は英語でメッセージのやり取りをすることなく予約を獲得できます。なお、オーナー様が操作する管理画面は使いやすい日本語のままですのでご安心ください。'
    },
    {
        question: '導入のための審査や手続きは難しいですか？',
        answer: 'オンラインで数分で完結します。スマートフォンで身分証明書（運転免許証等）を撮影・アップロードするだけで、最短当日からカード決済の受付を開始できます。'
    },
    {
        question: 'お客様のクレジットカード情報が漏洩しないか心配です。',
        answer: 'ご安心ください。カード情報は世界最高水準のセキュリティを誇るStripe社が直接管理します。オーナー様の元にカード番号が保存されることは一切ないため、管理・漏洩リスクを負う必要はありません。'
    },
    {
        question: '銀行への振込手数料はいくらかかりますか？',
        answer: 'Stripeからお客様の銀行口座への振込手数料は「無料」です。売上からシステム手数料（5% ※決済手数料込）を差し引いた金額が、そのまま入金されます。'
    }
];

const openFaqIndex = ref(null);
const toggleFaq = (index) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

const steps = [
    { step: 'Step 1', title: 'アカウント発行 ＆ 料金設定', description: 'メールアドレスだけで登録完了。お部屋の料金を決めます。' },
    { step: 'Step 2', title: '売上受取用の口座を連携', description: 'Stripeと連携して、売上の自動入金設定を行います。' },
    { step: 'Step 3', title: 'URLをInstagramやSNSに貼るだけ', description: '発行されたURLをプロフィールに追加して、予約受付開始！' }
];
</script>

<template>
    <Head title="RsvBase - 小規模宿泊施設のための極限までシンプルな予約システム" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 overflow-x-hidden selection:bg-teal-100 selection:text-teal-900">
        <!-- Header -->
        <header class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <RsvLogo className="w-9 h-9" />
                    <span class="text-xl font-extrabold tracking-tight text-slate-800">RsvBase</span>
                </div>
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#features" class="hover:text-teal-600 transition">特徴</a>
                    <a href="#steps" class="hover:text-teal-600 transition">導入の流れ</a>
                    <a href="#pricing" class="hover:text-teal-600 transition">料金体系</a>
                    <a href="#faq" class="hover:text-teal-600 transition">FAQ</a>
                    <Link :href="route('inquiry')" class="text-slate-800 hover:text-teal-600 font-bold transition">ログイン</Link>
                    <Link :href="route('inquiry')" class="bg-teal-700 text-white px-5 py-2 rounded-full hover:bg-teal-800 transition-all shadow-sm shadow-teal-200">
                        無料で始める
                    </Link>
                </nav>
                <div class="md:hidden">
                    <Link :href="route('inquiry')" class="bg-teal-700 text-white px-4 py-2 rounded-full text-sm font-bold shadow-sm">
                        開始
                    </Link>
                </div>
            </div>
        </header>

        <main class="pt-16">
            <!-- Hero Section -->
            <section class="relative py-20 lg:py-32 overflow-hidden bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="lg:flex lg:items-center lg:gap-16">
                        <div class="lg:w-1/2 text-center lg:text-left reveal">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-teal-50 text-teal-700 rounded-full text-sm font-bold mb-6 ring-1 ring-teal-100">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                                </span>
                                小規模宿のための予約ツール
                            </div>
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-[1.15] mb-6">
                                「〇日空いてますか？」のDM、<br class="hidden sm:block">
                                <span class="text-teal-700">今日で終わりにしませんか。</span>
                            </h1>
                            <p class="text-lg sm:text-xl text-slate-600 mb-10 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                                InstagramやSNSにURLを貼るだけ。<br>
                                手数料ゼロ・事前決済でドタキャンを防ぐ、<br class="hidden sm:block">
                                あなたの時間を守る予約システム「RsvBase」
                            </p>
                            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                                <Link :href="route('inquiry')" class="inline-block px-12 py-5 bg-orange-600 text-white rounded-2xl font-bold text-xl hover:bg-orange-700 transition-all transform hover:-translate-y-1 shadow-2xl shadow-orange-200">
                            無料で始める
                        </Link>
                                <p class="text-sm text-slate-400 font-medium italic">
                                    ※ 初期費用・月額固定費 0円
                                </p>
                            </div>
                        </div>
                        <div class="lg:w-1/2 mt-16 lg:mt-0 relative reveal duration-700 delay-300">
                            <!-- 縦型画像スライダー（スマホフレーム風） -->
                            <div class="relative mx-auto w-[280px] sm:w-[320px] aspect-[9/19] bg-slate-900 rounded-[3rem] shadow-[0_0_0_8px_rgba(15,23,42,1),0_32px_64px_-16px_rgba(15,23,42,0.3)] p-3 border-4 border-slate-800 overflow-hidden">
                                <div class="w-full h-full bg-slate-50 rounded-[2.5rem] overflow-hidden relative">
                                    <div v-for="(img, index) in heroImages" :key="index" 
                                         class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                                         :class="currentImageIndex === index ? 'opacity-100' : 'opacity-0'">
                                        <img :src="img" class="w-full h-full object-cover" alt="Hero Image" />
                                    </div>
                                    <!-- スライダーインジケーター -->
                                    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex gap-2">
                                        <div v-for="(_, index) in heroImages" :key="index"
                                             class="w-2 h-2 rounded-full transition-all"
                                             :class="currentImageIndex === index ? 'bg-slate-800 w-6' : 'bg-slate-300'"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- 背景装飾 -->
                            <div class="absolute -top-10 -right-10 w-64 h-64 bg-teal-50 rounded-full blur-3xl -z-10 opacity-60"></div>
                            <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-orange-50 rounded-full blur-3xl -z-10 opacity-60"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pain Points section -->
            <section class="py-20 bg-slate-50 overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl mx-auto text-center mb-16 reveal">
                        <span class="text-teal-600 font-bold uppercase tracking-widest text-xs">COMMISERATION</span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">こんなお悩み、ありませんか？</h2>
                    </div>
                    <div class="grid md:grid-cols-3 gap-8">
                        <div v-for="(pain, i) in [
                            '夜中のDM対応で、自分の睡眠時間やプライベートが削られている。',
                            'DMでの予約は、ドタキャン（NoShow）された時に泣き寝入りするしかない。',
                            '大手予約サイトは手数料が高く、管理画面も複雑すぎて使いこなせない。'
                        ]" :key="i" class="bg-white p-8 rounded-3xl shadow-sm ring-1 ring-slate-900/5 reveal" :style="{ transitionDelay: `${i * 150}ms` }">
                            <div class="w-10 h-10 bg-orange-50 rounded-full flex items-center justify-center mb-6">
                                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <p class="text-lg font-medium text-slate-800 leading-relaxed">{{ pain }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="py-24 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-20 reveal">
                        <span class="text-teal-600 font-bold uppercase tracking-widest text-xs">WHY RSVBASE?</span>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 mt-2 mb-6">直感的に使える、身軽な仕組み</h2>
                        <p class="text-lg text-slate-600">小規模な宿だからこそ必要な、最小限で最速の機能を揃えました。</p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-16">
                        <div v-for="(feature, i) in features" :key="i" class="flex gap-6 reveal" :style="{ transitionDelay: `${(i % 2) * 200}ms` }">
                            <div class="flex-shrink-0">
                                <div class="w-14 h-14 bg-teal-50 rounded-2xl flex items-center justify-center ring-1 ring-teal-100">
                                    <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-teal-700 font-bold text-sm mb-1 uppercase tracking-wider">{{ feature.title }}</h3>
                                <h4 class="text-xl font-bold text-slate-900 mb-3">{{ feature.subtitle }}</h4>
                                <p class="text-slate-600 leading-relaxed">{{ feature.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Comparison Section -->
            <section id="comparison" class="py-24 bg-slate-50 relative overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                        <span class="text-teal-600 font-bold uppercase tracking-widest text-xs">HYBRID STRATEGY</span>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 mt-2 mb-6">OTA（予約サイト）との賢い使い分け</h2>
                        <p class="text-lg text-slate-600 leading-relaxed">
                            すでにRakuten Oyadoなどの予約システムをご利用のオーナー様へ。<br class="hidden sm:block">
                            RsvBaseは、既存のシステムを「やめる」必要はありません。<br class="hidden sm:block">
                            <span class="text-teal-700 font-bold">用途を分けるだけで、利益は劇的に変わります。</span>
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8 lg:gap-12 max-w-5xl mx-auto">
                        <!-- Card A: OTA -->
                        <div class="bg-white rounded-[2.5rem] p-8 lg:p-10 shadow-sm border border-slate-100 flex flex-col reveal">
                            <div class="mb-8">
                                <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold mb-4">
                                    既存のOTA（例：Rakuten Oyado）
                                </div>
                                <h3 class="text-2xl font-bold text-slate-900 mb-2">「新規集客」のための広告塔</h3>
                                <p class="text-slate-500 text-sm">まだあなたの宿を知らない世界中のお客様へ</p>
                            </div>

                            <div class="space-y-6 mb-10 flex-grow">
                                <div class="flex justify-between items-center py-3 border-b border-slate-50">
                                    <span class="text-slate-500 font-medium">役割</span>
                                    <span class="text-slate-900 font-bold">「新規集客」のための広告塔</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-slate-50">
                                    <span class="text-slate-500 font-medium">ターゲット</span>
                                    <span class="text-slate-900 font-bold">まだあなたの宿を知らない世界中のお客様</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-slate-50">
                                    <span class="text-slate-500 font-medium">ゲスト手数料</span>
                                    <span class="text-slate-900 font-bold">12%<span class="text-xs font-normal text-slate-400 ml-1">（宿泊料金に上乗せ）</span></span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-slate-50">
                                    <span class="text-slate-500 font-medium">ホスト手数料</span>
                                    <span class="text-slate-900 font-bold">3%</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-slate-50">
                                    <span class="text-slate-500 font-medium">システム</span>
                                    <span class="text-slate-900 font-bold">在庫管理型<span class="text-xs font-normal text-slate-400 ml-1">（多機能・複雑）</span></span>
                                </div>
                            </div>

                            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 mt-auto">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">正しい使い方</h4>
                                <p class="text-sm text-slate-600 leading-relaxed italic">
                                    楽天トラベル等への自動掲載を利用し、新規顧客を獲得する窓口として活用。
                                </p>
                            </div>
                        </div>

                        <!-- Card B: RsvBase -->
                        <div class="bg-gradient-to-br from-teal-50 to-white rounded-[2.5rem] p-8 lg:p-10 shadow-xl shadow-teal-900/5 border border-teal-100 relative overflow-hidden flex flex-col reveal">
                            <div class="absolute top-0 right-0 p-6">
                                <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center shadow-lg shadow-teal-200">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                            </div>

                            <div class="mb-8">
                                <div class="inline-flex items-center gap-2 px-3 py-1 bg-teal-100 text-teal-700 rounded-full text-xs font-bold mb-4">
                                    RsvBase（本システム）
                                </div>
                                <h3 class="text-2xl font-bold text-slate-900 mb-2">「利益最大化」のための直販ツール</h3>
                                <p class="text-slate-500 text-sm">Instagramのフォロワー、リピーター様へ</p>
                            </div>

                            <div class="space-y-6 mb-10 flex-grow">
                                <div class="flex justify-between items-center py-3 border-b border-teal-100/50">
                                    <span class="text-slate-500 font-medium">役割</span>
                                    <span class="text-slate-900 font-bold">「利益最大化」のための直販ツール</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-teal-100/50">
                                    <span class="text-slate-500 font-medium">ターゲット</span>
                                    <span class="text-slate-900 font-bold">Instagramのフォロワー、リピーター様</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-teal-100/50">
                                    <span class="text-slate-500 font-medium">ゲスト手数料</span>
                                    <span class="text-teal-600 font-black text-xl">0円<span class="text-xs font-bold text-teal-500 ml-1">無料！</span></span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-teal-100/50">
                                    <span class="text-slate-500 font-medium">ホスト手数料</span>
                                    <span class="text-slate-900 font-bold">5% <span class="text-xs font-normal text-slate-400 ml-1">（Stripe決済手数料込）</span></span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-teal-100/50">
                                    <span class="text-slate-500 font-medium">システム</span>
                                    <span class="text-slate-900 font-bold">カレンダー型<span class="text-xs font-normal text-slate-400 ml-1">（超シンプル・1タップ決済）</span></span>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl p-5 border border-teal-100 shadow-sm mt-auto">
                                <h4 class="text-xs font-bold text-teal-500 uppercase tracking-wider mb-2">正しい使い方</h4>
                                <p class="text-sm text-slate-600 leading-relaxed italic">
                                    Instagramのプロフィールリンクに貼り、自力で集客したファンに一番お得な価格で提供する。
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-16 text-center reveal">
                        <div class="inline-block px-8 py-6 bg-white rounded-3xl shadow-sm border border-slate-100 max-w-3xl">
                            <p class="text-slate-800 font-bold text-lg mb-4">
                                「新規客」はOTAで集め、「ファン」はRsvBaseで直接おもてなししましょう。
                            </p>
                            <p class="text-slate-600 text-sm leading-relaxed text-left sm:text-center">
                                OTAは新規客を連れてきてくれる素晴らしいシステムですが、あなたがInstagramで自力で集めた大切なファンにまで、OTAの「ゲスト手数料（約12%）」を負担させる必要はありません。<br class="hidden sm:block">
                                InstagramのリンクをRsvBaseに変えるだけで、お客様は一番安く泊まることができ、オーナー様の手元に残る利益も最大化されます。
                            </p>
                        </div>
                        
                        <div class="mt-8">
                            <p class="text-[10px] text-slate-400 leading-relaxed">
                                ※Rakuten Oyadoは、楽天グループ株式会社またはその関連会社の商標または登録商標です。<br>
                                ※記載の手数料率等は調査時点の一般的な目安であり、実際の契約条件により異なる場合があります。
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Introduction Section (How it Works) -->

            <section id="steps" class="py-24 bg-slate-900 text-white overflow-hidden relative">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-96 h-96 bg-teal-500 rounded-full blur-[120px] -translate-x-1/2 -translate-y-1/2"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-teal-500 rounded-full blur-[120px] translate-x-1/2 translate-y-1/2"></div>
                </div>
                
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="text-center mb-20 reveal">
                        <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">わずか3ステップで導入完了</h2>
                        <p class="text-slate-400">ITが苦手なオーナー様でも、10分で予約受付を開始できます。</p>
                    </div>
                    
                    <div class="grid lg:grid-cols-3 gap-12 lg:gap-8">
                        <div v-for="(step, i) in steps" :key="i" class="relative reveal" :style="{ transitionDelay: `${i * 150}ms` }">
                            <div v-if="i < 2" class="hidden lg:block absolute top-10 left-full w-full h-[2px] bg-gradient-to-r from-teal-500/50 to-transparent z-0"></div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-teal-600 rounded-full flex items-center justify-center text-2xl font-black mb-6 shadow-xl shadow-teal-900/50 border-4 border-slate-900">
                                    {{ i + 1 }}
                                </div>
                                <h3 class="text-xl font-bold mb-4">{{ step.title }}</h3>
                                <p class="text-slate-400 leading-relaxed">{{ step.description }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-20 text-center reveal">
                        <div class="inline-block p-8 bg-slate-800/50 backdrop-blur-md rounded-[2.5rem] border border-slate-700 max-w-2xl">
                            <h4 class="text-xl font-bold mb-4 text-teal-400 italic">「InstagramやSNSのプロフィールに貼るだけ」</h4>
                            <p class="text-slate-300">専用の予約URLを貼り付ければ、フォロワーはいつでも、あなたの手を煩わせることなく空室を確認し、予約できます。</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pricing Section -->
            <section id="pricing" class="py-24 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl mx-auto bg-slate-50 rounded-[3rem] p-8 md:p-16 text-center ring-1 ring-slate-900/5 reveal">
                        <span class="text-teal-600 font-bold uppercase tracking-widest text-xs">PRICING</span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2 mb-6">初期費用・月額固定費は「0円」</h2>
                        <div class="text-5xl font-black text-slate-900 mb-8 tracking-tight">
                            0<span class="text-2xl font-bold text-slate-400 ml-1">円 ~</span>
                        </div>
                        <p class="text-lg text-slate-600 leading-relaxed mb-10">
                            使った分だけのシンプルな料金体系。決済が発生した時のみ、<span class="text-slate-900 font-bold underline decoration-teal-500">決済手数料 5%</span> を頂戴します。<br class="hidden sm:block">
                            売上がない月に費用は一切かかりません。
                        </p>
                        <div class="flex flex-wrap justify-center gap-4 mb-10">
                            <div class="px-4 py-2 bg-white rounded-full text-sm font-medium text-slate-500 ring-1 ring-slate-200">
                                Stripe決済手数料 <span class="text-slate-900">3.6%</span>
                            </div>
                            <div class="text-slate-300 flex items-center">+</div>
                            <div class="px-4 py-2 bg-white rounded-full text-sm font-medium text-slate-500 ring-1 ring-slate-200">
                                システム利用料 <span class="text-slate-900">1.4%</span>
                            </div>
                        </div>
                        <hr class="border-slate-200 mb-10">
                        <div class="grid sm:grid-cols-2 gap-6 text-left max-w-md mx-auto">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span class="font-medium">初期セットアップ無料</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span class="font-medium">月額料金 0円</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span class="font-medium">24時間 予約受付</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span class="font-medium">Stripe事前決済対応</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="faq" class="py-24 bg-slate-50">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16 reveal">
                        <span class="text-teal-600 font-bold uppercase tracking-widest text-xs">FAQ</span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2 mb-4">よくある質問</h2>
                        <p class="text-lg text-slate-600">導入前にご確認いただきたいポイントをまとめました。</p>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(faq, i) in faqs" :key="i" class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-900/5 overflow-hidden reveal" :style="{ transitionDelay: `${i * 100}ms` }">
                            <button
                                @click="toggleFaq(i)"
                                class="w-full flex items-center justify-between px-6 py-5 sm:px-8 sm:py-6 text-left transition hover:bg-slate-50 group"
                            >
                                <div class="flex items-start gap-4 pr-4">
                                    <span class="flex-shrink-0 w-8 h-8 bg-teal-50 text-teal-600 rounded-full flex items-center justify-center text-sm font-black ring-1 ring-teal-100">Q{{ i + 1 }}</span>
                                    <span class="text-base sm:text-lg font-bold text-slate-800 leading-relaxed">{{ faq.question }}</span>
                                </div>
                                <svg
                                    class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-300"
                                    :class="{ 'rotate-180': openFaqIndex === i }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div
                                class="overflow-hidden transition-all duration-300 ease-in-out"
                                :style="{ maxHeight: openFaqIndex === i ? '500px' : '0px', opacity: openFaqIndex === i ? 1 : 0 }"
                            >
                                <div class="px-6 pb-6 sm:px-8 sm:pb-8 pl-[4.5rem] sm:pl-[5rem]">
                                    <p class="text-slate-600 leading-relaxed">{{ faq.answer }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bottom CTA Section -->
            <section class="py-24 bg-teal-700 relative overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 reveal">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6 leading-tight">
                        まずは専用URLを、<br class="sm:hidden">
                        1週間だけInstagramやSNSに貼ってみてください。
                    </h2>
                    <p class="text-teal-50 text-xl mb-12 max-w-3xl mx-auto opacity-90 leading-relaxed">
                        どれだけDMのストレスが消え、スムーズにお金が入ってくるか、<br class="hidden sm:block">
                        すぐに実感できるはずです。
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                        <Link :href="route('inquiry')" class="w-full sm:w-auto px-10 py-5 bg-white text-teal-700 rounded-2xl font-black text-xl hover:bg-teal-50 transition-all transform hover:-translate-y-1 shadow-2xl">
                            無料で直販サイトをはじめる
                        </Link>
                    </div>
                </div>
                <!-- 背景装飾 -->
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-[600px] h-[600px] bg-white opacity-5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/4 w-[600px] h-[600px] bg-teal-400 opacity-20 rounded-full blur-3xl"></div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-100 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:flex lg:justify-between">
                    <div class="mb-12 lg:mb-0">
                        <div class="flex items-center gap-2 mb-6">
                            <RsvLogo className="w-9 h-9" />
                            <span class="text-xl font-extrabold tracking-tight text-slate-800">RsvBase</span>
                        </div>
                        <p class="text-slate-500 text-sm max-w-xs leading-relaxed">
                            小規模宿泊施設のオーナー様が「時間」と「精神的ゆとり」を取り戻すための極限まで身軽な予約ツール。
                        </p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-2 gap-12 lg:gap-24">
                        <div>
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6">Service</h3>
                            <ul class="space-y-4 text-sm font-medium text-slate-600">
                                <li><a href="#features" class="hover:text-teal-600 transition">特徴</a></li>
                                <li><a href="#steps" class="hover:text-teal-600 transition">導入の流れ</a></li>
                                <li><a href="#pricing" class="hover:text-teal-600 transition">料金体系</a></li>
                                <li><a href="#faq" class="hover:text-teal-600 transition">FAQ</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6">Legal</h3>
                            <ul class="space-y-4 text-sm font-medium text-slate-600">
                                <li><Link :href="route('terms')" class="hover:text-teal-600 transition">利用規約</Link></li>
                                <li><Link :href="route('privacy')" class="hover:text-teal-600 transition">プライバシーポリシー</Link></li>
                                <li><Link :href="route('platform.legal')" class="hover:text-teal-600 transition">特商法に基づく表記</Link></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mt-16 pt-8 border-t border-slate-50 text-center lg:text-left">
                    <p class="text-slate-400 text-xs">
                        &copy; 2026 RsvBase. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.reveal {
    opacity: 0;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromBottom {
    from { transform: translateY(1rem); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.animate-in {
    animation-fill-mode: forwards;
}

.fade-in {
    animation-name: fadeIn;
}

.slide-in-from-bottom-4 {
    animation-name: slideInFromBottom;
}

.duration-1000 {
    animation-duration: 1000ms;
}

.fill-mode-forwards {
    animation-fill-mode: forwards;
}

/* スクロールバーのカスタマイズ */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
