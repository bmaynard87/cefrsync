import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import FormField from '@/components/FormField.vue';

describe('FormField', () => {
    it('renders label with correct text', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email Address',
                modelValue: '',
            },
        });

        expect(wrapper.find('label').text()).toBe('Email Address');
        expect(wrapper.find('label').attributes('for')).toBe('email');
    });

    it('renders input with correct id', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
            },
        });

        expect(wrapper.find('input').attributes('id')).toBe('email');
    });

    it('sets input type to text by default', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'name',
                label: 'Name',
                modelValue: '',
            },
        });

        expect(wrapper.find('input').attributes('type')).toBe('text');
    });

    it('allows custom input types', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'password',
                label: 'Password',
                type: 'password',
                modelValue: '',
            },
        });

        expect(wrapper.find('input').attributes('type')).toBe('password');
    });

    it('displays placeholder text', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
                placeholder: 'you@example.com',
            },
        });

        expect(wrapper.find('input').attributes('placeholder')).toBe('you@example.com');
    });

    it('marks input as required when prop is true', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
                required: true,
            },
        });

        expect(wrapper.find('input').attributes('required')).toBeDefined();
    });

    it('sets autofocus attribute', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
                autofocus: true,
            },
        });

        expect(wrapper.find('input').attributes('autofocus')).toBeDefined();
    });

    it('sets autocomplete attribute', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
                autocomplete: 'username',
            },
        });

        expect(wrapper.find('input').attributes('autocomplete')).toBe('username');
    });

    it('displays error message when provided', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
                error: 'Email is required',
            },
        });

        expect(wrapper.text()).toContain('Email is required');
        const errorText = wrapper.find('p.text-red-600');
        expect(errorText.exists()).toBe(true);
    });

    it('does not display error paragraph when no error', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
            },
        });

        const errorText = wrapper.find('p.text-red-600');
        expect(errorText.exists()).toBe(false);
    });

    it('applies error border class when error is present', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
                error: 'Invalid email',
            },
        });

        expect(wrapper.find('input').classes()).toContain('border-red-500');
    });

    it('emits update:modelValue on input', async () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: '',
            },
        });

        await wrapper.find('input').setValue('test@example.com');
        
        expect(wrapper.emitted('update:modelValue')).toBeTruthy();
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['test@example.com']);
    });

    it('displays current modelValue', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'Email',
                modelValue: 'existing@example.com',
            },
        });

        expect((wrapper.find('input').element as HTMLInputElement).value).toBe('existing@example.com');
    });
});
