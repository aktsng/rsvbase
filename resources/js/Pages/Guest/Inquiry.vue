<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import BookingLayout from '@/Layouts/BookingLayout.vue';
import { ref } from 'vue';

const isSubmitted = ref(false);

const form = useForm({
    name: '',
    email: '',
    facility_name: '',
    rooms_count: '',
    message: '',
});

const submit = () => {
    form.post(route('inquiry.store'), {
        onSuccess: () => {
            isSubmitted.value = true;
            form.reset();
        },
    });
};
</script>

<template>
    <BookingLayout>
        <Head title="お問い合わせ - RsvBase" />

        <div class="py-24 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <!-- Header Section -->
                <div class="text-center mb-16 animate-in fade-in slide-in-from-bottom-4 duration-700">
                    <span class="inline-block px-4 py-1.5 bg-teal-50 text-teal-700 font-bold text-xs tracking-[0.2em] rounded-full uppercase mb-6">Contact Us</span>
                    <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight mb-6">お問い合わせ</h1>
                    <p class="text-slate-500 text-lg leading-relaxed max-w-2xl mx-auto">
                        RsvBase の導入検討、機能に関するご質問など、<br class="hidden sm:block">お気軽にお仕事の相談をお寄せください。
                    </p>
                </div>

                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-700">
                    <div class="p-8 sm:p-12 lg:p-16">
                        <div v-if="!isSubmitted">
                            <form @submit.prevent="submit" class="space-y-8">
                                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <label for="name" class="block text-sm font-bold text-slate-700 ml-1">お名前 <span class="text-rose-500">*</span></label>
                                        <input type="text" id="name" v-model="form.name" required placeholder="山田 太郎"
                                               class="block w-full px-5 py-4 border border-slate-200 rounded-2xl bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="email" class="block text-sm font-bold text-slate-700 ml-1">メールアドレス <span class="text-rose-500">*</span></label>
                                        <input type="email" id="email" v-model="form.email" required placeholder="example@email.com"
                                               class="block w-full px-5 py-4 border border-slate-200 rounded-2xl bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <label for="facility_name" class="block text-sm font-bold text-slate-700 ml-1">施設名（屋号）</label>
                                        <input type="text" id="facility_name" v-model="form.facility_name" placeholder="ゲストハウスRsv"
                                               class="block w-full px-5 py-4 border border-slate-200 rounded-2xl bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="rooms_count" class="block text-sm font-bold text-slate-700 ml-1">客室数</label>
                                        <select id="rooms_count" v-model="form.rooms_count"
                                                class="block w-full px-5 py-4 border border-slate-200 rounded-2xl bg-slate-50 text-slate-700 focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300">
                                            <option value="">選択してください</option>
                                            <option value="1">1部屋（一棟貸し）</option>
                                            <option value="2-3">2-3部屋</option>
                                            <option value="4-5">4-5部屋</option>
                                            <option value="6+">6部屋以上</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="message" class="block text-sm font-bold text-slate-700 ml-1">お問い合わせ内容 <span class="text-rose-500">*</span></label>
                                    <textarea id="message" v-model="form.message" required rows="6" placeholder="導入に関するご質問や、現在の運営状況などをご記入ください。"
                                              class="block w-full px-5 py-4 border border-slate-200 rounded-2xl bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300"></textarea>
                                </div>

                                <div class="pt-6">
                                    <button type="submit" :disabled="form.processing"
                                            class="w-full py-5 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-2xl shadow-xl shadow-teal-900/10 hover:shadow-teal-900/20 active:scale-[0.99] transition-all duration-300 disabled:opacity-50 group overflow-hidden relative">
                                        <span class="relative z-10 flex items-center justify-center gap-3">
                                            <span>{{ form.processing ? '送信中...' : 'メッセージを送信する' }}</span>
                                        </span>
                                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Success Message -->
                        <div v-else class="text-center py-10 animate-in fade-in zoom-in duration-700">
                            <div class="relative inline-block mb-10">
                                <div class="absolute inset-0 bg-teal-100 rounded-full blur-2xl opacity-60 animate-pulse"></div>
                                <div class="w-24 h-24 bg-teal-50 text-teal-600 rounded-full flex items-center justify-center relative z-10 mx-auto shadow-inner ring-1 ring-teal-100">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <h2 class="text-3xl font-extrabold text-slate-900 mb-6 tracking-tight">メッセージをお預かりしました</h2>
                            <p class="text-slate-500 mb-12 leading-relaxed max-w-md mx-auto text-lg">
                                お問い合わせいただき誠にありがとうございます。<br>
                                内容を確認次第、担当者より2〜3営業日以内にご連絡差し上げます。
                            </p>
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
