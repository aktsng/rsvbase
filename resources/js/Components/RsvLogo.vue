<template>
  <div :class="[className, 'flex items-center justify-center overflow-hidden']">
    <img src="/images/logo.png" alt="RsvBase Logo" class="w-full h-full object-contain" />
  </div>
</template>

<script setup>
defineProps({
  className: {
    type: String,
    default: 'w-8 h-8'
  }
});
</script>
