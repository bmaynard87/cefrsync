import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AuthLayout from '@/components/AuthLayout.vue';

describe('AuthLayout', () => {
    it('renders the CefrSync logo and tagline', () => {
        const wrapper = mount(AuthLayout, {
            props: {
                title: 'Test Title',
            },
        });

        expect(wrapper.text()).toContain('CefrSync');
        expect(wrapper.text()).toContain('Language learning companion');
    });

    it('renders the provided title', () => {
        const wrapper = mount(AuthLayout, {
            props: {
                title: 'Welcome Back',
            },
        });

        expect(wrapper.find('h2').text()).toBe('Welcome Back');
    });

    it('renders the subtitle when provided', () => {
        const wrapper = mount(AuthLayout, {
            props: {
                title: 'Test Title',
                subtitle: 'This is a subtitle',
            },
        });

        expect(wrapper.text()).toContain('This is a subtitle');
    });

    it('does not render subtitle paragraph when not provided', () => {
        const wrapper = mount(AuthLayout, {
            props: {
                title: 'Test Title',
            },
        });

        const subtitles = wrapper.findAll('p').filter(p => p.classes().includes('text-sm'));
        const hasSubtitle = subtitles.some(p => !p.text().includes('Language learning companion') && !p.text().includes('©'));
        expect(hasSubtitle).toBe(false);
    });

    it('renders slot content', () => {
        const wrapper = mount(AuthLayout, {
            props: {
                title: 'Test Title',
            },
            slots: {
                default: '<form><input type="text" /></form>',
            },
        });

        expect(wrapper.find('form').exists()).toBe(true);
        expect(wrapper.find('input[type="text"]').exists()).toBe(true);
    });

    it('renders copyright footer with current year', () => {
        const wrapper = mount(AuthLayout, {
            props: {
                title: 'Test Title',
            },
        });

        const currentYear = new Date().getFullYear();
        expect(wrapper.text()).toContain(`© ${currentYear} CefrSync. All rights reserved.`);
    });

    it('applies correct styling classes', () => {
        const wrapper = mount(AuthLayout, {
            props: {
                title: 'Test Title',
            },
        });

        // Check for gradient background
        expect(wrapper.find('.bg-gradient-to-br').exists()).toBe(true);
        
        // Check for card styling
        expect(wrapper.find('.rounded-2xl').exists()).toBe(true);
        expect(wrapper.find('.shadow-xl').exists()).toBe(true);
    });
});
