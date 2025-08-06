<!-- resources/js/components/Asset/RealTimePrice.vue -->
<template>
  <Card class="w-full md:w-1/2 lg:w-1/3">
    <CardHeader>
      <CardTitle>Live Price</CardTitle>
      <CardDescription>{{ props.symbol }}</CardDescription>
    </CardHeader>
    <CardContent class="space-y-2">
      <!-- Current price -->
      <div v-if="price !== null" class="text-4xl font-bold">
      <span v-if="props.showDollar">$</span>{{ formattedPrice }}
      </div>

      <!-- % change vs open-of-day -->
      <div
        v-if="changePct !== null"
        :class="changePct >= 0 ? 'text-green-500' : 'text-red-500'"
        class="text-lg font-medium"
      >
        {{ changePct >= 0 ? '+' : '' }}{{ changePct.toFixed(2) }}%
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from '@/components/ui/card'

interface Props {
  symbol: string
  initialLast: number | null
  initialOpen: number | null
  decimals: number
  showDollar: boolean
}
const props = defineProps<Props>()

// State
const price     = ref<number | null>(props.initialLast)
const openPrice = ref<number | null>(props.initialOpen)

// 🎯 Formatted price with thousands separators
const formattedPrice = computed<string>(() => {
  if (price.value === null) return ''
  return price.value.toLocaleString('en-US', {
    minimumFractionDigits: props.decimals,
    maximumFractionDigits: props.decimals,
  })
})

// Computed % change vs open-of-day
const changePct = computed<number | null>(() => {
  if (price.value === null || openPrice.value === null) {
    return null
  }
  return ((price.value - openPrice.value) / openPrice.value) * 100
})

// Real-time via Echo
let channel: any
const echo = (window as any).Echo!

function handleTick(payload: any) {
  const raw = String(payload.s ?? '').replace(/-/g, '')
  if (raw.toUpperCase() !== props.symbol.toUpperCase()) {
    return
  }
  price.value = parseFloat(payload.a ?? payload.p)
}

onMounted(() => {
  channel = echo.channel('ticks')
  channel.listen('.RealTimeTickReceived', handleTick)
})

onUnmounted(() => {
  channel.stopListening('.RealTimeTickReceived')
  echo.leaveChannel('ticks')
})
</script>
