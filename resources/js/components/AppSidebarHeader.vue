<!-- resources/js/components/AppSidebarHeader.vue -->
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { SidebarTrigger } from '@/components/ui/sidebar'
import { Button } from '@/components/ui/button'        // ← import Button
import type { BreadcrumbItemType } from '@/types'

withDefaults(
  defineProps<{
    breadcrumbs?: BreadcrumbItemType[]
  }>(),
  { breadcrumbs: () => [] }
)

// theme state
const isDark = ref(false)

onMounted(() => {
  const stored = localStorage.getItem('theme')
  if (stored === 'dark' || stored === 'light') {
    isDark.value = stored === 'dark'
  } else {
    isDark.value = document.documentElement.classList.contains('dark')
  }
  document.documentElement.classList.toggle('dark', isDark.value)
})

function toggleTheme() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}
</script>

<template>
  <header
    class="
      flex
      h-16
      shrink-0
      items-center
      gap-2
      border-b
      border-sidebar-border/70
      px-6
      transition-[width,height]
      ease-linear
      group-has-data-[collapsible=icon]/sidebar-wrapper:h-12
      md:px-4
    "
  >
    <div class="flex items-center gap-2 w-full">
      <!-- ForexSeer logo (mobile only) -->
      <img
        src="/images/forexseer-logo.png"
        alt="ForexSeer"
        class="h-10 block md:hidden"
      />

      <!-- hamburger toggle -->
      <SidebarTrigger class="-ml-1" />

      <!-- breadcrumbs -->
      <template v-if="breadcrumbs && breadcrumbs.length">
        <Breadcrumbs :breadcrumbs="breadcrumbs" />
      </template>

      <!-- spacer + theme toggle -->
      <Button
        variant="ghost"
        size="icon"
        class="ml-auto"
        @click="toggleTheme"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
      >
        <template v-if="isDark">
          <!-- sun icon -->
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-7 w-7"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 3v1m0 16v1m8.66-11h-1M4.34 12h-1m15.36 5.36l-.707-.707M6.343 6.343l-.707-.707m12.02 12.02l-.707-.707M6.343 17.657l-.707-.707M12 5a7 7 0 000 14 7 7 0 000-14z"
            />
          </svg>
        </template>
        <template v-else>
          <!-- moon icon -->
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-7 w-7"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
            />
          </svg>
        </template>
      </Button>
    </div>
  </header>
</template>
