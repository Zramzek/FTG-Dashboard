<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import type { Game, PaginatedData } from "@/types";

const props = defineProps<{
  games: PaginatedData<Game>;
  genres: string[];
  platforms: string[];
  filters: {
    search?: string;
    genre?: string;
    platform?: string;
    sort?: string;
    direction?: string;
  };
}>();

const page = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

// Local filter state
const search = ref(props.filters.search ?? "");
const genreFilter = ref(props.filters.genre ?? "");
const platformFilter = ref(props.filters.platform ?? "");
const sortField = ref(props.filters.sort ?? "updated_at");
const sortDirection = ref(props.filters.direction ?? "desc");

// Sync state
const syncing = ref(false);
const lastSyncTime = ref<string | null>(null);

// Modal state
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingGame = ref<Game | null>(null);
const deletingGame = ref<Game | null>(null);

// Form data
const formData = ref({
  title: "",
  thumbnail: "",
  short_description: "",
  game_url: "",
  genre: "",
  platform: "",
  publisher: "",
  developer: "",
  release_date: "",
  freetogame_profile_url: "",
});

const formErrors = ref<Record<string, string>>({});

// Fetch last sync time
function fetchSyncStatus() {
  fetch("/sync/status")
    .then((r) => r.json())
    .then((data) => {
      lastSyncTime.value = data.last_sync_time;
    });
}
fetchSyncStatus();

// Search with debounce
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (val) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 400);
});

function applyFilters() {
  router.get(
    "/manage",
    {
      search: search.value || undefined,
      genre: genreFilter.value || undefined,
      platform: platformFilter.value || undefined,
      sort: sortField.value,
      direction: sortDirection.value,
    },
    { preserveState: true, preserveScroll: true },
  );
}

function toggleSort(field: string) {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortField.value = field;
    sortDirection.value = "asc";
  }
  applyFilters();
}

function getSortIcon(field: string) {
  if (sortField.value !== field) return "↕";
  return sortDirection.value === "asc" ? "↑" : "↓";
}

// CRUD
function openCreate() {
  formData.value = {
    title: "",
    thumbnail: "",
    short_description: "",
    game_url: "",
    genre: "",
    platform: "",
    publisher: "",
    developer: "",
    release_date: "",
    freetogame_profile_url: "",
  };
  formErrors.value = {};
  showCreateModal.value = true;
}

function openEdit(game: Game) {
  editingGame.value = game;
  formData.value = {
    title: game.title,
    thumbnail: game.thumbnail ?? "",
    short_description: game.short_description ?? "",
    game_url: game.game_url ?? "",
    genre: game.genre ?? "",
    platform: game.platform ?? "",
    publisher: game.publisher ?? "",
    developer: game.developer ?? "",
    release_date: game.release_date ?? "",
    freetogame_profile_url: game.freetogame_profile_url ?? "",
  };
  formErrors.value = {};
  showEditModal.value = true;
}

function openDelete(game: Game) {
  deletingGame.value = game;
  showDeleteModal.value = true;
}

function submitCreate() {
  router.post("/games", formData.value, {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false;
    },
    onError: (errors) => {
      formErrors.value = errors;
    },
  });
}

function submitEdit() {
  if (!editingGame.value) return;
  router.put(`/games/${editingGame.value.id}`, formData.value, {
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
      editingGame.value = null;
    },
    onError: (errors) => {
      formErrors.value = errors;
    },
  });
}

