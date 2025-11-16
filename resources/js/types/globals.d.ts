import { AppPageProps } from '@/types/index';
import type { route as ziggyRoute } from 'ziggy-js';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps { }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
        route: typeof ziggyRoute;
    }
}

// Ziggy route helper
declare global {
    const route: typeof ziggyRoute;

    // Google Identity Services SDK
    interface Window {
        google?: {
            accounts: {
                id: {
                    initialize: (config: {
                        client_id: string;
                        callback: (response: any) => void;
                        auto_select?: boolean;
                        cancel_on_tap_outside?: boolean;
                    }) => void;
                    renderButton: (
                        element: HTMLElement,
                        config: {
                            theme?: 'outline' | 'filled_blue' | 'filled_black';
                            size?: 'large' | 'medium' | 'small';
                            text?: 'signin_with' | 'signup_with' | 'continue_with' | 'signin';
                            shape?: 'rectangular' | 'pill' | 'circle' | 'square';
                            logo_alignment?: 'left' | 'center';
                            width?: number;
                            locale?: string;
                        }
                    ) => void;
                    prompt: () => void;
                    disableAutoSelect: () => void;
                };
            };
        };
    }
}

