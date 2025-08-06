<!-- resources/js/pages/Asset/Show.vue -->
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import RealTimePrice from '@/components/Asset/RealTimePrice.vue'

// Destructure exactly what you need from the Inertia page props
const { market, symbol, access } = usePage<{
  props: {
    market: string
    symbol: string
    access: any
  }
}>().props
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">
      {{ symbol }}
      <span class="text-sm text-gray-400">({{ market }})</span>
    </h1>

    <!-- real‐time price card -->
    <RealTimePrice :symbol="symbol" />

    <!-- 
      TODO: drop in your Chart component
      <Chart :market="market" :symbol="symbol" />
    -->

    <div>
      <p class="text-gray-500">Here’s where your asset‐specific UI will go.</p>
    </div>
  </div>
</template>