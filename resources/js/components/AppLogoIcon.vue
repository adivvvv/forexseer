<!-- resources/js/components/AppLogoIcon.vue -->
<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

defineOptions({
  inheritAttrs: false,
})

interface Props {
  className?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <img
    src="/images/forexseer-logo.png"
    alt="ForexSeer"
    :class="className"
    v-bind="$attrs"
  />
</template>