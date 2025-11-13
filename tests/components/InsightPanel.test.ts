import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { mount, VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import InsightPanel from '@/components/Insights/InsightPanel.vue';
import axios from 'axios';

// Mock axios
vi.mock('axios');
const mockedAxios = vi.mocked(axios, true);

// Mock Inertia
let mockPageProps: any = {};
vi.mock('@inertiajs/vue3', async () => {
    const actual = await vi.importActual('@inertiajs/vue3');
    return {
        ...actual,
        usePage: () => ({
            props: mockPageProps,
        }),
    };
});

// Mock setInterval and clearInterval
vi.useFakeTimers();

describe('InsightPanel', () => {
    let wrapper: VueWrapper;

    beforeEach(() => {
        vi.clearAllMocks();
        vi.clearAllTimers();
    });

    afterEach(() => {
        if (wrapper) {
            wrapper.unmount();
        }
    });

    describe('when user is authenticated', () => {
        beforeEach(() => {
            mockPageProps = {
                auth: {
                    user: {
                        id: 1,
                        name: 'Test User',
                        email: 'test@example.com',
                    },
                },
            };

            mockedAxios.get.mockResolvedValue({
                data: {
                    insights: [],
                    unread_count: 0,
                },
            });

            wrapper = mount(InsightPanel);
        });

        it('fetches insights on mount', async () => {
            await nextTick();
            
            expect(mockedAxios.get).toHaveBeenCalledWith('/insights');
            expect(mockedAxios.get).toHaveBeenCalledTimes(1);
        });

        it('starts polling for insights every 30 seconds', async () => {
            await nextTick();
            
            // Initial fetch on mount
            expect(mockedAxios.get).toHaveBeenCalledTimes(1);
            
            // Fast-forward time by 30 seconds
            vi.advanceTimersByTime(30000);
            await nextTick();
            
            // Should have called again
            expect(mockedAxios.get).toHaveBeenCalledTimes(2);
            
            // Fast-forward another 30 seconds
            vi.advanceTimersByTime(30000);
            await nextTick();
            
            expect(mockedAxios.get).toHaveBeenCalledTimes(3);
        });

        it('cleans up interval when component is unmounted', async () => {
            await nextTick();
            
            const initialCalls = mockedAxios.get.mock.calls.length;
            
            // Unmount the component
            wrapper.unmount();
            
            // Fast-forward time - should not trigger any more calls
            vi.advanceTimersByTime(60000);
            await nextTick();
            
            expect(mockedAxios.get).toHaveBeenCalledTimes(initialCalls);
        });

        it('stops polling when receiving 401 error', async () => {
            await nextTick();
            
            // Mock 401 error with proper axios error structure
            const error = {
                isAxiosError: true,
                response: { status: 401 },
            };
            mockedAxios.get.mockRejectedValueOnce(error);
            (mockedAxios.isAxiosError as any) = () => true;
            
            // Trigger the interval
            vi.advanceTimersByTime(30000);
            await nextTick();
            await nextTick(); // Extra tick for error handling
            
            const callsAfterError = mockedAxios.get.mock.calls.length;
            
            // Fast-forward more time - should not make more calls
            vi.advanceTimersByTime(60000);
            await nextTick();
            
            expect(mockedAxios.get).toHaveBeenCalledTimes(callsAfterError);
        });

        it('updates insights when data changes', async () => {
            const newInsights = [
                {
                    id: 1,
                    insight_type: 'grammar',
                    title: 'Grammar Tip',
                    message: 'Great progress!',
                    data: {},
                    is_read: false,
                    created_at: '2025-01-01',
                },
            ];

            mockedAxios.get.mockResolvedValueOnce({
                data: {
                    insights: newInsights,
                    unread_count: 1,
                },
            });

            // Trigger an interval fetch
            vi.advanceTimersByTime(30000);
            await nextTick();
            await nextTick();

            // Verify the API was called
            expect(mockedAxios.get).toHaveBeenCalledWith('/insights');
        });
    });

    describe('when user is not authenticated', () => {
        beforeEach(() => {
            mockPageProps = {
                auth: {
                    user: null,
                },
            };

            mockedAxios.get.mockResolvedValue({
                data: {
                    insights: [],
                    unread_count: 0,
                },
            });

            wrapper = mount(InsightPanel);
        });

        it('does not fetch insights on mount', async () => {
            await nextTick();
            
            expect(mockedAxios.get).not.toHaveBeenCalled();
        });

        it('does not start polling interval', async () => {
            await nextTick();
            
            // Fast-forward time by several intervals
            vi.advanceTimersByTime(90000);
            await nextTick();
            
            // Should never have called the API
            expect(mockedAxios.get).not.toHaveBeenCalled();
        });
    });

    describe('silent fetching', () => {
        beforeEach(() => {
            mockPageProps = {
                auth: {
                    user: {
                        id: 1,
                        name: 'Test User',
                    },
                },
            };

            mockedAxios.get.mockResolvedValue({
                data: {
                    insights: [],
                    unread_count: 0,
                },
            });

            wrapper = mount(InsightPanel);
        });

        it('makes silent API calls during polling', async () => {
            await nextTick();
            
            const initialCalls = mockedAxios.get.mock.calls.length;
            
            // Trigger silent fetch via interval
            vi.advanceTimersByTime(30000);
            await nextTick();
            
            // Should have made another API call
            expect(mockedAxios.get).toHaveBeenCalledTimes(initialCalls + 1);
        });
    });
});
