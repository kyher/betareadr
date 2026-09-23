import type { ReadingLanguage } from './language';

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
