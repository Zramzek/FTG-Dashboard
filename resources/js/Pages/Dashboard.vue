<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, computed, defineProps } from "vue";
import VueApexCharts from "vue3-apexcharts";
import type { Game } from "@/types";

const props = defineProps<{
  stats: {
    totalGames: number;
    totalGenres: number;
    totalPlatforms: number;
    topGenre: { genre: string; count: number } | null;
    latestGame: Game | null;
  };
  genreDistribution: Array<{ genre: string; count: number }>;
  platformDistribution: Array<{ platform: string; count: number }>;
  gamesPerMonth: Array<{ month: string; count: number }>;
  filters: { range: string };
  lastSyncTime: string | null;
}>();

const selectedRange = ref(props.filters.range ?? "all");

const rangeOptions = [
  { value: "all", label: "All Time" },
  { value: "1m", label: "Last 1 Month" },
  { value: "3m", label: "Last 3 Months" },
  { value: "6m", label: "Last 6 Months" },
  { value: "1y", label: "Last 1 Year" },
  { value: "2y", label: "Last 2 Years" },
  { value: "3y", label: "Last 3 Years" },
  { value: "5y", label: "Last 5 Years" },
];

function applyRange(range: string) {
  selectedRange.value = range;
  router.get(
    "/",
    { range: range === "all" ? undefined : range },
    { preserveState: true, preserveScroll: true },
  );
}

const pieOptions = computed(() => ({
  chart: {
    type: "pie" as const,
    background: "transparent",
    foreColor: "#94a3b8",
  },
  labels: props.genreDistribution.map((g: { genre: string }) => g.genre),
  legend: {
    position: "bottom" as const,
    fontSize: "12px",
    labels: { colors: "#94a3b8" },
  },
  stroke: { width: 0 },
  dataLabels: {
    enabled: true,
    style: { fontSize: "11px", fontWeight: 600 },
    dropShadow: { enabled: false },
  },
  tooltip: { theme: "dark" },
  responsive: [
    {
      breakpoint: 480,
      options: {
        chart: { width: 300 },
        legend: { position: "bottom" as const },
      },
    },
  ],
}));

const pieSeries = computed(() =>
  props.genreDistribution.map((g: { count: number }) => g.count),
);

const columnOptions = computed(() => ({
  chart: {
    type: "bar" as const,
    background: "transparent",
    foreColor: "#94a3b8",
    toolbar: { show: false },
  },
  plotOptions: { bar: { borderRadius: 4, columnWidth: "60%" } },
  xaxis: {
    categories: props.gamesPerMonth.map((g: { month: string }) => g.month),
    labels: { style: { colors: "#94a3b8", fontSize: "11px" } },
    axisBorder: { color: "#334155" },
    axisTicks: { color: "#334155" },
  },
  yaxis: { labels: { style: { colors: "#94a3b8" } } },
  grid: { borderColor: "#1e293b", strokeDashArray: 4 },
  tooltip: { theme: "dark" },
  dataLabels: { enabled: false },
}));

const columnSeries = computed(() => [
  {
    name: "Games Released",
    data: props.gamesPerMonth.map((g: { count: number }) => g.count),
  },
]);

const platformBarOptions = computed(() => ({
  chart: {
    type: "bar" as const,
    background: "transparent",
    foreColor: "#94a3b8",
    toolbar: { show: false },
  },
  plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: "50%" } },
  xaxis: {
    categories: props.platformDistribution.map(
      (p: { platform: string }) => p.platform,
    ),
    labels: { style: { colors: "#94a3b8" } },
    axisBorder: { color: "#334155" },
    axisTicks: { color: "#334155" },
  },
  yaxis: { labels: { style: { colors: "#94a3b8", fontSize: "12px" } } },
  grid: { borderColor: "#1e293b", strokeDashArray: 4 },
  tooltip: { theme: "dark" },
  dataLabels: { enabled: true, style: { fontSize: "11px" } },
}));

