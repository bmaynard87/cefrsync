import { describe, it, expect, beforeEach } from 'vitest';
import { mount, VueWrapper } from '@vue/test-utils';
import Welcome from '@/Pages/Welcome.vue';

describe('Welcome', () => {
    let wrapper: VueWrapper;

    describe('when user is not authenticated', () => {
        beforeEach(() => {
            wrapper = mount(Welcome, {
                props: {
                    canLogin: true,
                    canRegister: true,
                },
                global: {
                    mocks: {
                        $page: {
                            props: {
                                auth: {
                                    user: null,
                                },
                            },
                        },
                    },
                },
            });
        });

        it('renders the CefrSync branding', () => {
            expect(wrapper.text()).toContain('CefrSync');
        });

        it('displays the hero title with AI-Powered Conversations', () => {
            expect(wrapper.text()).toContain('Master Languages with');
            expect(wrapper.text()).toContain('AI-Powered Conversations');
        });

        it('displays the hero subtitle about CEFR levels', () => {
            expect(wrapper.text()).toContain('Practice real conversations tailored to your CEFR level');
            expect(wrapper.text()).toContain('Get instant feedback, track your progress');
        });

        it('shows login link in header navigation', () => {
            const loginLink = wrapper.findAll('a').find(link => 
                link.text() === 'Log in'
            );
            expect(loginLink).toBeDefined();
        });

        it('shows register button in header navigation', () => {
            const registerButton = wrapper.findAll('a').find(link => 
                link.text() === 'Get Started'
            );
            expect(registerButton).toBeDefined();
        });

        it('shows "Start Learning Free" CTA button in hero section', () => {
            expect(wrapper.text()).toContain('Start Learning Free');
        });

        it('shows "Sign In" button in hero section', () => {
            const signInButtons = wrapper.findAll('a').filter(link => 
                link.text().includes('Sign In')
            );
            expect(signInButtons.length).toBeGreaterThan(0);
        });

        it('displays all 6 feature cards', () => {
            expect(wrapper.text()).toContain('CEFR-Aligned Learning');
            expect(wrapper.text()).toContain('Natural Conversations');
            expect(wrapper.text()).toContain('Instant Feedback');
            expect(wrapper.text()).toContain('Progress Tracking');
            expect(wrapper.text()).toContain('24/7 Availability');
            expect(wrapper.text()).toContain('Multiple Languages');
        });

        it('displays feature card descriptions', () => {
            expect(wrapper.text()).toContain('Conversations adapt to your level from A1 (Beginner) to C2 (Proficient)');
            expect(wrapper.text()).toContain('Engage in realistic dialogues about topics you care about');
            expect(wrapper.text()).toContain('Get immediate corrections and suggestions');
            expect(wrapper.text()).toContain('Monitor your improvement across grammar, vocabulary, fluency');
            expect(wrapper.text()).toContain('Practice whenever inspiration strikes');
            expect(wrapper.text()).toContain('Learn Spanish, French, German, Italian, Japanese');
        });

        it('displays bottom CTA section', () => {
            expect(wrapper.text()).toContain('Ready to Accelerate Your Language Learning?');
            expect(wrapper.text()).toContain('Join thousands of learners practicing with AI-powered conversations');
        });

        it('shows "Get Started for Free" button in bottom CTA', () => {
            expect(wrapper.text()).toContain('Get Started for Free');
        });

        it('displays footer with copyright', () => {
            expect(wrapper.text()).toContain('© 2024 CefrSync');
            expect(wrapper.text()).toContain('Practice languages with confidence');
        });
    });

    describe('when user is authenticated', () => {
        beforeEach(() => {
            wrapper = mount(Welcome, {
                props: {
                    canLogin: true,
                    canRegister: true,
                },
                global: {
                    mocks: {
                        $page: {
                            props: {
                                auth: {
                                    user: {
                                        id: 1,
                                        name: 'Test User',
                                        email: 'test@example.com',
                                    },
                                },
                            },
                        },
                    },
                },
            });
        });

        it('shows "Go to Chat" button in header instead of login/register', () => {
            expect(wrapper.text()).toContain('Go to Chat');
            expect(wrapper.text()).not.toContain('Log in');
            expect(wrapper.text()).not.toContain('Get Started');
        });

        it('shows "Continue Learning" button in bottom CTA instead of register', () => {
            expect(wrapper.text()).toContain('Continue Learning');
            expect(wrapper.text()).not.toContain('Get Started for Free');
        });

        it('does not show hero section CTAs for unauthenticated users', () => {
            // The hero section CTAs should only show when not authenticated
            const heroSection = wrapper.find('main .mx-auto.max-w-4xl');
            expect(heroSection.text()).not.toContain('Start Learning Free');
            expect(heroSection.text()).not.toContain('Sign In');
        });
    });

    describe('when canLogin is false', () => {
        beforeEach(() => {
            wrapper = mount(Welcome, {
                props: {
                    canLogin: false,
                    canRegister: false,
                },
                global: {
                    mocks: {
                        $page: {
                            props: {
                                auth: {
                                    user: null,
                                },
                            },
                        },
                    },
                },
            });
        });

        it('does not show navigation links', () => {
            const nav = wrapper.find('nav');
            expect(nav.exists()).toBe(false);
        });
    });

    describe('when canRegister is false', () => {
        beforeEach(() => {
            wrapper = mount(Welcome, {
                props: {
                    canLogin: true,
                    canRegister: false,
                },
                global: {
                    mocks: {
                        $page: {
                            props: {
                                auth: {
                                    user: null,
                                },
                            },
                        },
                    },
                },
            });
        });

        it('shows login but not register button in header', () => {
            expect(wrapper.text()).toContain('Log in');
            expect(wrapper.text()).not.toContain('Get Started');
        });

        it('does not show bottom CTA when not authenticated', () => {
            // The bottom CTA requires both canLogin and canRegister
            const ctaSection = wrapper.html();
            expect(ctaSection).not.toContain('Get Started for Free');
        });
    });

    describe('page metadata', () => {
        it('sets the correct page title', () => {
            wrapper = mount(Welcome, {
                props: {
                    canLogin: true,
                    canRegister: true,
                },
                global: {
                    mocks: {
                        $page: {
                            props: {
                                auth: {
                                    user: null,
                                },
                            },
                        },
                    },
                },
            });

            const head = wrapper.findComponent({ name: 'Head' });
            expect(head.exists()).toBe(true);
            expect(head.attributes('title')).toBe('Welcome to CefrSync');
        });
    });

    describe('visual elements', () => {
        beforeEach(() => {
            wrapper = mount(Welcome, {
                props: {
                    canLogin: true,
                    canRegister: true,
                },
                global: {
                    mocks: {
                        $page: {
                            props: {
                                auth: {
                                    user: null,
                                },
                            },
                        },
                    },
                },
            });
        });

        it('renders SVG icons for all features', () => {
            const svgs = wrapper.findAll('svg');
            // Logo (1) + 6 feature icons + 2-3 in CTAs = at least 9 SVGs
            expect(svgs.length).toBeGreaterThanOrEqual(9);
        });

        it('applies gradient background styling', () => {
            const mainDiv = wrapper.find('.min-h-screen');
            expect(mainDiv.classes()).toContain('bg-gradient-to-b');
            expect(mainDiv.classes()).toContain('from-blue-50');
            expect(mainDiv.classes()).toContain('to-white');
        });

        it('renders all feature cards with proper structure', () => {
            const featureCards = wrapper.findAll('.rounded-2xl.border.border-gray-200');
            expect(featureCards.length).toBe(6);
        });
    });

    describe('responsive design classes', () => {
        beforeEach(() => {
            wrapper = mount(Welcome, {
                props: {
                    canLogin: true,
                    canRegister: true,
                },
                global: {
                    mocks: {
                        $page: {
                            props: {
                                auth: {
                                    user: null,
                                },
                            },
                        },
                    },
                },
            });
        });

        it('applies responsive grid to features section', () => {
            const featuresGrid = wrapper.find('.sm\\:grid-cols-2.lg\\:grid-cols-3');
            expect(featuresGrid.exists()).toBe(true);
        });

        it('applies responsive text sizing to hero title', () => {
            const heroTitle = wrapper.find('h1');
            expect(heroTitle.classes()).toContain('text-5xl');
            expect(heroTitle.classes()).toContain('sm:text-6xl');
        });

        it('applies responsive layout to hero CTAs', () => {
            const ctaContainer = wrapper.find('.flex-col.sm\\:flex-row');
            expect(ctaContainer.exists()).toBe(true);
        });
    });
});
