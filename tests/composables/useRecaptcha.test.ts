import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { defineComponent, h } from 'vue';
import { mount } from '@vue/test-utils';
import { useRecaptcha } from '@/composables/useRecaptcha';

// Mock usePage from Inertia before importing
vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            recaptcha: {
                siteKey: 'test-site-key',
            },
        },
    }),
}));

describe('useRecaptcha', () => {
    beforeEach(() => {
        // Clean up any existing scripts
        document.querySelectorAll('script[src*="google.com/recaptcha"]').forEach((script) => {
            script.remove();
        });

        // Reset window.grecaptcha
        delete (window as any).grecaptcha;
    });

    it('should load reCAPTCHA script with correct site key', async () => {
        // Mock grecaptcha
        (window as any).grecaptcha = {
            ready: vi.fn((callback) => callback()),
            execute: vi.fn(() => Promise.resolve('test-token')),
        };

        let executeRecaptcha: any;

        const TestComponent = defineComponent({
            setup() {
                const recaptcha = useRecaptcha();
                executeRecaptcha = recaptcha.executeRecaptcha;
                return () => h('div');
            },
        });

        mount(TestComponent);

        const token = await executeRecaptcha('test-action');

        expect(token).toBe('test-token');
        expect((window as any).grecaptcha.execute).toHaveBeenCalledWith('test-site-key', {
            action: 'test-action',
        });
    });

    it('should handle grecaptcha execution errors', async () => {
        // Mock grecaptcha to throw error
        (window as any).grecaptcha = {
            ready: vi.fn((callback) => callback()),
            execute: vi.fn(() => Promise.reject(new Error('Execution failed'))),
        };

        let executeRecaptcha: any;

        const TestComponent = defineComponent({
            setup() {
                const recaptcha = useRecaptcha();
                executeRecaptcha = recaptcha.executeRecaptcha;
                return () => h('div');
            },
        });

        mount(TestComponent);

        await expect(executeRecaptcha('test')).rejects.toThrow('Execution failed');
    });

    it('should set isExecuting flag during execution', async () => {
        // Mock grecaptcha with a promise we can control
        let resolveExecution: (value: string) => void;
        const executionPromise = new Promise<string>((resolve) => {
            resolveExecution = resolve;
        });

        (window as any).grecaptcha = {
            ready: vi.fn((callback) => callback()),
            execute: vi.fn(() => executionPromise),
        };

        let executeRecaptcha: any;
        let isExecuting: any;

        const TestComponent = defineComponent({
            setup() {
                const recaptcha = useRecaptcha();
                executeRecaptcha = recaptcha.executeRecaptcha;
                isExecuting = recaptcha.isExecuting;
                return () => h('div');
            },
        });

        mount(TestComponent);

        // Start execution
        const promise = executeRecaptcha('test');

        // Wait a tick for the promise to start
        await new Promise((resolve) => setTimeout(resolve, 0));

        // Should be executing
        expect(isExecuting.value).toBe(true);

        // Resolve the execution
        resolveExecution!('test-token');
        await promise;

        // Should no longer be executing
        expect(isExecuting.value).toBe(false);
    });
});
