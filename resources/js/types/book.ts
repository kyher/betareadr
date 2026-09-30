import type { ContentLanguage } from './language';

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

export type Book = {
    id: number;
    title: string;
    description: string | null;
    language: ContentLanguage;
    genre: Genre;
    content_warnings: string[];
    cover_image_url: string | null;
};
