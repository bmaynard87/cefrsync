import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface ReCaptchaConfig {
    siteKey: string;
}

declare global {
    interface Window {
        grecaptcha: {
            ready: (callback: () => void) => void;
            execute: (siteKey: string, options: { action: string }) => Promise<string>;
        };
    }
}

export function useRecaptcha() {
    const isLoaded = ref(false);
    const isExecuting = ref(false);
    const error = ref<string | null>(null);
    
    // Get the site key from Inertia props
    const page = usePage();
    const siteKey = (page.props.recaptcha as ReCaptchaConfig)?.siteKey;

    /**
     * Load the reCAPTCHA script if not already loaded
     */
    const loadRecaptchaScript = (): Promise<void> => {
        return new Promise((resolve, reject) => {
            // If siteKey is not configured, reject immediately
            if (!siteKey) {
                reject(new Error('reCAPTCHA site key not configured'));
                return;
            }

            // Check if script tag already exists with our site key
            const existingScript = document.querySelector(`script[src*="render=${siteKey}"]`);
            
            if (existingScript) {
                // Script exists, wait for grecaptcha to be ready
                if (window.grecaptcha?.ready) {
                    window.grecaptcha.ready(() => {
                        isLoaded.value = true;
                        resolve();
                    });
                } else {
                    // Script tag exists but not loaded yet, wait for it
                    existingScript.addEventListener('load', () => {
                        if (window.grecaptcha?.ready) {
                            window.grecaptcha.ready(() => {
                                isLoaded.value = true;
                                resolve();
                            });
                        } else {
                            reject(new Error('reCAPTCHA loaded but grecaptcha not available'));
                        }
                    });
                }
                return;
            }

            // Create and load the script
            const script = document.createElement('script');
            script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}`;
            script.async = true;
            script.defer = true;
            
            script.onload = () => {
                if (window.grecaptcha?.ready) {
                    window.grecaptcha.ready(() => {
                        isLoaded.value = true;
                        resolve();
                    });
                } else {
                    reject(new Error('reCAPTCHA script loaded but grecaptcha not available'));
                }
            };
            
            script.onerror = () => {
                error.value = 'Failed to load reCAPTCHA';
                reject(new Error('Failed to load reCAPTCHA script'));
            };

            document.head.appendChild(script);
        });
    };

    /**
     * Execute reCAPTCHA and get a token
     * @param action The action name for this reCAPTCHA execution
     * @returns The reCAPTCHA token, or empty string if reCAPTCHA is not configured
     */
    const executeRecaptcha = async (action: string = 'submit'): Promise<string> => {
        // If no site key configured, skip reCAPTCHA (for testing environments)
        if (!siteKey) {
            console.log('reCAPTCHA site key not configured - skipping verification');
            return '';
        }

        try {
            isExecuting.value = true;
            error.value = null;

            // Ensure script is loaded
            if (!isLoaded.value) {
                await loadRecaptchaScript();
            }

            // Wait for grecaptcha to be ready and execute
            return await new Promise<string>((resolve, reject) => {
                window.grecaptcha.ready(async () => {
                    try {
                        const token = await window.grecaptcha.execute(siteKey, { action });
                        resolve(token);
                    } catch (err) {
                        reject(err);
                    }
                });
            });
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'reCAPTCHA execution failed';
            throw err;
        } finally {
            isExecuting.value = false;
        }
    };

    // Load script on mount
    onMounted(() => {
        if (siteKey) {
            loadRecaptchaScript().catch(console.error);
        }
    });

    return {
        isLoaded,
        isExecuting,
        error,
        executeRecaptcha,
    };
}
