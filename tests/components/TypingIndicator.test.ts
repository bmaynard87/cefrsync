import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import TypingIndicator from '@/components/Chat/TypingIndicator.vue';

describe('TypingIndicator', () => {
    it('renders AI avatar', () => {
        const wrapper = mount(TypingIndicator);

        const avatar = wrapper.find('[data-test="typing-avatar"]');
        expect(avatar.exists()).toBe(true);
        expect(avatar.text()).toContain('AI');
    });

    it('displays three animated dots', () => {
        const wrapper = mount(TypingIndicator);

        const dots = wrapper.findAll('[data-test="typing-dot"]');
        expect(dots.length).toBe(3);
    });

    it('has correct styling for message container', () => {
        const wrapper = mount(TypingIndicator);

        const container = wrapper.find('[data-test="typing-container"]');
        expect(container.classes()).toContain('rounded-2xl');
        expect(container.classes()).toContain('bg-white');
    });

    it('has bounce animation on dots', () => {
        const wrapper = mount(TypingIndicator);

        const dots = wrapper.findAll('[data-test="typing-dot"]');
        dots.forEach((dot) => {
            expect(dot.classes()).toContain('animate-bounce');
        });
    });

    it('renders in a flex layout', () => {
        const wrapper = mount(TypingIndicator);

        expect(wrapper.classes()).toContain('flex');
        expect(wrapper.classes()).toContain('gap-4');
    });
});
