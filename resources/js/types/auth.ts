export type Locale = 'en' | 'es' | 'fr';
export type ContentLanguage = 'en' | 'es' | 'fr';
export type ReadingLanguage = ContentLanguage | '*';
export type Genre =
    | 'action_adventure'
    | 'biography'
    | 'childrens'
    | 'contemporary_fiction'
    | 'crime'
    | 'erotica'
    | 'fantasy'
    | 'graphic_novel'
    | 'historical_fiction'
    | 'history'
    | 'horror'
    | 'humor'
    | 'literary_fiction'
    | 'memoir'
    | 'mystery'
    | 'nonfiction'
    | 'paranormal'
    | 'poetry'
    | 'religion_spirituality'
    | 'romance'
    | 'science_fiction'
    | 'self_help'
    | 'thriller_suspense'
    | 'western'
    | 'young_adult'
    | 'other';

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

export type Auth = {
    user: User;
    has_author_profile: boolean;
    has_beta_reader_profile: boolean;
};

export type AuthorProfile = {
    display_name: string;
    handle: string;
    bio: string | null;
};

export type BetaReaderProfile = {
    display_name: string;
    handle: string;
    bio: string | null;
    reading_languages: ReadingLanguage[];
    content_boundaries: string[];
};

export type Book = {
    id: number;
    title: string;
    description: string | null;
    language: ContentLanguage;
    genre: Genre | null;
    content_warnings: string[];
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
