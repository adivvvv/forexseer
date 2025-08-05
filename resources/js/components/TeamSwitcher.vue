<!-- resources/js/components/TeamSwitcher.vue -->
<script setup lang="ts">
import Logo from '@/components/Logo.vue'
</script>

<template>
  <!-- Full header: hidden when collapsed -->
  <div class="flex items-center px-3 py-2 space-x-2 group-data-[collapsible=icon]:hidden">
    <!-- logo at 32×32px -->
    <Logo class="w-14 h-14" />
    <div class="flex flex-col leading-tight">
      <span class="text-lg font-semibold">ForexSeer</span>
      <span class="text-xs text-muted-foreground">Free</span>
    </div>
  </div>

  <!-- Collapsed icon only: visible only when collapsed -->
  <div class="hidden group-data-[collapsible=icon]:flex items-center justify-center p-1">
    <!-- logo at 24×24px -->
    <Logo class="w-6 h-6" />
  </div>
</template>