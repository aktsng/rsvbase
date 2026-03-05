<script setup>
import RsvLogo from '@/Components/RsvLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-slate-900 flex items-center justify-center p-4 sm:p-6 relative overflow-hidden selection:bg-teal-500/30">
    <!-- Background Accents -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
      <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] bg-teal-500/10 rounded-full blur-[120px]"></div>
      <div class="absolute -bottom-[20%] -right-[10%] w-[60%] h-[60%] bg-indigo-500/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="w-full max-w-md relative z-10 animate-in fade-in zoom-in-95 duration-700">
      <!-- Logo -->
      <div class="text-center mb-10">
        <Link :href="route('home')" class="inline-flex flex-col items-center gap-4 group">
          <div class="relative">
            <div class="absolute inset-0 bg-teal-400 rounded-2xl blur-xl opacity-20 group-hover:opacity-40 transition-opacity"></div>
            <RsvLogo className="w-16 h-16 relative z-10 transform group-hover:scale-105 transition-transform duration-500" />
          </div>
          <div class="space-y-1">
            <span class="text-3xl font-extrabold text-white tracking-tight">RsvBase</span>
            <p class="text-slate-400 text-xs font-medium tracking-[0.2em] uppercase">Owner Access</p>
          </div>
        </Link>
      </div>

      <!-- Card -->
      <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] shadow-2xl p-8 sm:p-10 mb-8 overflow-hidden relative group">
        <!-- Subtle glass reflection -->
        <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent pointer-events-none"></div>
        <slot />
      </div>

      <!-- Footer Help -->
      <div class="text-center flex flex-col gap-6">
        <p class="text-slate-500 text-xs">
          &copy; {{ new Date().getFullYear() }} RsvBase. Simple reservation system.
        </p>
      </div>
    </div>
  </div>
</template>
