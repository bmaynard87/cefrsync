import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import TypewriterText from '@/components/ui/TypewriterText.vue';

describe('TypewriterText', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    it('renders empty initially and types out text', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'Hello World',
                speed: 10, // faster for testing
            },
        });

        const visibleText = wrapper.find('[data-test="typewriter-text"]');
        
        // Initially empty (visible portion)
        expect(visibleText.text()).toBe('');

        // First character after initial delay
        await vi.advanceTimersByTimeAsync(1);
        expect(visibleText.text()).toBe('H');

        // Advance to type all remaining characters (10 more chars at 10ms each)
        await vi.advanceTimersByTimeAsync(10 * 10);
        expect(visibleText.text()).toBe('Hello World');
    });

    it('detects RTL languages and applies correct direction', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'مرحبا بك', // Arabic text
                speed: 10,
            },
        });

        const textElement = wrapper.find('[data-test="typewriter-text"]');
        expect(textElement.attributes('dir')).toBe('rtl');
    });

    it('detects LTR languages and applies correct direction', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'Hello World',
                speed: 10,
            },
        });

        const textElement = wrapper.find('[data-test="typewriter-text"]');
        expect(textElement.attributes('dir')).toBe('ltr');
    });

    it('handles Hebrew text as RTL', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'שלום', // Hebrew
                speed: 10,
            },
        });

        const textElement = wrapper.find('[data-test="typewriter-text"]');
        expect(textElement.attributes('dir')).toBe('rtl');
    });

    it('emits complete event when typing finishes', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'Hi',
                speed: 10,
            },
        });

        expect(wrapper.emitted('complete')).toBeUndefined();

        // Type all characters
        await vi.advanceTimersByTimeAsync(10 * 2);

        expect(wrapper.emitted('complete')).toHaveLength(1);
    });

    it('respects custom speed prop', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'AB',
                speed: 100,
            },
        });

        const visibleText = wrapper.find('[data-test="typewriter-text"]');
        expect(visibleText.text()).toBe('');

        // First char after initial delay
        await vi.advanceTimersByTimeAsync(1);
        expect(visibleText.text()).toBe('A');

        // Second char after speed delay
        await vi.advanceTimersByTimeAsync(100);
        expect(visibleText.text()).toBe('AB');
    });

    it('can be disabled to show full text immediately', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'Instant text',
                disabled: true,
            },
        });

        const visibleText = wrapper.find('[data-test="typewriter-text"]');
        
        // Should show full text immediately
        expect(visibleText.text()).toBe('Instant text');
    });

    it('handles empty text', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: '',
                speed: 10,
            },
        });

        expect(wrapper.text()).toBe('');
        
        await vi.advanceTimersByTimeAsync(100);
        expect(wrapper.text()).toBe('');
    });

    it('updates when text prop changes', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'First',
                speed: 10,
            },
        });

        const visibleText = wrapper.find('[data-test="typewriter-text"]');
        
        // Type first text (initial delay + 5 chars)
        await vi.advanceTimersByTimeAsync(1);
        await vi.advanceTimersByTimeAsync(10 * 4);
        expect(visibleText.text()).toBe('First');

        // Change text - it resets and starts typing new text
        await wrapper.setProps({ text: 'Second' });
        expect(visibleText.text()).toBe('');
        
        // After prop change, typing starts with initial delay
        await vi.advanceTimersByTimeAsync(1);
        expect(visibleText.text()).toBe('S');

        // Type remaining text (5 more chars)
        await vi.advanceTimersByTimeAsync(10 * 5);
        expect(visibleText.text()).toBe('Second');
    });

    it('detects mixed RTL content with Arabic characters', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: 'Hello مرحبا',
                speed: 10,
            },
        });

        const textElement = wrapper.find('[data-test="typewriter-text"]');
        // Should be RTL if it contains RTL characters
        expect(textElement.attributes('dir')).toBe('rtl');
    });

    it('applies auto direction when language cannot be determined', async () => {
        const wrapper = mount(TypewriterText, {
            props: {
                text: '123 456',
                speed: 10,
            },
        });

        const textElement = wrapper.find('[data-test="typewriter-text"]');
        expect(textElement.attributes('dir')).toBe('auto');
    });
});