const platformBarSeries = computed(() => [
  {
    name: "Games",
    data: props.platformDistribution.map((p: { count: number }) => p.count),
  },
]);

function formatDateTime(dateStr: string | null) {
  if (!dateStr) return "Never";
  try {
    return new Date(dateStr).toLocaleString("id-ID", {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch {
    return dateStr;
  }
}
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout>
    <div class="space-y-6">
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <div>
          <h1 class="text-2xl font-bold text-white">Dashboard</h1>
          <p class="mt-1 text-sm text-slate-400">
            Analytics overview of FreeToGame data
          </p>
        </div>
        <div class="text-xs text-slate-500" v-if="lastSyncTime">
          Last sync: {{ formatDateTime(lastSyncTime) }}
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-400">Total Games</p>
              <p class="mt-2 text-3xl font-bold text-white">
                {{ stats.totalGames.toLocaleString() }}
              </p>
            </div>
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-700/50"
            >
              <i class="pi pi-th-large" style="color: white"></i>
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-400">Total Genres</p>
              <p class="mt-2 text-3xl font-bold text-white">
                {{ stats.totalGenres }}
              </p>
            </div>
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-700/50"
            >
              <i class="pi pi-tag" style="color: white"></i>
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-400">Top Genre</p>
              <p class="mt-2 text-xl font-bold text-white">
                {{ stats.topGenre?.genre ?? "-" }}
              </p>
              <p class="text-xs text-slate-500" v-if="stats.topGenre">
                {{ stats.topGenre.count }} games
              </p>
            </div>
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-700/50"
            >
              <i class="pi pi-crown" style="color: white"></i>
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-5">
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-slate-400">Latest Game</p>
              <p class="mt-2 text-lg font-bold text-white truncate">
                {{ stats.latestGame?.title ?? "-" }}
              </p>
              <p class="text-xs text-slate-500" v-if="stats.latestGame">
                {{ stats.latestGame.release_date }}
              </p>
            </div>
            <div
              class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-700/50 flex-shrink-0"
            >
              <i class="pi pi-chart-pie" style="color: white"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-4">
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-sm font-medium text-slate-300 mr-2">Period:</span>
          <button
            v-for="opt in rangeOptions"
            :key="opt.value"
            @click="applyRange(opt.value)"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-150"
            :class="
              selectedRange === opt.value
                ? 'bg-slate-600 text-white'
                : 'text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'
            "
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-6">
          <h2 class="text-lg font-semibold text-white mb-4">
            Genre Distribution
          </h2>
          <div v-if="genreDistribution.length > 0">
            <VueApexCharts
              type="pie"
              height="380"
              :options="pieOptions"
              :series="pieSeries"
            />
          </div>
          <div
            v-else
            class="flex items-center justify-center h-[380px] text-slate-500 text-sm"
          >
            No data available. Sync games first.
          </div>
        </div>

        <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-6">
          <h2 class="text-lg font-semibold text-white mb-4">
            Games by Release Month
          </h2>
          <div v-if="gamesPerMonth.length > 0">
            <VueApexCharts
              type="bar"
              height="380"
              :options="columnOptions"
              :series="columnSeries"
            />
          </div>
          <div
            v-else
            class="flex items-center justify-center h-[380px] text-slate-500 text-sm"
          >
            No data available. Sync games first.
          </div>
        </div>

        <div
          class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-6 lg:col-span-2"
        >
          <h2 class="text-lg font-semibold text-white mb-4">
            Platform Distribution
          </h2>
          <div v-if="platformDistribution.length > 0">
            <VueApexCharts
              type="bar"
              height="300"
              :options="platformBarOptions"
              :series="platformBarSeries"
            />
          </div>
          <div
            v-else
            class="flex items-center justify-center h-[300px] text-slate-500 text-sm"
          >
            No data available. Sync games first.
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
