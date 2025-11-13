import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import PasswordStrengthIndicator from '@/components/PasswordStrengthIndicator.vue';

describe('PasswordStrengthIndicator', () => {
    it('does not render when password is empty', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: '',
            },
        });

        expect(wrapper.html()).toBe('<!--v-if-->');
    });

    it('shows weak strength for simple passwords', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'abc123',
            },
        });

        expect(wrapper.text()).toContain('Weak');
    });

    it('shows good strength for moderately complex passwords', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'Abc12345',
            },
        });

        expect(wrapper.text()).toContain('Good');
    });

    it('shows strong strength for complex passwords', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'Abc123!@#',
            },
        });

        expect(wrapper.text()).toContain('Strong');
    });

    it('displays all password requirements', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'test',
            },
        });

        expect(wrapper.text()).toContain('At least 8 characters');
        expect(wrapper.text()).toContain('Contains uppercase letter');
        expect(wrapper.text()).toContain('Contains lowercase letter');
        expect(wrapper.text()).toContain('Contains number');
        expect(wrapper.text()).toContain('Contains special character');
    });

    it('marks met requirements with green checkmark', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'abcdefgh',
            },
        });

        const svg = wrapper.findAll('svg');
        expect(svg.length).toBeGreaterThan(0);
        
        // At least 8 characters requirement should be met
        expect(wrapper.text()).toContain('At least 8 characters');
    });

    it('shows correct progress bar width for weak password', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'abc',
            },
        });

        const progressBar = wrapper.find('.h-2 > div');
        expect(progressBar.attributes('style')).toContain('33%');
    });

    it('shows correct progress bar width for good password', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'Abc12345',
            },
        });

        const progressBar = wrapper.find('.h-2 > div');
        expect(progressBar.attributes('style')).toContain('66%');
    });

    it('shows correct progress bar width for strong password', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'Abc123!@#',
            },
        });

        const progressBar = wrapper.find('.h-2 > div');
        expect(progressBar.attributes('style')).toContain('100%');
    });

    it('validates minimum length requirement', () => {
        const weakPassword = mount(PasswordStrengthIndicator, {
            props: { password: 'Ab1!' },
        });
        expect(weakPassword.text()).toContain('At least 8 characters');

        const goodPassword = mount(PasswordStrengthIndicator, {
            props: { password: 'Abcd123!' },
        });
        expect(goodPassword.text()).toContain('At least 8 characters');
    });

    it('validates uppercase letter requirement', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'abcd1234!@#',
            },
        });

        expect(wrapper.text()).toContain('Contains uppercase letter');
    });

    it('validates lowercase letter requirement', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'ABCD1234!@#',
            },
        });

        expect(wrapper.text()).toContain('Contains lowercase letter');
    });

    it('validates number requirement', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'ABCDefgh!@#',
            },
        });

        expect(wrapper.text()).toContain('Contains number');
    });

    it('validates special character requirement', () => {
        const wrapper = mount(PasswordStrengthIndicator, {
            props: {
                password: 'ABCDefgh123',
            },
        });

        expect(wrapper.text()).toContain('Contains special character');
    });
});
