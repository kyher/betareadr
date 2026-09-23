import type { Locale } from './language';

export type User = {
    id: number;
    name: string;
    email: string;
    locale: Locale;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};
