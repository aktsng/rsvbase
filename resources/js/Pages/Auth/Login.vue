<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <GuestLayout>
    <Head title="ログイン" />

    <div class="relative z-10">
      <h2 class="text-2xl font-bold text-white mb-8 text-center tracking-tight">管理画面にログイン</h2>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-2">
          <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Email</label>
          <div class="relative group">
            <input id="email" v-model="form.email" type="email" required autofocus
                   class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-slate-500
                          focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500/50 transition-all duration-300"
                   placeholder="example@rsvbase.com">
          </div>
          <p v-if="form.errors.email" class="mt-1 text-xs text-rose-400 font-medium ml-1">{{ form.errors.email }}</p>
        </div>

        <div class="space-y-2">
          <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Password</label>
          <div class="relative">
            <input id="password" v-model="form.password" type="password" required
                   class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-slate-500
                          focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500/50 transition-all duration-300"
                   placeholder="••••••••">
          </div>
          <p v-if="form.errors.password" class="mt-1 text-xs text-rose-400 font-medium ml-1">{{ form.errors.password }}</p>
        </div>

        <div class="flex items-center justify-between px-1">
          <label class="flex items-center cursor-pointer group">
            <input id="remember" v-model="form.remember" type="checkbox"
                   class="w-4 h-4 rounded border-white/20 bg-white/10 text-teal-500 focus:ring-teal-500/50 focus:ring-offset-0 transition-colors">
            <span class="ml-2 text-sm text-slate-400 group-hover:text-slate-300 transition-colors">ログインを記憶</span>
          </label>
          
          <Link v-if="route().has('password.request')" :href="route('password.request')" class="text-sm text-slate-500 hover:text-teal-400 transition-colors">
            忘れた方はこちら
          </Link>
        </div>

        <div class="pt-2">
          <button type="submit" :disabled="form.processing"
                class="w-full py-4 bg-teal-600 hover:bg-teal-500 text-white font-bold rounded-2xl shadow-xl shadow-teal-900/20 active:scale-[0.98] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed group relative overflow-hidden">
            <div class="relative z-10 flex items-center justify-center gap-3">
              <span v-if="form.processing" class="animate-spin w-5 h-5 border-2 border-white/30 border-t-white rounded-full"></span>
              <span>{{ form.processing ? '認証中...' : 'ログイン' }}</span>
            </div>
            <!-- Glass flare effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
          </button>
        </div>
      </form>
      
      <div class="mt-8 text-center px-4">
        <p class="text-xs text-slate-500 leading-relaxed">
          アカウントをお持ちでない場合は、<Link :href="route('inquiry')" class="text-teal-400 font-bold hover:underline">こちら</Link>からお問い合わせください。
        </p>
      </div>
    </div>
  </GuestLayout>
</template>
