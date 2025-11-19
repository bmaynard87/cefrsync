import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import InsightPanel from '@/components/Insights/InsightPanel.vue';

// Mock axios
vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
        delete: vi.fn(),
    },
}));

describe('InsightPanel notification badge pulsing animation', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('adds pulse animation when unread count increases', async () => {
        const wrapper = mount(InsightPanel, {
            global: {
                stubs: {
                    teleport: true,
                },
            },
        });

        // Get initial unread count (should be 0)
        const instance = wrapper.vm as any;
        expect(instance.unreadCount).toBe(0);

        const badge = wrapper.find('.bg-red-600');
        expect(badge.exists()).toBe(false);

        // Simulate initial load completion to enable pulse animation
        instance.hasInitiallyLoaded = true;

        // Simulate unread count increase
        instance.unreadCount = 2;
        await nextTick();

        const newBadge = wrapper.find('.bg-red-600');
        expect(newBadge.exists()).toBe(true);
        expect(newBadge.classes()).toContain('animate-pulse-subtle');
    });

    it('removes pulse animation after 3 seconds', async () => {
        const wrapper = mount(InsightPanel, {
            global: {
                stubs: {
                    teleport: true,
                },
            },
        });

        const instance = wrapper.vm as any;

        // Simulate initial load completion to enable pulse animation
        instance.hasInitiallyLoaded = true;

        // Set unread count
        instance.unreadCount = 2;
        await nextTick();

        const badge = wrapper.find('.bg-red-600');
        expect(badge.classes()).toContain('animate-pulse-subtle');

        // Fast forward 4 seconds
        await vi.advanceTimersByTimeAsync(4000);
        await nextTick();

        expect(badge.classes()).not.toContain('animate-pulse-subtle');
    });

    it('does not pulse when unread count stays the same', async () => {
        const wrapper = mount(InsightPanel, {
            global: {
                stubs: {
                    teleport: true,
                },
            },
        });

        const instance = wrapper.vm as any;

        // Simulate initial load completion to enable pulse animation
        instance.hasInitiallyLoaded = true;

        // Set initial unread count
        instance.unreadCount = 2;
        await nextTick();

        const badge = wrapper.find('.bg-red-600');
        expect(badge.classes()).toContain('animate-pulse-subtle');

        // Wait for animation to end
        await vi.advanceTimersByTimeAsync(4000);
        await nextTick();
        expect(badge.classes()).not.toContain('animate-pulse-subtle');

        // Set same unread count again
        instance.unreadCount = 2;
        await nextTick();

        // Should not pulse again
        expect(badge.classes()).not.toContain('animate-pulse-subtle');
    });

    it('pulses again when unread count increases after previous pulse ended', async () => {
        const wrapper = mount(InsightPanel, {
            global: {
                stubs: {
                    teleport: true,
                },
            },
        });

        const instance = wrapper.vm as any;

        // Simulate initial load completion to enable pulse animation
        instance.hasInitiallyLoaded = true;

        // First increase
        instance.unreadCount = 1;
        await nextTick();

        let badge = wrapper.find('.bg-red-600');
        expect(badge.classes()).toContain('animate-pulse-subtle');

        // Wait for pulse to end
        await vi.advanceTimersByTimeAsync(4000);
        await nextTick();
        expect(badge.classes()).not.toContain('animate-pulse-subtle');

        // Second increase
        instance.unreadCount = 3;
        await nextTick();

        badge = wrapper.find('.bg-red-600');
        expect(badge.classes()).toContain('animate-pulse-subtle');
    });

    it('restarts pulse animation if count increases again before timeout', async () => {
        const wrapper = mount(InsightPanel, {
            global: {
                stubs: {
                    teleport: true,
                },
            },
        });

        const instance = wrapper.vm as any;

        // Simulate initial load completion to enable pulse animation
        instance.hasInitiallyLoaded = true;

        // First increase
        instance.unreadCount = 1;
        await nextTick();

        const badge = wrapper.find('.bg-red-600');
        expect(badge.classes()).toContain('animate-pulse-subtle');

        // Wait 1 second
        await vi.advanceTimersByTimeAsync(1000);

        // Second increase before timeout
        instance.unreadCount = 2;
        await nextTick();

        // Should still have pulse class
        expect(badge.classes()).toContain('animate-pulse-subtle');

        // Wait 2 more seconds (only 2 from second increase)
        await vi.advanceTimersByTimeAsync(2000);
        await nextTick();

        // Should still be pulsing
        expect(badge.classes()).toContain('animate-pulse-subtle');

        // Wait 2 more seconds (4 seconds from second increase)
        await vi.advanceTimersByTimeAsync(2000);
        await nextTick();

        // Now it should stop
        expect(badge.classes()).not.toContain('animate-pulse-subtle');
    });

    it('does not pulse when unread count decreases', async () => {
        const wrapper = mount(InsightPanel, {
            global: {
                stubs: {
                    teleport: true,
                },
            },
        });

        const instance = wrapper.vm as any;

        // Simulate initial load completion to enable pulse animation
        instance.hasInitiallyLoaded = true;

        // Set unread count
        instance.unreadCount = 3;
        await nextTick();

        const badge = wrapper.find('.bg-red-600');
        expect(badge.classes()).toContain('animate-pulse-subtle');

        // Wait for pulse to end
        await vi.advanceTimersByTimeAsync(4000);
        await nextTick();
        expect(badge.classes()).not.toContain('animate-pulse-subtle');

        // Decrease count (user reads an insight)
        instance.unreadCount = 2;
        await nextTick();

        // Should not pulse on decrease
        expect(badge.classes()).not.toContain('animate-pulse-subtle');
    });
});
