import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface LanguageOption {
    value: string;
    label: string;
    native_name?: string;
}

export interface ProficiencyLevelOption {
    value: string;
    label: string;
}

export function useLanguageOptions() {
    const page = usePage();
    
    // Get languages from shared Inertia props
    const languages = computed(() => {
        const langs = (page.props.languages as LanguageOption[]) || [];
        return [
            { value: '', label: 'Select Language' },
            ...langs,
        ];
    });

    const proficiencyLevels: ProficiencyLevelOption[] = [
        { value: '', label: 'Select Level' },
        { value: 'A1', label: 'A1 - Beginner' },
        { value: 'A2', label: 'A2 - Elementary' },
        { value: 'B1', label: 'B1 - Intermediate' },
        { value: 'B2', label: 'B2 - Upper Intermediate' },
        { value: 'C1', label: 'C1 - Advanced' },
        { value: 'C2', label: 'C2 - Proficient' },
        { value: '', label: "I don't know my level" },
    ];

    return {
        languages,
        proficiencyLevels,
    };
}

