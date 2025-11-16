import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import SendIcon from '@/components/Icons/SendIcon.vue';

describe('SendIcon', () => {
    it('renders the send icon', () => {
        const wrapper = mount(SendIcon);
        const svg = wrapper.find('svg');
        
        expect(svg.exists()).toBe(true);
    });

    it('has correct default size classes', () => {
        const wrapper = mount(SendIcon);
        const svg = wrapper.find('svg');
        
        expect(svg.classes()).toContain('h-5');
        expect(svg.classes()).toContain('w-5');
    });

    it('accepts custom class prop', () => {
        const wrapper = mount(SendIcon, {
            props: {
                class: 'h-6 w-6 text-blue-500',
            },
        });
        const svg = wrapper.find('svg');
        
        expect(svg.classes()).toContain('h-6');
        expect(svg.classes()).toContain('w-6');
        expect(svg.classes()).toContain('text-blue-500');
    });

    it('renders as Lucide Send icon', () => {
        const wrapper = mount(SendIcon);
        const svg = wrapper.find('svg');
        
        // Lucide icons have standard attributes
        expect(svg.attributes('xmlns')).toBe('http://www.w3.org/2000/svg');
        expect(svg.attributes('viewBox')).toBe('0 0 24 24');
    });
});
