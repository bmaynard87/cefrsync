import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import Register from '@/Pages/Auth/Register.vue';

const mockLanguages = [
    { value: 'en', label: 'English' },
    { value: 'es', label: 'Spanish' },
    { value: 'fr', label: 'French' },
];

describe('Register', () => {
    it('renders registration form with all required fields', () => {
        const wrapper = mount(Register);

        expect(wrapper.find('input[id="first_name"]').exists()).toBe(true);
        expect(wrapper.find('input[id="last_name"]').exists()).toBe(true);
        expect(wrapper.find('input[id="email"]').exists()).toBe(true);
        expect(wrapper.find('select[id="native_language"]').exists()).toBe(true);
        expect(wrapper.find('select[id="target_language"]').exists()).toBe(true);
        expect(wrapper.find('select[id="proficiency_level"]').exists()).toBe(true);
        expect(wrapper.find('input[id="password"]').exists()).toBe(true);
        expect(wrapper.find('input[id="password_confirmation"]').exists()).toBe(true);
    });

    it('displays "Create your account" title', () => {
        const wrapper = mount(Register);

        expect(wrapper.text()).toContain('Create your account');
    });

    it('displays subtitle with sign in link', () => {
        const wrapper = mount(Register);

        expect(wrapper.text()).toContain('Already have an account?');
        const link = wrapper.find('a[href="/login"]');
        expect(link.exists()).toBe(true);
        expect(link.text()).toBe('Sign in');
    });

    it('renders first name field with correct label', () => {
        const wrapper = mount(Register);

        const labels = wrapper.findAll('label');
        const firstNameLabel = labels.find(label => label.text() === 'First Name');
        expect(firstNameLabel).toBeDefined();
    });

    it('renders email field with correct label', () => {
        const wrapper = mount(Register);

        const labels = wrapper.findAll('label');
        const emailLabel = labels.find(label => label.text() === 'Email');
        expect(emailLabel).toBeDefined();
    });

    it('renders native language field with correct label', () => {
        const wrapper = mount(Register);

        expect(wrapper.text()).toContain('Native Language');
    });

    it('renders target language field with correct label', () => {
        const wrapper = mount(Register);

        expect(wrapper.text()).toContain('Target Language');
    });

    it('renders proficiency level field with correct label', () => {
        const wrapper = mount(Register);

        expect(wrapper.text()).toContain('Proficiency Level');
    });

    it('renders password field with correct label', () => {
        const wrapper = mount(Register);

        const labels = wrapper.findAll('label');
        const passwordLabel = labels.find(label => label.text() === 'Password');
        expect(passwordLabel).toBeDefined();
    });

    it('renders password confirmation field with correct label', () => {
        const wrapper = mount(Register);

        const labels = wrapper.findAll('label');
        const confirmLabel = labels.find(label => label.text() === 'Confirm Password');
        expect(confirmLabel).toBeDefined();
    });

    it('autofocuses first name input', () => {
        const wrapper = mount(Register);

        expect(wrapper.find('input[id="first_name"]').attributes('autofocus')).toBeDefined();
    });

    it('marks all fields as required', () => {
        const wrapper = mount(Register);

        expect(wrapper.find('input[id="first_name"]').attributes('required')).toBeDefined();
        expect(wrapper.find('input[id="last_name"]').attributes('required')).toBeDefined();
        expect(wrapper.find('input[id="email"]').attributes('required')).toBeDefined();
        expect(wrapper.find('select[id="native_language"]').attributes('required')).toBeDefined();
        expect(wrapper.find('select[id="target_language"]').attributes('required')).toBeDefined();
        expect(wrapper.find('select[id="proficiency_level"]').attributes('required')).toBeDefined();
        expect(wrapper.find('input[id="password"]').attributes('required')).toBeDefined();
        expect(wrapper.find('input[id="password_confirmation"]').attributes('required')).toBeDefined();
    });

    it('renders create account button', () => {
        const wrapper = mount(Register);

        const button = wrapper.find('button[type="submit"]');
        expect(button.exists()).toBe(true);
        expect(button.text()).toContain('Create account');
    });

    it('filters target language options when native language is selected', async () => {
        const wrapper = mount(Register);

        // Set native language to 'en'
        await wrapper.find('select[id="native_language"]').setValue('en');
        await wrapper.vm.$nextTick();

        // Check that target language select excludes 'en'
        const targetSelect = wrapper.findAll('select[id="target_language"] option');
        const targetValues = targetSelect.map(opt => opt.attributes('value')).filter(val => val);
        
        expect(targetValues).not.toContain('en');
    });

    it('filters native language options when target language is selected', async () => {
        const wrapper = mount(Register);

        // Set target language to 'es'
        await wrapper.find('select[id="target_language"]').setValue('es');
        await wrapper.vm.$nextTick();

        // Check that native language select excludes 'es'
        const nativeSelect = wrapper.findAll('select[id="native_language"] option');
        const nativeValues = nativeSelect.map(opt => opt.attributes('value')).filter(val => val);
        
        expect(nativeValues).not.toContain('es');
    });

    it('renders all proficiency level options', () => {
        const wrapper = mount(Register);

        const proficiencyOptions = wrapper.findAll('select[id="proficiency_level"] option');
        const optionTexts = proficiencyOptions.map(opt => opt.text()).filter(text => text !== 'Select proficiency level');

        expect(optionTexts).toContain('A1 - Beginner');
        expect(optionTexts).toContain('A2 - Elementary');
        expect(optionTexts).toContain('B1 - Intermediate');
        expect(optionTexts).toContain('B2 - Upper Intermediate');
        expect(optionTexts).toContain('C1 - Advanced');
        expect(optionTexts).toContain('C2 - Proficient');
    });

    it('displays validation error for first name', async () => {
        const wrapper = mount(Register);

        wrapper.vm.form.errors.first_name = 'The first name field is required.';
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The first name field is required.');
    });

    it('displays validation error for email', async () => {
        const wrapper = mount(Register);

        wrapper.vm.form.errors.email = 'The email field is required.';
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The email field is required.');
    });

    it('displays validation error for native language', async () => {
        const wrapper = mount(Register);

        wrapper.vm.form.errors.native_language = 'The native language field is required.';
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The native language field is required.');
    });

    it('displays validation error for target language', async () => {
        const wrapper = mount(Register);

        wrapper.vm.form.errors.target_language = 'The target language field is required.';
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The target language field is required.');
    });

    it('displays validation error for password', async () => {
        const wrapper = mount(Register);

        wrapper.vm.form.errors.password = 'The password field is required.';
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The password field is required.');
    });

    it('displays validation error for password confirmation', async () => {
        const wrapper = mount(Register);

        wrapper.vm.form.errors.password_confirmation = 'The password confirmation does not match.';
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The password confirmation does not match.');
    });

    it('shows loading state on submit button when processing', async () => {
        const wrapper = mount(Register);

        wrapper.vm.form.processing = true;
        await wrapper.vm.$nextTick();

        const button = wrapper.find('button[type="submit"]');
        expect(button.attributes('disabled')).toBeDefined();
    });

    it('has autocomplete enabled for first name field', () => {
        const wrapper = mount(Register);

        expect(wrapper.find('input[id="first_name"]').attributes('autocomplete')).toBe('given-name');
    });

    it('has autocomplete enabled for email field', () => {
        const wrapper = mount(Register);

        expect(wrapper.find('input[id="email"]').attributes('autocomplete')).toBe('username');
    });

    it('has autocomplete set for password fields', () => {
        const wrapper = mount(Register);

        expect(wrapper.find('input[id="password"]').attributes('autocomplete')).toBe('new-password');
        expect(wrapper.find('input[id="password_confirmation"]').attributes('autocomplete')).toBe('new-password');
    });
});
