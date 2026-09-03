import { usePage } from '@inertiajs/vue3';

export function useTranslation() {
    const page = usePage();

    return {
        t: (text: string) => page.props.translations[text] ?? text,
    };
}
