import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ChatSidebar from '@/components/Chat/ChatSidebar.vue';

describe('ChatSidebar', () => {
    it('renders new chat button', () => {
        const wrapper = mount(ChatSidebar, {
            props: {
                chats: [],
                activeChat: null,
            },
        });

        const newChatButton = wrapper.find('[data-test="new-chat-button"]');
        expect(newChatButton.exists()).toBe(true);
        expect(newChatButton.text()).toContain('New Chat');
    });

    it('displays list of previous chats', () => {
        const chats = [
            { id: 1, title: 'Daily Routine Practice', timestamp: '2:30 PM', isActive: true },
            { id: 2, title: 'Travel Vocabulary', timestamp: 'Yesterday at 4:15 PM', isActive: false },
        ];

        const wrapper = mount(ChatSidebar, {
            props: {
                chats,
                activeChat: 1,
            },
        });

        const chatButtons = wrapper.findAll('[data-test="chat-item"]');
        expect(chatButtons.length).toBe(2);
        expect(chatButtons[0].text()).toContain('Daily Routine Practice');
        expect(chatButtons[1].text()).toContain('Travel Vocabulary');
    });

    it('highlights active chat', () => {
        const chats = [
            { id: 1, title: 'Daily Routine Practice', timestamp: '2:30 PM', isActive: true },
            { id: 2, title: 'Travel Vocabulary', timestamp: 'Yesterday', isActive: false },
        ];

        const wrapper = mount(ChatSidebar, {
            props: {
                chats,
                activeChat: 1,
            },
        });

        const chatButtons = wrapper.findAll('[data-test="chat-item"]');
        expect(chatButtons[0].classes()).toContain('bg-gray-100');
        expect(chatButtons[1].classes()).not.toContain('bg-gray-100');
    });

    it('emits new-chat event when new chat button is clicked', async () => {
        const wrapper = mount(ChatSidebar, {
            props: {
                chats: [],
                activeChat: null,
            },
        });

        const newChatButton = wrapper.find('[data-test="new-chat-button"]');
        await newChatButton.trigger('click');

        expect(wrapper.emitted()).toHaveProperty('new-chat');
    });

    it('emits select-chat event when chat is clicked', async () => {
        const chats = [
            { id: 1, title: 'Daily Routine Practice', timestamp: '2:30 PM', isActive: true },
        ];

        const wrapper = mount(ChatSidebar, {
            props: {
                chats,
                activeChat: null,
            },
        });

        const chatButton = wrapper.find('[data-test="chat-item"]');
        await chatButton.trigger('click');

        expect(wrapper.emitted()).toHaveProperty('select-chat');
        expect(wrapper.emitted('select-chat')?.[0]).toEqual([1]);
    });

    describe('Scrolling behavior', () => {
        it('has scrollable chat list', () => {
            const manyChats = Array.from({ length: 20 }, (_, i) => ({
                id: i + 1,
                title: `Chat ${i + 1}`,
                timestamp: '2:30 PM',
                isActive: false,
            }));

            const wrapper = mount(ChatSidebar, {
                props: {
                    chats: manyChats,
                    activeChat: 1,
                },
            });

            const chatList = wrapper.find('[data-test="chat-list"]');
            expect(chatList.exists()).toBe(true);
            expect(chatList.classes()).toContain('overflow-y-auto');
        });

        it('constrains chat list to viewport height', () => {
            const wrapper = mount(ChatSidebar, {
                props: {
                    chats: [],
                    activeChat: null,
                },
            });

            const chatList = wrapper.find('[data-test="chat-list"]');
            expect(chatList.exists()).toBe(true);
            
            // Should have height constraint
            const classes = chatList.classes().join(' ');
            expect(classes).toMatch(/h-|max-h-|flex-1/);
        });
    });
});
