<template>
  <AuthenticatedLayout>
    <template #sidebar>
      <SidebarLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')" icon="home">
        ダッシュボード
      </SidebarLink>
      <SidebarLink :href="route('admin.facilities.index')" :active="route().current('admin.facilities.*')" icon="building">
        施設管理
      </SidebarLink>
      <SidebarLink :href="route('admin.reservations.index')" :active="route().current('admin.reservations.*')" icon="calendar">
        予約管理
      </SidebarLink>
      <SidebarLink :href="route('admin.users.index')" :active="route().current('admin.users.*')" icon="user">
        オーナー管理
      </SidebarLink>
      <SidebarLink :href="route('admin.webhooks.index')" :active="route().current('admin.webhooks.*')" icon="webhook">
        Webhookログ
      </SidebarLink>
      <SidebarLink :href="route('admin.announcements.index')" :active="route().current('admin.announcements.*')" icon="bell">
        お知らせ管理
      </SidebarLink>
    </template>

    <template #header>
      <slot name="header">管理者ダッシュボード</slot>
    </template>

    <slot />
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SidebarLink from '@/Components/SidebarLink.vue';
</script>
