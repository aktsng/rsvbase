<template>
  <AdminLayout>
    <Head title="オーナー編集" />
    <template #header>オーナー編集: {{ user.name }}</template>

    <div class="max-w-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <form @submit.prevent="submit" class="space-y-6 bg-white p-8 shadow-sm ring-1 ring-slate-200 rounded-2xl">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">名前 <span class="text-red-500">*</span></label>
          <input type="text" v-model="form.name" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
          <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
          <input type="email" v-model="form.email" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
          <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
        </div>

        <div class="pt-4 border-t border-slate-100">
          <p class="text-xs text-slate-500 mb-4">パスワードを変更する場合のみ入力してください。</p>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">新しいパスワード</label>
              <input type="password" v-model="form.password" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">新しいパスワード (確認)</label>
              <input type="password" v-model="form.password_confirmation" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-4 border-t border-slate-200 pt-6">
          <Link :href="route('admin.users.index')" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">キャンセル</Link>
          <button type="submit" :disabled="form.processing" 
                  class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-primary-600 hover:bg-primary-700 transition">
            <span v-if="form.processing">更新中...</span>
            <span v-else>更新する</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>