function submitDelete() {
  if (!deletingGame.value) return;
  router.delete(`/games/${deletingGame.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      deletingGame.value = null;
    },
  });
}

function syncData() {
  syncing.value = true;
  router.post(
    "/sync",
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        syncing.value = false;
        fetchSyncStatus();
      },
    },
  );
}

function goToPage(url: string | null) {
  if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
}

function formatDate(dateStr: string | null) {
  if (!dateStr) return "-";
  try {
    return new Date(dateStr).toLocaleDateString("id-ID", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  } catch {
    return dateStr;
  }
}

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
  <Head title="Manage Data" />
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <div>
          <h1 class="text-2xl font-bold text-white">Manage Games</h1>
          <p class="mt-1 text-sm text-slate-400">
            Manage your FreeToGame data collection
          </p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Last sync info -->
          <div class="text-xs text-slate-500" v-if="lastSyncTime">
            Last sync: {{ formatDateTime(lastSyncTime) }}
          </div>
          <!-- Sync button -->
          <button
            @click="syncData"
            :disabled="syncing"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 bg-green-600 text-white hover:shadow-lg hover:shadow-green-500/25 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg
              class="h-4 w-4"
              :class="{ 'animate-spin': syncing }"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 19.644l3.182-3.182"
              />
            </svg>
            {{ syncing ? "Syncing..." : "Sync Data" }}
          </button>
          <!-- Create button -->
          <button
            @click="openCreate"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 bg-blue-600 text-white hover:shadow-lg hover:shadow-indigo-500/25"
          >
            <svg
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 4.5v15m7.5-7.5h-15"
              />
            </svg>
            Add Game
          </button>
        </div>
      </div>

      <!-- Flash messages -->
      <div
        v-if="flash.success"
        class="rounded-xl bg-green-500/10 border border-green-500/20 p-4 text-sm text-green-400"
      >
        {{ flash.success }}
      </div>
      <div
        v-if="flash.error"
        class="rounded-xl bg-red-500/10 border border-red-500/20 p-4 text-sm text-red-400"
      >
        {{ flash.error }}
      </div>

      <!-- Filters -->
      <div
        class="rounded-2xl bg-slate-800/50 backdrop-blur-sm border border-slate-700/50 p-4"
      >
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <input
              v-model="search"
              type="text"
              placeholder="Search games..."
              class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none transition-colors"
            />
          </div>
          <div>
            <select
              v-model="genreFilter"
              @change="applyFilters()"
              class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none transition-colors"
            >
              <option value="">All Genres</option>
              <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
            </select>
          </div>
          <div>
            <select
              v-model="platformFilter"
              @change="applyFilters()"
              class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none transition-colors"
            >
              <option value="">All Platforms</option>
              <option v-for="p in platforms" :key="p" :value="p">
                {{ p }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div
        class="rounded-2xl bg-slate-800/50 backdrop-blur-sm border border-slate-700/50 overflow-hidden"
      >
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-700/50">
            <thead>
              <tr class="bg-slate-900/50">
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                  @click="toggleSort('title')"
                >
                  Title {{ getSortIcon("title") }}
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                  @click="toggleSort('genre')"
                >
                  Genre {{ getSortIcon("genre") }}
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                  @click="toggleSort('platform')"
                >
                  Platform {{ getSortIcon("platform") }}
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                  @click="toggleSort('publisher')"
                >
                  Publisher {{ getSortIcon("publisher") }}
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                  @click="toggleSort('release_date')"
                >
                  Release {{ getSortIcon("release_date") }}
                </th>
                <th
                  class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors"
                  @click="toggleSort('updated_at')"
                >
                  Updated {{ getSortIcon("updated_at") }}
                </th>
                <th
                  class="px-4 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/30">
              <tr
                v-for="game in games.data"
                :key="game.id"
                class="hover:bg-slate-700/20 transition-colors"
              >
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <img
                      v-if="game.thumbnail"
                      :src="game.thumbnail"
                      :alt="game.title"
                      class="h-10 w-16 rounded-lg object-cover bg-slate-700"
                    />
                    <div class="min-w-0">
                      <p
                        class="text-sm font-medium text-white truncate max-w-[200px]"
                      >
                        {{ game.title }}
                      </p>
                      <p class="text-xs text-slate-500 truncate max-w-[200px]">
                        {{ game.developer }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex px-2.5 py-1 rounded-lg bg-indigo-500/10 text-xs font-medium text-indigo-400 border border-indigo-500/20"
                  >
                    {{ game.genre ?? "-" }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-300">
                  {{ game.platform ?? "-" }}
                </td>
                <td
                  class="px-4 py-3 text-sm text-slate-300 truncate max-w-[150px]"
                >
                  {{ game.publisher ?? "-" }}
                </td>
                <td class="px-4 py-3 text-sm text-slate-400">
                  {{ formatDate(game.release_date) }}
                </td>
                <td class="px-4 py-3 text-sm text-slate-400">
                  {{ formatDate(game.updated_at) }}
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="openEdit(game)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-all"
                      title="Edit"
                    >
                      <svg
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"
                        />
                      </svg>
                    </button>
                    <button
                      @click="openDelete(game)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all"
                      title="Delete"
                    >
                      <svg
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
                        />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="games.data.length === 0">
                <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                  <div class="flex flex-col items-center gap-2">
                    <svg
                      class="h-12 w-12 text-slate-600"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="1"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
                      />
                    </svg>
                    <p class="text-sm">
                      No games found. Try syncing data or adjusting your
                      filters.
                    </p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="games.last_page > 1"
          class="flex items-center justify-between border-t border-slate-700/50 px-4 py-3"
        >
          <p class="text-sm text-slate-400">
            Showing {{ games.from ?? 0 }} - {{ games.to ?? 0 }} of
            {{ games.total }}
          </p>
          <div class="flex gap-1">
            <button
              v-for="link in games.links"
              :key="link.label"
              @click="goToPage(link.url)"
              :disabled="!link.url"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
              :class="
                link.active
                  ? 'bg-indigo-600 text-white'
                  : link.url
                    ? 'text-slate-400 hover:bg-slate-700/50 hover:text-white'
                    : 'text-slate-600 cursor-not-allowed'
              "
              v-html="link.label"
            ></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div
        v-if="showCreateModal || showEditModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <div
          class="absolute inset-0 bg-black/60 backdrop-blur-sm"
          @click="
            showCreateModal = false;
            showEditModal = false;
          "
        ></div>
        <div
          class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-slate-800 border border-slate-700/50 shadow-2xl"
        >
          <div
            class="sticky top-0 bg-slate-800 border-b border-slate-700/50 px-6 py-4 flex items-center justify-between"
          >
            <h3 class="text-lg font-semibold text-white">
              {{ showCreateModal ? "Add New Game" : "Edit Game" }}
            </h3>
            <button
              @click="
                showCreateModal = false;
                showEditModal = false;
              "
              class="text-slate-400 hover:text-white transition-colors"
            >
              <svg
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Title *</label
                >
                <input
                  v-model="formData.title"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                />
                <p v-if="formErrors.title" class="mt-1 text-xs text-red-400">
                  {{ formErrors.title }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Genre</label
                >
                <input
                  v-model="formData.genre"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Platform</label
                >
                <select
                  v-model="formData.platform"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                >
                  <option value="">Select Platform</option>
                  <option value="PC (Windows)">PC (Windows)</option>
                  <option value="Web Browser">Web Browser</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Publisher</label
                >
                <input
                  v-model="formData.publisher"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Developer</label
                >
                <input
                  v-model="formData.developer"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Release Date</label
                >
                <input
                  v-model="formData.release_date"
                  type="date"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Thumbnail URL</label
                >
                <input
                  v-model="formData.thumbnail"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Description</label
                >
                <textarea
                  v-model="formData.short_description"
                  rows="3"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none resize-none"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Game URL</label
                >
                <input
                  v-model="formData.game_url"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-300 mb-1"
                  >Profile URL</label
                >
                <input
                  v-model="formData.freetogame_profile_url"
                  class="w-full rounded-xl bg-slate-900/50 border border-slate-700/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"
                />
              </div>
            </div>
            <div
              class="flex justify-end gap-3 pt-4 border-t border-slate-700/50"
            >
              <button
                @click="
                  showCreateModal = false;
                  showEditModal = false;
                "
                class="px-4 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-white transition-colors"
              >
                Cancel
              </button>
              <button
                @click="showCreateModal ? submitCreate() : submitEdit()"
                class="px-6 py-2 rounded-xl text-sm font-medium bg-blue-600 text-white hover:shadow-lg hover:shadow-indigo-500/25 transition-all"
              >
                {{ showCreateModal ? "Create" : "Save Changes" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Modal -->
    <Teleport to="body">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <div
          class="absolute inset-0 bg-black/60 backdrop-blur-sm"
          @click="showDeleteModal = false"
        ></div>
        <div
          class="relative w-full max-w-md rounded-2xl bg-slate-800 border border-slate-700/50 shadow-2xl p-6"
        >
          <div class="text-center">
            <div
              class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10 mb-4"
            >
              <svg
                class="h-6 w-6 text-red-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Delete Game</h3>
            <p class="text-sm text-slate-400 mb-6">
              Are you sure you want to delete
              <strong class="text-white">{{ deletingGame?.title }}</strong
              >? This action cannot be undone.
            </p>
            <div class="flex justify-center gap-3">
              <button
                @click="showDeleteModal = false"
                class="px-4 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-white transition-colors"
              >
                Cancel
              </button>
              <button
                @click="submitDelete()"
                class="px-6 py-2 rounded-xl text-sm font-medium bg-red-600 text-white hover:shadow-lg hover:shadow-red-500/25 transition-all"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>
