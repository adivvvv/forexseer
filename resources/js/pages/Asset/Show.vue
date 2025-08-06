<!-- resources/js/pages/Asset/Show.vue -->
<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-semibold">
      {{ name }} <span class="text-sm text-gray-400">({{ market }})</span>
    </h1>

    <RealTimePrice
      :symbol="symbol"
      :initial-last="initialLast"
      :initial-open="initialOpen"
      :decimals="decimals"
    />

    <p class="text-gray-500">…your chart and other UI…</p>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import RealTimePrice from '@/components/Asset/RealTimePrice.vue'

const {
  props: { name, type, market, symbol, decimals, initialLast, initialOpen }
} = usePage<{
  props: {
    name: string
    type: string
    market: string
    symbol: string
    decimals: number
    initialLast: number|null
    initialOpen: number|null
  }
}>()
</script>