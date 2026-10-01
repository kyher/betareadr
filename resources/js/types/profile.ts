import type { Genre } from './book';
import type { ReadingLanguage } from './language';

export type AuthorProfile = {
    display_name: string;
    handle: string;
    bio: string | null;
    avatar_url: string | null;
};

export type BetaReaderProfile = {
    display_name: string;
    handle: string;
    bio: string | null;
    avatar_url: string | null;
    reading_languages: ReadingLanguage[];
    preferred_genres?: Genre[];
    content_boundaries: string[];
};
