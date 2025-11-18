import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ChatHeader from '@/components/Chat/ChatHeader.vue';

describe('ProficiencyBadge display', () => {
    it('displays proficiency level correctly', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'English',
                targetLanguage: 'Spanish',
                proficiencyLevel: 'B1',
                proficiencyLabel: 'Intermediate',
                autoUpdateProficiency: true,
            },
        });

        const badge = wrapper.find('[data-test="proficiency-level"]');
        expect(badge.exists()).toBe(true);
        expect(badge.text()).toContain('B1');
    });

    it('displays "Not Set" when proficiency level is null', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'English',
                targetLanguage: 'Spanish',
                proficiencyLevel: null,
                autoUpdateProficiency: false,
            },
        });

        const badge = wrapper.find('[data-test="proficiency-level"]');
        expect(badge.text()).toContain('Not Set');
    });

    it('displays "Dynamic" prefix when auto-update is enabled', () => {
        const wrapper = mount(ChatHeader, {
            props: {
                nativeLanguage: 'English',
                targetLanguage: 'Spanish',
                proficiencyLevel: 'B1',
                proficiencyLabel: 'Intermediate',
                autoUpdateProficiency: true,
            },
        });

        const badge = wrapper.find('[data-test="proficiency-level"]');
        expect(badge.text()).toContain('Dynamic');
    });
});
