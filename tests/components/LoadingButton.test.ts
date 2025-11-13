import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import LoadingButton from '@/components/LoadingButton.vue';

describe('LoadingButton', () => {
    it('renders slot content when not loading', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: false,
            },
            slots: {
                default: 'Sign in',
            },
        });

        expect(wrapper.text()).toContain('Sign in');
        expect(wrapper.text()).not.toContain('Loading');
    });

    it('shows loading text and spinner when loading', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: true,
                loadingText: 'Signing in...',
            },
            slots: {
                default: 'Sign in',
            },
        });

        expect(wrapper.text()).toContain('Signing in...');
        expect(wrapper.text()).not.toContain('Sign in');
        expect(wrapper.find('svg').exists()).toBe(true);
    });

    it('uses default loading text when not provided', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: true,
            },
            slots: {
                default: 'Submit',
            },
        });

        expect(wrapper.text()).toContain('Loading...');
    });

    it('disables button when loading', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: true,
            },
            slots: {
                default: 'Click me',
            },
        });

        expect(wrapper.find('button').attributes('disabled')).toBeDefined();
    });

    it('enables button when not loading', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: false,
            },
            slots: {
                default: 'Click me',
            },
        });

        expect(wrapper.find('button').attributes('disabled')).toBeUndefined();
    });

    it('sets button type to submit by default', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: false,
            },
            slots: {
                default: 'Submit',
            },
        });

        expect(wrapper.find('button').attributes('type')).toBe('submit');
    });

    it('allows custom button type', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: false,
                type: 'button',
            },
            slots: {
                default: 'Cancel',
            },
        });

        expect(wrapper.find('button').attributes('type')).toBe('button');
    });

    it('renders loading spinner with correct classes', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: true,
            },
            slots: {
                default: 'Submit',
            },
        });

        const svg = wrapper.find('svg');
        expect(svg.exists()).toBe(true);
        expect(svg.classes()).toContain('animate-spin');
        expect(svg.classes()).toContain('h-4');
        expect(svg.classes()).toContain('w-4');
    });

    it('applies full width class', () => {
        const wrapper = mount(LoadingButton, {
            props: {
                loading: false,
            },
            slots: {
                default: 'Submit',
            },
        });

        expect(wrapper.find('button').classes()).toContain('w-full');
    });
});
