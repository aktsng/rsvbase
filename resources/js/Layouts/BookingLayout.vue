<script setup>
import { Link } from '@inertiajs/vue3';
import RsvLogo from '@/Components/RsvLogo.vue';
import { useI18n } from '@/composables/useI18n.js';

const { t } = useI18n();
</script>

<template>
  <div class="min-h-screen bg-slate-50 selection:bg-teal-100">
    <!-- Header -->
    <header class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <Link :href="route('home')" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
          <RsvLogo className="w-8 h-8 transform rotate-0" />
          <span class="text-xl font-extrabold tracking-tight text-slate-800">RsvBase</span>
        </Link>
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
          <Link :href="route('home')" class="hover:text-teal-700 transition">{{ t('home') }}</Link>
          <Link :href="route('inquiry')" class="hover:text-teal-700 transition">{{ t('inquiry') }}</Link>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="pt-16 min-h-screen flex flex-col">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 pt-16 pb-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start gap-12 mb-16">
          <div class="max-w-xs">
            <div class="flex items-center gap-2 mb-6">
              <RsvLogo className="w-8 h-8" />
              <span class="text-xl font-extrabold tracking-tight text-slate-800">RsvBase</span>
            </div>
            <p class="text-slate-500 text-sm leading-relaxed">
              {{ t('footer_tagline') }}<br>
              {{ t('footer_desc') }}
            </p>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-12">
            <div>
              <h4 class="text-slate-900 font-bold mb-6">{{ t('footer_service') }}</h4>
              <ul class="space-y-4 text-sm text-slate-500">
                <li><Link :href="route('home')" class="hover:text-teal-700">{{ t('home') }}</Link></li>
                <li><Link :href="route('inquiry')" class="hover:text-teal-700">{{ t('inquiry') }}</Link></li>
              </ul>
            </div>
            <div>
              <h4 class="text-slate-900 font-bold mb-6">{{ t('footer_legal') }}</h4>
              <ul class="space-y-4 text-sm text-slate-500">
                <li><Link :href="route('terms')" class="hover:text-teal-700">{{ t('footer_terms') }}</Link></li>
                <li><Link :href="route('privacy')" class="hover:text-teal-700">{{ t('footer_privacy') }}</Link></li>
                <li><Link :href="route('platform.legal')" class="hover:text-teal-700">{{ t('footer_scta') }}</Link></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="pt-8 border-t border-slate-50 text-center">
          <p class="text-slate-400 text-xs tracking-wider">
            &copy; {{ new Date().getFullYear() }} RsvBase. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>
