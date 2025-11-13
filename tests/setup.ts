import { expect, vi } from 'vitest';
import { config } from '@vue/test-utils';
import { reactive } from 'vue';

// Mock Inertia.js
const mockInertia = {
    form: (data: any) => reactive({
        ...data,
        errors: {},
        processing: false,
        isDirty: false,
        hasErrors: false,
        submit: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
        get: vi.fn(),
        reset: vi.fn(),
        clearErrors: vi.fn(),
        setError: vi.fn(),
        transform: vi.fn(),
    }),
};

// Mock route helper
const mockRoute = vi.fn((name: string) => `/${name}`) as any;

// Configure Vue Test Utils global mocks
config.global.mocks = {
    route: mockRoute,
};

// Mock Inertia module
vi.mock('@inertiajs/vue3', () => ({
    useForm: mockInertia.form,
    router: {
        visit: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
    },
    Link: {
        name: 'Link',
        template: '<a :href="href"><slot /></a>',
        props: ['href', 'method', 'as'],
    },
    Head: {
        name: 'Head',
        template: '<head><slot /></head>',
    },
    usePage: () => ({
        props: {
            auth: { user: null },
        },
    }),
}));
