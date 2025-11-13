import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import Login from '@/Pages/Auth/Login.vue';

describe('Login', () => {
    it('renders login form with email and password fields', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        expect(wrapper.find('input[type="email"]').exists()).toBe(true);
        expect(wrapper.find('input[type="password"]').exists()).toBe(true);
    });

    it('displays "Welcome back" title', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        expect(wrapper.text()).toContain('Welcome back');
    });

    it('displays subtitle with sign up link', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        expect(wrapper.text()).toContain("Don't have an account?");
        const link = wrapper.find('a[href="/register"]');
        expect(link.exists()).toBe(true);
        expect(link.text()).toBe('Sign up');
    });

    it('renders email field with correct label', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        const labels = wrapper.findAll('label');
        const emailLabel = labels.find(label => label.text() === 'Email');
        expect(emailLabel).toBeDefined();
    });

    it('renders password field with correct label', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        const labels = wrapper.findAll('label');
        const passwordLabel = labels.find(label => label.text() === 'Password');
        expect(passwordLabel).toBeDefined();
    });

    it('renders remember me checkbox', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        const checkbox = wrapper.find('input[type="checkbox"]');
        expect(checkbox.exists()).toBe(true);
        expect(wrapper.text()).toContain('Remember me');
    });

    it('shows forgot password link when canResetPassword is true', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: true,
                status: '',
            },
        });

        const link = wrapper.find('a');
        const forgotLink = wrapper.findAll('a').find(l => l.text().includes('Forgot'));
        expect(forgotLink).toBeDefined();
    });

    it('hides forgot password link when canResetPassword is false', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        const link = wrapper.find('a[href="/forgot-password"]');
        expect(link.exists()).toBe(false);
    });

    it('displays status message when provided', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: 'Your password has been reset!',
            },
        });

        expect(wrapper.text()).toContain('Your password has been reset!');
    });

    it('renders sign in button', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        const button = wrapper.find('button[type="submit"]');
        expect(button.exists()).toBe(true);
        expect(button.text()).toContain('Sign in');
    });

    it('autofocuses email input', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        expect(wrapper.find('input[type="email"]').attributes('autofocus')).toBeDefined();
    });

    it('marks email and password as required', () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        expect(wrapper.find('input[type="email"]').attributes('required')).toBeDefined();
        expect(wrapper.find('input[type="password"]').attributes('required')).toBeDefined();
    });

    it('displays validation error for email', async () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        wrapper.vm.form.errors.email = 'The email field is required.';
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The email field is required.');
    });

    it('displays validation error for password', async () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        wrapper.vm.form.errors.password = 'The password field is required.';
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The password field is required.');
    });

    it('shows loading state on submit button when processing', async () => {
        const wrapper = mount(Login, {
            props: {
                canResetPassword: false,
                status: '',
            },
        });

        wrapper.vm.form.processing = true;
        await wrapper.vm.$nextTick();

        const button = wrapper.find('button[type="submit"]');
        expect(button.attributes('disabled')).toBeDefined();
    });
});
