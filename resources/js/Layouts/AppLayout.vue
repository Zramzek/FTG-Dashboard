<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import "primeicons/primeicons.css";

const currentPath = computed(() => window.location.pathname);
const mobileMenuOpen = ref(false);

const navItems = [
  { name: "Dashboard", href: "/", icon: "pi pi-chart-bar" },
  { name: "Manage Data", href: "/manage", icon: "pi pi-desktop" },
];
</script>

<template>
  <div class="min-h-screen bg-slate-950">
    <aside
      class="fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0"
      :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex h-full flex-col bg-slate-900 border-r border-slate-800">
        <div
          class="flex h-16 items-center gap-3 px-6 border-b border-slate-800"
        >
          <span class="text-lg font-bold text-slate-100"
            >FreeToGame Dashboard</span
          >
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
          <Link
            v-for="item in navItems"
            :key="item.name"
            :href="item.href"
            class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all duration-200"
            :class="
              currentPath === item.href
                ? 'bg-slate-800 text-white border border-slate-700'
                : 'text-slate-400 hover:bg-slate-800/50 hover:text-white'
            "
          >
            <i :class="item.icon"></i>
            {{ item.name }}
          </Link>
        </nav>

        <div class="border-t border-slate-800 px-4 py-4">
          <p class="text-xs text-slate-600 text-center">
            FreeToGame Dashboard v1.0
          </p>
        </div>
      </div>
    </aside>

    <div
      v-if="mobileMenuOpen"
      class="fixed inset-0 z-40 bg-black/60 lg:hidden"
      @click="mobileMenuOpen = false"
    ></div>

    <div class="lg:pl-64">
      <div
        class="sticky top-0 z-30 flex h-16 items-center gap-4 bg-slate-900 border-b border-slate-800 px-4 lg:hidden"
      >
        <button
          @click="mobileMenuOpen = true"
          class="text-slate-400 hover:text-white transition-colors"
        >
          <svg
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
            />
          </svg>
        </button>
        <span class="text-lg font-bold text-slate-100">FTG Dashboard</span>
      </div>

      <main class="p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
