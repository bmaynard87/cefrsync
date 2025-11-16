import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CorrectionMessage from '@/components/Chat/CorrectionMessage.vue';

describe('CorrectionMessage', () => {
    const mockCorrectionData = {
        error_type: 'offensive' as const,
        severity: 'critical' as const,
        original_text: 'Tu eres un idiota',
        corrected_text: 'Eres muy inteligente',
        explanation: 'This phrase is offensive. Consider using positive language instead.',
        context: 'The word "idiota" is insulting and should be avoided in polite conversation.',
        recommendations: ['Use positive expressions', 'Practice respectful communication'],
    };

    it('renders correction message with all data', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Critical error detected',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Offensive Language');
        expect(wrapper.text()).toContain('critical');
        expect(wrapper.text()).toContain('Tu eres un idiota');
        expect(wrapper.text()).toContain('Eres muy inteligente');
        expect(wrapper.text()).toContain('This phrase is offensive');
        expect(wrapper.text()).toContain('10:30 AM');
    });

    it('displays error type correctly for offensive', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Offensive Language');
    });

    it('displays error type correctly for meaningless', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    error_type: 'meaningless' as const,
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Unclear Meaning');
    });

    it('displays error type correctly for unnatural', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    error_type: 'unnatural' as const,
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Unnatural Phrasing');
    });

    it('displays error type correctly for archaic', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    error_type: 'archaic' as const,
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Archaic Expression');
    });

    it('displays error type correctly for dangerous', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    error_type: 'dangerous' as const,
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Potentially Harmful');
    });

    it('displays severity badge with correct text', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('critical');
    });

    it('displays medium severity correctly', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    severity: 'medium' as const,
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('medium');
    });

    it('displays high severity correctly', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    severity: 'high' as const,
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('high');
    });

    it('shows original text section', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Original');
        expect(wrapper.text()).toContain(mockCorrectionData.original_text);
    });

    it('shows suggested correction section', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Suggested');
        expect(wrapper.text()).toContain(mockCorrectionData.corrected_text);
    });

    it('displays explanation section', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Explanation');
        expect(wrapper.text()).toContain(mockCorrectionData.explanation);
    });

    it('displays context section', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Context');
        expect(wrapper.text()).toContain(mockCorrectionData.context);
    });

    it('displays recommendations when provided', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('Recommendations');
        expect(wrapper.text()).toContain('Use positive expressions');
        expect(wrapper.text()).toContain('Practice respectful communication');
    });

    it('hides recommendations section when not provided', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    recommendations: undefined,
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).not.toContain('Recommendations');
    });

    it('hides recommendations section when empty array', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    recommendations: [],
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).not.toContain('Recommendations');
    });

    it('renders multiple recommendations as list items', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    recommendations: ['First tip', 'Second tip', 'Third tip'],
                },
                timestamp: '10:30 AM',
            },
        });

        expect(wrapper.text()).toContain('First tip');
        expect(wrapper.text()).toContain('Second tip');
        expect(wrapper.text()).toContain('Third tip');
    });

    it('applies correct styling classes for critical severity', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        const container = wrapper.find('div');
        expect(container.classes()).toContain('border-red-300');
    });

    it('applies correct styling classes for high severity', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    severity: 'high' as const,
                },
                timestamp: '10:30 AM',
            },
        });

        const container = wrapper.find('div');
        expect(container.classes()).toContain('border-orange-300');
    });

    it('applies correct styling classes for medium severity', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: {
                    ...mockCorrectionData,
                    severity: 'medium' as const,
                },
                timestamp: '10:30 AM',
            },
        });

        const container = wrapper.find('div');
        expect(container.classes()).toContain('border-yellow-300');
    });

    it('renders timestamp in footer', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '3:45 PM',
            },
        });

        expect(wrapper.text()).toContain('3:45 PM');
    });

    it('has proper semantic structure with sections', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        // Check that the component has the main sections
        const html = wrapper.html();
        expect(html).toContain('Original');
        expect(html).toContain('Suggested');
        expect(html).toContain('Explanation');
        expect(html).toContain('Context');
    });

    it('uses icons for visual hierarchy', () => {
        const wrapper = mount(CorrectionMessage, {
            props: {
                content: 'Error',
                correctionData: mockCorrectionData,
                timestamp: '10:30 AM',
            },
        });

        // Check for lucide-vue-next icon components
        const html = wrapper.html();
        // Icons should be rendered as SVGs
        expect(html).toContain('svg');
    });
});
