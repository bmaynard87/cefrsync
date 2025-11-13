import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import LanguageSelect from '@/components/LanguageSelect.vue';

const mockLanguages = [
    { value: 'en', label: 'English' },
    { value: 'es', label: 'Spanish' },
    { value: 'fr', label: 'French' },
    { value: 'de', label: 'German' },
];

describe('LanguageSelect', () => {
    it('renders label with correct text', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Select Language',
                modelValue: '',
                options: mockLanguages,
            },
        });

        expect(wrapper.find('label').text()).toBe('Select Language');
        expect(wrapper.find('label').attributes('for')).toBe('language');
    });

    it('renders all language options', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
            },
        });

        const options = wrapper.findAll('option');
        // Filter out placeholder if exists
        const languageOptions = options.filter(opt => opt.attributes('value') !== '');
        
        expect(languageOptions).toHaveLength(4);
        expect(languageOptions[0].text()).toBe('English');
        expect(languageOptions[1].text()).toBe('Spanish');
        expect(languageOptions[2].text()).toBe('French');
        expect(languageOptions[3].text()).toBe('German');
    });

    it('renders placeholder option when provided', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
                placeholder: 'Choose a language',
            },
        });

        const placeholderOption = wrapper.find('option[value=""]');
        expect(placeholderOption.exists()).toBe(true);
        expect(placeholderOption.text()).toBe('Choose a language');
        expect(placeholderOption.attributes('disabled')).toBeDefined();
    });

    it('does not render placeholder when not provided', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
            },
        });

        const placeholderOption = wrapper.find('option[value=""]');
        expect(placeholderOption.exists()).toBe(false);
    });

    it('filters out excluded language', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
                excludeValue: 'es',
            },
        });

        const options = wrapper.findAll('option');
        const languageOptions = options.filter(opt => opt.attributes('value') !== '');
        
        expect(languageOptions).toHaveLength(3);
        expect(languageOptions.map(opt => opt.text())).toEqual(['English', 'French', 'German']);
        expect(languageOptions.map(opt => opt.text())).not.toContain('Spanish');
    });

    it('shows all options when excludeValue is not set', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
            },
        });

        const options = wrapper.findAll('option');
        const languageOptions = options.filter(opt => opt.attributes('value') !== '');
        
        expect(languageOptions).toHaveLength(4);
    });

    it('marks select as required when prop is true', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
                required: true,
            },
        });

        expect(wrapper.find('select').attributes('required')).toBeDefined();
    });

    it('displays error message when provided', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
                error: 'Language is required',
            },
        });

        expect(wrapper.text()).toContain('Language is required');
        const errorText = wrapper.find('p.text-red-600');
        expect(errorText.exists()).toBe(true);
    });

    it('applies error border class when error is present', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
                error: 'Invalid selection',
            },
        });

        expect(wrapper.find('select').classes()).toContain('border-red-500');
    });

    it('emits update:modelValue on change', async () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
            },
        });

        await wrapper.find('select').setValue('fr');
        
        expect(wrapper.emitted('update:modelValue')).toBeTruthy();
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['fr']);
    });

    it('displays current modelValue', () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: 'de',
                options: mockLanguages,
            },
        });

        expect((wrapper.find('select').element as HTMLSelectElement).value).toBe('de');
    });

    it('updates filtered options when excludeValue changes', async () => {
        const wrapper = mount(LanguageSelect, {
            props: {
                id: 'language',
                label: 'Language',
                modelValue: '',
                options: mockLanguages,
                excludeValue: 'en',
            },
        });

        let options = wrapper.findAll('option').filter(opt => opt.attributes('value') !== '');
        expect(options).toHaveLength(3);
        expect(options.map(opt => opt.text())).not.toContain('English');

        await wrapper.setProps({ excludeValue: 'fr' });

        options = wrapper.findAll('option').filter(opt => opt.attributes('value') !== '');
        expect(options).toHaveLength(3);
        expect(options.map(opt => opt.text())).toContain('English');
        expect(options.map(opt => opt.text())).not.toContain('French');
    });
});
