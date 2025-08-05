<!-- resources/js/components/AppSidebar.vue -->
<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'

import {
  TrendingUp,
  Repeat,
  BarChart,
  PieChart,
} from 'lucide-vue-next'

import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import TeamSwitcher from '@/components/TeamSwitcher.vue'

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarRail,
} from '@/components/ui/sidebar'

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: 'icon',
})

const data = {
  // user stays the same
  user: {
    name: 'shadcn',
    email: 'm@example.com',
    avatar: '/avatars/shadcn.jpg',
  },

  // only one “team” for branding now
  teams: [
    { name: 'ForexSeer', logo: TrendingUp, plan: 'Free Version' },
  ],

  // **Our new sidebar sections:**
  navMain: [
    {
      title: 'Major Forex Pairs',
      url: '/forex',
      icon: TrendingUp,
      isActive: true, // expanded by default
      items: [
        { title: 'EURUSD', url: '/forex/EURUSD' },
        { title: 'GBPUSD', url: '/forex/GBPUSD' },
        { title: 'USDJPY', url: '/forex/USDJPY' },
        { title: 'USDCHF', url: '/forex/USDCHF' },
        { title: 'USDCAD', url: '/forex/USDCAD' },
        { title: 'AUDUSD', url: '/forex/AUDUSD' },
        { title: 'NZDUSD', url: '/forex/NZDUSD' },
        { title: 'MXNUSD', url: '/forex/MXNUSD' },
        { title: 'BRLUSD', url: '/forex/BRLUSD' },
        { title: 'ZARUSD', url: '/forex/ZARUSD' },
      ],
    },
    {
      title: 'Crosses',
      url: '/forex',
      icon: Repeat,
      items: [
        { title: 'EUR/GBP', url: '/forex/EURGBP' },
        { title: 'EUR/JPY', url: '/forex/EURJPY' },
        { title: 'GBP/JPY', url: '/forex/GBPJPY' },
        { title: 'EUR/AUD', url: '/forex/EURAUD' },
        { title: 'AUD/JPY', url: '/forex/AUDJPY' },
        { title: 'EUR/CAD', url: '/forex/EURCAD' },
        { title: 'CAD/JPY', url: '/forex/CADJPY' },
        { title: 'GBP/AUD', url: '/forex/GBPAUD' },
        { title: 'EUR/NZD', url: '/forex/EURNZD' },
        { title: 'AUD/CAD', url: '/forex/AUDCAD' },
      ],
    },
    {
      title: 'U.S. Stocks',
      url: '/stocks',
      icon: BarChart,
      items: [
        { title: 'NVIDIA (NVDA)', url: '/stocks/NVDA' },
        { title: 'Microsoft (MSFT)', url: '/stocks/MSFT' },
        { title: 'Apple (AAPL)', url: '/stocks/AAPL' },
        { title: 'Amazon (AMZN)', url: '/stocks/AMZN' },
        { title: 'Meta (META)', url: '/stocks/META' },
        { title: 'Broadcom (AVGO)', url: '/stocks/AVGO' },
        { title: 'Alphabet (GOOGL)', url: '/stocks/GOOGL' },
        { title: 'Berkshire Hathaway (BRK.B)', url: '/stocks/BRK.B' },
        { title: 'Tesla (TSLA)', url: '/stocks/TSLA' },
        { title: 'JPMorgan Chase (JPM)', url: '/stocks/JPM' },
        { title: 'Visa (V)', url: '/stocks/V' },
        { title: 'Eli Lilly (LLY)', url: '/stocks/LLY' },
        { title: 'Netflix (NFLX)', url: '/stocks/NFLX' },
        { title: 'Exxon Mobil (XOM)', url: '/stocks/XOM' },
        { title: 'Mastercard (MA)', url: '/stocks/MA' },
        { title: 'Walmart (WMT)', url: '/stocks/WMT' },
        { title: 'Costco (COST)', url: '/stocks/COST' },
        { title: 'Oracle (ORCL)', url: '/stocks/ORCL' },
        { title: 'Johnson & Johnson (JNJ)', url: '/stocks/JNJ' },
        { title: 'Home Depot (HD)', url: '/stocks/HD' },
      ],
    },
    {
      title: 'Crypto',
      url: '/crypto',
      icon: PieChart,
      items: [
        { title: 'Bitcoin (BTCUSD)', url: '/crypto/BTCUSD' },
        { title: 'Ether (ETHUSD)', url: '/crypto/ETHUSD' },
        { title: 'Solana (SOLUSD)', url: '/crypto/SOLUSD' },
        { title: 'Ripple (XRPUSD)', url: '/crypto/XRPUSD' },
        { title: 'BNB (BNBUSD)', url: '/crypto/BNBUSD' },
        { title: 'Tron (TRXUSD)', url: '/crypto/TRXUSD' },
        { title: 'Dogecoin (DOGEUSD)', url: '/crypto/DOGEUSD' },
        { title: 'Cardano (ADAUSD)', url: '/crypto/ADAUSD' },
        { title: 'Avalanche (AVAXUSD)', url: '/crypto/AVAXUSD' },
        { title: 'Monero (XMRUSD)', url: '/crypto/XMRUSD' },
      ],
    },
  ],
}
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarHeader>
      <TeamSwitcher :teams="data.teams" />
    </SidebarHeader>

    <SidebarContent>
      <!-- Only one NavMain now, with all four sections -->
      <NavMain :items="data.navMain" />
    </SidebarContent>

    <SidebarFooter>
      <NavUser :user="data.user" />
    </SidebarFooter>

    <SidebarRail />
  </Sidebar>
</template>