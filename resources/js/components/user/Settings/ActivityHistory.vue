<template>
  <div class="bg-white/90 shadow-2xl rounded-2xl border border-indigo-100 mt-8 transition-transform hover:scale-[1.01] duration-200">
    <div class="p-8">
      <h2 class="text-xl font-bold mb-5 flex items-center gap-2 bg-gradient-text-v2">
        <span>📜</span> Lịch sử hoạt động
      </h2>
      <div v-if="activities.length" class="divide-y divide-indigo-50">
        <div v-for="(activity, idx) in paginatedActivities" :key="idx" class="py-3 flex items-center gap-3">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
          </span>
          <div class="flex-1">
            <div class="font-medium text-gray-800">{{ activity.action }}</div>
            <div class="text-xs text-gray-400">{{ formatTime(activity.timestamp) }}</div>
          </div>
        </div>
      </div>
      <div v-else class="text-gray-400 text-center py-6">
        Không có hoạt động nào gần đây.
      </div>

      <!-- Phân trang -->
      <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-6">
        <button 
          @click="currentPage = 1"
          :disabled="currentPage === 1"
          class="px-3 py-1 rounded-lg border border-indigo-200 hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          &laquo;
        </button>
        <button 
          @click="currentPage--"
          :disabled="currentPage === 1"
          class="px-3 py-1 rounded-lg border border-indigo-200 hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          &lsaquo;
        </button>

        <template v-for="page in displayedPages" :key="page">
          <button 
            v-if="page !== '...'"
            @click="currentPage = page"
            :class="[
              'px-3 py-1 rounded-lg border',
              currentPage === page 
                ? 'bg-indigo-500 text-white border-indigo-500' 
                : 'border-indigo-200 hover:bg-indigo-50'
            ]"
          >
            {{ page }}
          </button>
          <span v-else class="px-2">...</span>
        </template>

        <button 
          @click="currentPage++"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 rounded-lg border border-indigo-200 hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          &rsaquo;
        </button>
        <button 
          @click="currentPage = totalPages"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 rounded-lg border border-indigo-200 hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          &raquo;
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { formatTime, calculateDisplayedPages, calculateTotalPages } from '@/utils'

export default {
  name: 'ActivityHistory',
  props: {
    activities: {
      type: Array,
      required: true
    }
  },
  setup(props) {
    const currentPage = ref(1)
    const itemsPerPage = 5

    const activitiesSorted = computed(() => {
      return [...props.activities].sort((a, b) => (b.timestamp || '').localeCompare(a.timestamp || ''))
    })

    const totalPages = computed(() => {
      return calculateTotalPages(activitiesSorted.value.length, itemsPerPage)
    })

    const displayedPages = computed(() => {
      return calculateDisplayedPages(currentPage.value, totalPages.value)
    })

    const paginatedActivities = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage
      const end = start + itemsPerPage
      return activitiesSorted.value.slice(start, end)
    })

    return {
      currentPage,
      totalPages,
      displayedPages,
      paginatedActivities,
      formatTime
    }
  }
}
</script> 