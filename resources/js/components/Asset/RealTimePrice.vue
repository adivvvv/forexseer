<template>
  <Card class="w-full md:w-1/3">
    <CardHeader>
      <CardTitle>Live Price</CardTitle>
      <CardDescription>{{ props.symbol }}</CardDescription>
    </CardHeader>
    <CardContent class="space-y-2">
      <div v-if="price !== null" class="text-4xl font-bold">
        ${{ price.toFixed(5) }}
      </div>
      <div
        v-if="change !== null"
        :class="change >= 0 ? 'text-green-500' : 'text-red-500'"
        class="text-lg font-medium"
      >
        {{ change >= 0 ? '+' : '' }}{{ change.toFixed(5) }}
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
} from '@/components/ui/card'

// Props (e.g. "BTCUSD")
const props = defineProps<{ symbol: string }>()

// Reactive state
const price  = ref<number | null>(null)
const change = ref<number | null>(null)

let channel: any
const echo = (window as any).Echo

function handleTick(payload: any) {
  // incoming payload has { s: "BTC-USD", p: "...", … }
  const raw = String(payload.s ?? '').replace(/-/g, '')
  if (raw.toUpperCase() !== props.symbol.toUpperCase()) return

  const newPrice = parseFloat(payload.a ?? payload.p)
  const prev     = price.value
  price.value  = newPrice
  change.value = prev != null
    ? +(newPrice - prev).toFixed(5)
    : 0
}

onMounted(() => {
  channel = echo.channel('ticks')
  channel.listen('.RealTimeTickReceived', handleTick)
})

onUnmounted(() => {
  // **only** leave that one channel, don't tear down the whole socket
  channel.stopListening('.RealTimeTickReceived')
  echo.leaveChannel('ticks')
})
</script>