import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ChatInput from '@/components/Chat/ChatInput.vue';

describe('ChatInput', () => {
    it('renders textarea with placeholder', () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: '',
                disabled: false,
            },
        });

        const textarea = wrapper.find('textarea');
        expect(textarea.exists()).toBe(true);
        expect(textarea.attributes('placeholder')).toContain('Type your message');
    });

    it('displays current input value', async () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: 'Hello world',
                disabled: false,
            },
        });

        const textarea = wrapper.find('textarea');
        expect((textarea.element as HTMLTextAreaElement).value).toBe('Hello world');
    });

    it('emits update:modelValue when text changes', async () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: '',
                disabled: false,
            },
        });

        const textarea = wrapper.find('textarea');
        await textarea.setValue('New message');

        expect(wrapper.emitted('update:modelValue')).toBeTruthy();
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['New message']);
    });

    it('emits send event when send button is clicked', async () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: 'Test message',
                disabled: false,
            },
        });

        const sendButton = wrapper.find('[data-test="send-button"]');
        await sendButton.trigger('click');

        expect(wrapper.emitted('send')).toBeTruthy();
    });

    it('emits send event when Enter key is pressed', async () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: 'Test message',
                disabled: false,
            },
        });

        const textarea = wrapper.find('textarea');
        await textarea.trigger('keydown', { key: 'Enter' });

        expect(wrapper.emitted('send')).toBeTruthy();
    });

    it('does not emit send when Shift+Enter is pressed', async () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: 'Test message',
                disabled: false,
            },
        });

        const textarea = wrapper.find('textarea');
        await textarea.trigger('keydown', { key: 'Enter', shiftKey: true });

        expect(wrapper.emitted('send')).toBeFalsy();
    });

    it('disables send button when input is empty', () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: '',
                disabled: false,
            },
        });

        const sendButton = wrapper.find('[data-test="send-button"]');
        expect(sendButton.attributes('disabled')).toBeDefined();
    });

    it('enables send button when input has text', () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: 'Some text',
                disabled: false,
            },
        });

        const sendButton = wrapper.find('[data-test="send-button"]');
        expect(sendButton.attributes('disabled')).toBeUndefined();
    });

    it('disables send button when disabled prop is true', () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: 'Some text',
                disabled: true,
            },
        });

        const sendButton = wrapper.find('[data-test="send-button"]');
        expect(sendButton.attributes('disabled')).toBeDefined();
    });

    it('disables textarea when disabled prop is true', () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: '',
                disabled: true,
            },
        });

        const textarea = wrapper.find('textarea');
        expect(textarea.attributes('disabled')).toBeDefined();
    });

    it('displays helper text about keyboard shortcuts', () => {
        const wrapper = mount(ChatInput, {
            props: {
                modelValue: '',
                disabled: false,
            },
        });

        expect(wrapper.text()).toContain('Press Enter to send');
        expect(wrapper.text()).toContain('Shift+Enter for new line');
    });
});
