import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ChatMessage from '@/components/Chat/ChatMessage.vue';

describe('ChatMessage', () => {
    it('renders user message with correct styling', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'Hello, how are you?',
                role: 'user',
                timestamp: '2:30 PM',
            },
        });

        const messageContent = wrapper.find('[data-test="message-content"]');
        expect(messageContent.text()).toContain('Hello, how are you?');
        expect(messageContent.classes()).toContain('bg-blue-600');
        expect(messageContent.classes()).toContain('text-white');
    });

    it('renders assistant message with correct styling', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'I am doing well, thank you!',
                role: 'assistant',
                timestamp: '2:31 PM',
            },
        });

        const messageContent = wrapper.find('[data-test="message-content"]');
        expect(messageContent.text()).toContain('I am doing well, thank you!');
        expect(messageContent.classes()).toContain('bg-white');
        expect(messageContent.classes()).not.toContain('bg-blue-600');
    });

    it('displays timestamp', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'Test message',
                role: 'user',
                timestamp: '3:45 PM',
            },
        });

        expect(wrapper.text()).toContain('3:45 PM');
    });

    it('shows analyzing indicator when isAnalyzing is true', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'Test message',
                role: 'assistant',
                timestamp: '2:30 PM',
                isAnalyzing: true,
            },
        });

        expect(wrapper.text()).toContain('Analyzing...');
        expect(wrapper.find('[data-test="analyzing-indicator"]').exists()).toBe(true);
    });

    it('does not show analyzing indicator when isAnalyzing is false', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'Test message',
                role: 'assistant',
                timestamp: '2:30 PM',
                isAnalyzing: false,
            },
        });

        expect(wrapper.text()).not.toContain('Analyzing...');
        expect(wrapper.find('[data-test="analyzing-indicator"]').exists()).toBe(false);
    });

    it('user messages have reverse layout', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'User message',
                role: 'user',
                timestamp: '2:30 PM',
            },
        });

        const container = wrapper.find('[data-test="message-container"]');
        expect(container.classes()).toContain('flex-row-reverse');
    });

    it('assistant messages have normal layout', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'Assistant message',
                role: 'assistant',
                timestamp: '2:30 PM',
            },
        });

        const container = wrapper.find('[data-test="message-container"]');
        expect(container.classes()).not.toContain('flex-row-reverse');
    });

    it('displays correct avatar label for user', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'Test',
                role: 'user',
                timestamp: '2:30 PM',
            },
        });

        const avatar = wrapper.find('[data-test="message-avatar"]');
        expect(avatar.text()).toContain('You');
    });

    it('displays correct avatar label for assistant', () => {
        const wrapper = mount(ChatMessage, {
            props: {
                content: 'Test',
                role: 'assistant',
                timestamp: '2:30 PM',
            },
        });

        const avatar = wrapper.find('[data-test="message-avatar"]');
        expect(avatar.text()).toContain('AI');
    });
});
