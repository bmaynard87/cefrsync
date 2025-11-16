import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AiDisclaimer from '@/components/Chat/AiDisclaimer.vue';

describe('AiDisclaimer', () => {
    it('renders the disclaimer text', () => {
        const wrapper = mount(AiDisclaimer);
        
        expect(wrapper.text()).toContain('AI-generated');
        expect(wrapper.text()).toContain('may contain inaccuracies');
    });

    it('applies correct styling classes', () => {
        const wrapper = mount(AiDisclaimer);
        const container = wrapper.find('[data-test="ai-disclaimer"]');
        
        expect(container.exists()).toBe(true);
        expect(container.classes()).toContain('text-xs');
        expect(container.classes()).toContain('text-gray-500');
    });

    it('includes keyboard shortcut hint', () => {
        const wrapper = mount(AiDisclaimer);
        
        expect(wrapper.text()).toContain('Enter to send');
        expect(wrapper.text()).toContain('Shift+Enter');
    });

    it('is accessible with proper structure', () => {
        const wrapper = mount(AiDisclaimer);
        const container = wrapper.find('[data-test="ai-disclaimer"]');
        
        // Should be a proper semantic container
        expect(container.exists()).toBe(true);
    });

    it('splits content into two sections', () => {
        const wrapper = mount(AiDisclaimer);
        const spans = wrapper.findAll('span');
        
        // Should have two separate spans for shortcuts and disclaimer
        expect(spans.length).toBeGreaterThanOrEqual(2);
    });
});
