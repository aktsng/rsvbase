<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <!-- 代理ログインバー -->
    <div v-if="$page.props.impersonating"
         class="bg-amber-500 text-white text-center py-2 px-4 text-sm font-semibold flex items-center justify-center gap-3 z-50 relative">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>
      </svg>
      代理ログイン中：{{ $page.props.auth.user?.name }}
      <Link :href="route('admin.impersonate.stop')" method="post" as="button"
            class="ml-2 bg-white text-amber-700 px-3 py-1 rounded-full text-xs font-bold hover:bg-amber-50 transition">
        Adminに戻る
      </Link>
    </div>

    <!-- サイドバー -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-white border-r border-slate-200 shadow-sm z-40 transform transition-transform duration-200 lg:translate-x-0"
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
           :style="{ top: $page.props.impersonating ? '40px' : '0' }">
      <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100">
        <RsvLogo className="w-8 h-8" />
        <span class="text-lg font-bold text-slate-800">RsvBase</span>
      </div>
      <div class="flex flex-col h-[calc(100%-80px)]">
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
          <slot name="sidebar" />
        </nav>

        <div class="p-4 border-t border-slate-100">
          <Link :href="route('logout')" method="post" as="button"
                class="flex items-center gap-3 w-full px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 transition-all duration-150">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>ログアウト</span>
          </Link>
        </div>
      </div>
    </aside>

    <!-- メインコンテンツ -->
    <div class="lg:ml-64 transition-all duration-200" :style="{ marginTop: $page.props.impersonating ? '40px' : '0' }">
      <!-- トップバー -->
      <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30">
        <div class="flex items-center justify-between px-6 py-3">
          <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 transition">
              <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>
            <h1 class="text-lg font-semibold text-slate-800">
              <slot name="header" />
            </h1>
          </div>
          <div class="flex items-center gap-4">
            <!-- ログアウトはサイドバーに移動 -->
          </div>
        </div>
      </header>

      <!-- ページコンテンツ -->
      <main class="p-6">
        <!-- フラッシュメッセージ -->
        <div v-if="$page.props.flash.success"
             class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash.error"
             class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ $page.props.flash.error }}
        </div>

        <slot />
      </main>
    </div>

    <!-- モバイルオーバーレイ -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false"
         class="lg:hidden fixed inset-0 bg-black/30 z-30" />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import RsvLogo from '@/Components/RsvLogo.vue';

const sidebarOpen = ref(false);
const page = usePage();

// ページ遷移（URL変更）時にサイドバーを閉じる（主にモバイル向け）
watch(() => page.url, () => {
    sidebarOpen.value = false;
});
</script>
