import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ChatHeader from '@/components/Chat/ChatHeader.vue';

describe('ChatHeader', () => {
    it('renders title and description', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
            },
        });

        expect(wrapper.text()).toContain('Language Exchange Chat');
        expect(wrapper.text()).toContain('Practice your language skills with AI assistance');
    });

    it('displays language parameters correctly', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
            },
        });

        expect(wrapper.text()).toContain('Native:');
        expect(wrapper.text()).toContain('Spanish');
        expect(wrapper.text()).toContain('Target:');
        expect(wrapper.text()).toContain('English');
        expect(wrapper.text()).toContain('Level:');
        expect(wrapper.text()).toContain('B1');
    });

    it('displays full proficiency level label', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
                proficiencyLabel: 'Intermediate',
            },
        });

        expect(wrapper.text()).toContain('B1 (Intermediate)');
    });

    it('renders settings button', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
            },
        });

        const settingsButton = wrapper.find('[data-test="settings-button"]');
        expect(settingsButton.exists()).toBe(true);
        expect(settingsButton.text()).toContain('Settings');
    });

    it('emits settings event when settings button is clicked', async () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
            },
        });

        const settingsButton = wrapper.find('[data-test="settings-button"]');
        await settingsButton.trigger('click');

        expect(wrapper.emitted()).toHaveProperty('settings');
    });

    it('displays language parameters with correct styling classes', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
            },
        });

        const nativeBadge = wrapper.find('[data-test="native-language"]');
        const targetBadge = wrapper.find('[data-test="target-language"]');
        const levelBadge = wrapper.find('[data-test="proficiency-level"]');

        expect(nativeBadge.classes()).toContain('bg-blue-50');
        expect(targetBadge.classes()).toContain('bg-green-50');
        expect(levelBadge.classes()).toContain('bg-purple-50');
    });

    it('displays "Dynamic" with level when auto-update proficiency is enabled', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
                proficiencyLabel: 'Intermediate',
                autoUpdateProficiency: true,
            },
        });

        expect(wrapper.text()).toContain('Dynamic (B1 - Intermediate)');
    });

    it('displays proficiency level normally when auto-update is disabled', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
                proficiencyLabel: 'Intermediate',
                autoUpdateProficiency: false,
            },
        });

        expect(wrapper.text()).toContain('B1 (Intermediate)');
        expect(wrapper.text()).not.toContain('Dynamic');
    });

    it('displays proficiency level normally when auto-update is not provided', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: 'B1',
            },
        });

        expect(wrapper.text()).toContain('B1');
        expect(wrapper.text()).not.toContain('Dynamic');
    });

    it('displays "Not Set" when proficiency level is null', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'Spanish',
                targetLanguage: 'English',
                proficiencyLevel: null,
            },
        });

        expect(wrapper.text()).toContain('Not Set');
    });
});
