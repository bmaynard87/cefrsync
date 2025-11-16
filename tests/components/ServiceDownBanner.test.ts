import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ServiceDownBanner from '@/components/Chat/ServiceDownBanner.vue';

describe('ServiceDownBanner', () => {
    it('renders warning message when service is down', () => {
        const wrapper = mount(ServiceDownBanner, {
            props: {
                isServiceDown: true,
            },
        });

        expect(wrapper.find('[data-test="service-down-banner"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('Language processing service is currently unavailable');
    });

    it('does not render when service is available', () => {
        const wrapper = mount(ServiceDownBanner, {
            props: {
                isServiceDown: false,
            },
        });

        expect(wrapper.find('[data-test="service-down-banner"]').exists()).toBe(false);
    });

    it('displays helpful message about what is affected', () => {
        const wrapper = mount(ServiceDownBanner, {
            props: {
                isServiceDown: true,
            },
        });

        expect(wrapper.text()).toContain('Chat functionality is temporarily disabled');
    });

    it('applies error styling', () => {
        const wrapper = mount(ServiceDownBanner, {
            props: {
                isServiceDown: true,
            },
        });

        const banner = wrapper.find('[data-test="service-down-banner"]');
        expect(banner.classes()).toContain('bg-red-50');
        expect(banner.classes()).toContain('border-red-200');
    });

    it('includes alert icon', () => {
        const wrapper = mount(ServiceDownBanner, {
            props: {
                isServiceDown: true,
            },
        });

        expect(wrapper.find('[data-test="alert-icon"]').exists()).toBe(true);
    });
});
