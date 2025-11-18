<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Spinner from '@/components/ui/spinner/Spinner.vue';

interface Insight {
    id: number;
    insight_type: string;
    title: string;
    message: string;
    data: any;
    is_read: boolean;
    created_at: string;
}

interface InsightsData {
    insights: Insight[];
    unread_count: number;
}

const page = usePage();
const insights = ref<Insight[]>([]);
const unreadCount = ref(0);
const isLoading = ref(false);
const isOpen = ref(false);
let pollInterval: number | null = null;

const isAuthenticated = computed(() => !!page.props.auth?.user);

// Pulsing animation for unread count changes
const isPulsing = ref(false);
let pulseTimeout: ReturnType<typeof setTimeout> | null = null;
const hasInitiallyLoaded = ref(false);

    watch(unreadCount, (newValue, oldValue) => {
        // Only pulse if the count increased AND we've completed initial load
        if (hasInitiallyLoaded.value && newValue > oldValue) {
            isPulsing.value = true;
            if (pulseTimeout) {
                clearTimeout(pulseTimeout);
            }
            pulseTimeout = setTimeout(() => {
                isPulsing.value = false;
            }, 4000);
        }
    });const fetchInsights = async (silent = false) => {
    // Don't fetch if user is not authenticated
    if (!isAuthenticated.value) {
        return;
    }

    if (!silent) {
        isLoading.value = true;
    }
    try {
        const { data } = await axios.get<InsightsData>('/insights');

        // Only update if data has changed to prevent unnecessary re-renders
        if (JSON.stringify(insights.value) !== JSON.stringify(data.insights)) {
            insights.value = data.insights;
        }
        if (unreadCount.value !== data.unread_count) {
            unreadCount.value = data.unread_count;
        }
    } catch (error) {
        // If unauthorized, stop polling
        if (axios.isAxiosError(error) && error.response?.status === 401) {
            if (pollInterval !== null) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        } else {
            console.error('Error fetching insights:', error);
        }
    } finally {
        if (!silent) {
            isLoading.value = false;
        }
    }
};

const markAsRead = async (insightId: number) => {
    try {
        await axios.patch(`/insights/${insightId}/read`);

        const insight = insights.value.find(i => i.id === insightId);
        if (insight) {
            insight.is_read = true;
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
    } catch (error) {
        console.error('Error marking insight as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/insights/mark-all-read');

        insights.value.forEach(insight => {
            insight.is_read = true;
        });
        unreadCount.value = 0;
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

const deleteInsight = async (insightId: number) => {
    try {
        await axios.delete(`/insights/${insightId}`);

        const index = insights.value.findIndex(i => i.id === insightId);
        if (index !== -1) {
            const wasUnread = !insights.value[index].is_read;
            insights.value.splice(index, 1);
            if (wasUnread) {
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        }
    } catch (error) {
        console.error('Error deleting insight:', error);
    }
};

const getInsightIcon = (type: string) => {
    switch (type) {
        case 'grammar_pattern':
            return '📝';
        case 'vocabulary_strength':
            return '📚';
        case 'proficiency_suggestion':
            return '🎯';
        default:
            return '💡';
    }
};

const getInsightColor = (type: string) => {
    switch (type) {
        case 'grammar_pattern':
            return 'bg-blue-50 border-blue-200';
        case 'vocabulary_strength':
            return 'bg-green-50 border-green-200';
        case 'proficiency_suggestion':
            return 'bg-purple-50 border-purple-200';
        default:
            return 'bg-gray-50 border-gray-200';
    }
};

const togglePanel = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchInsights();
    }
};

onMounted(() => {
    // Only start polling if user is authenticated
    if (isAuthenticated.value) {
        fetchInsights().finally(() => {
            // Set flag after initial fetch completes (whether successful or not)
            // This allows subsequent count changes to trigger pulse animation
            hasInitiallyLoaded.value = true;
        });
        // Poll for new insights every 30 seconds (silently to avoid flash)
        pollInterval = setInterval(() => fetchInsights(true), 30000) as unknown as number;
    } else {
        // If not authenticated, still set the flag so tests can work
        hasInitiallyLoaded.value = true;
    }
});

onUnmounted(() => {
    // Clean up interval when component is destroyed
    if (pollInterval !== null) {
        clearInterval(pollInterval);
        pollInterval = null;
    }

    // Clean up pulse timeout
    if (pulseTimeout) {
        clearTimeout(pulseTimeout);
    }
});
</script>

<template>
    <div class="relative">
        <!-- Notification Badge Button -->
        <button @click="togglePanel" class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors"
            :class="{ 'text-blue-600': isOpen }">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                </path>
            </svg>
            <span v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/5 -translate-y-1/5 bg-red-600 rounded-full transition-all origin-center"
                :class="{ 'animate-pulse-subtle': isPulsing }">
                {{ unreadCount }}
            </span>
        </button>

        <!-- Insights Panel -->
        <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="isOpen"
                class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-[600px] flex flex-col">
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">LangGPT Insights</h3>
                    <button v-if="insights.length > 0 && unreadCount > 0" @click="markAllAsRead"
                        class="text-sm text-blue-600 hover:text-blue-800">
                        Mark all read
                    </button>
                </div>

                <!-- Insights List -->
                <div class="overflow-y-auto flex-1">
                    <div v-if="isLoading" class="p-8 text-center text-gray-500">
                        <Spinner class="h-8 w-8 mx-auto mb-2" />
                        Loading insights...
                    </div>

                    <div v-else-if="insights.length === 0" class="p-8 text-center text-gray-500">
                        <div class="text-4xl mb-2">💡</div>
                        <p class="font-medium">No insights yet</p>
                        <p class="text-sm mt-1">Keep chatting to receive personalized feedback!</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div v-for="insight in insights" :key="insight.id"
                            class="p-4 hover:bg-gray-50 transition-colors"
                            :class="{ 'bg-blue-50/30': !insight.is_read }">
                            <div class="flex items-start gap-3">
                                <div class="text-2xl flex-shrink-0">
                                    {{ getInsightIcon(insight.insight_type) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="font-semibold text-gray-900 text-sm">
                                            {{ insight.title }}
                                        </h4>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <button v-if="!insight.is_read" @click="markAsRead(insight.id)"
                                                class="text-blue-600 hover:text-blue-800 p-1" title="Mark as read">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                            <button @click="deleteInsight(insight.id)"
                                                class="text-gray-400 hover:text-red-600 p-1" title="Dismiss">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ insight.message }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        {{ new Date(insight.created_at).toLocaleDateString() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Backdrop -->
        <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-40"></div>
    </div>
</template>
