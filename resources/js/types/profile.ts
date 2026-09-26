import type { ReadingLanguage } from './language';
import type { Genre } from './book';

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
    preferred_genres?: Genre[];
    content_boundaries: string[];
};
