<?php

namespace App\Enums;

enum Genre: string
{
    case ActionAdventure = 'action_adventure';
    case Biography = 'biography';
    case Childrens = 'childrens';
    case ContemporaryFiction = 'contemporary_fiction';
    case Crime = 'crime';
    case Erotica = 'erotica';
    case Fantasy = 'fantasy';
    case GraphicNovel = 'graphic_novel';
    case HistoricalFiction = 'historical_fiction';
    case History = 'history';
    case Horror = 'horror';
    case Humor = 'humor';
    case LiteraryFiction = 'literary_fiction';
    case Memoir = 'memoir';
    case Mystery = 'mystery';
    case Nonfiction = 'nonfiction';
    case Other = 'other';
    case Paranormal = 'paranormal';
    case Poetry = 'poetry';
    case ReligionSpirituality = 'religion_spirituality';
    case Romance = 'romance';
    case ScienceFiction = 'science_fiction';
    case SelfHelp = 'self_help';
    case ThrillerSuspense = 'thriller_suspense';
    case Western = 'western';
    case YoungAdult = 'young_adult';

    public function label(): string
    {
        return match ($this) {
            self::ActionAdventure => 'Action & Adventure',
            self::Biography => 'Biography',
            self::Childrens => "Children's",
            self::ContemporaryFiction => 'Contemporary Fiction',
            self::Crime => 'Crime',
            self::Erotica => 'Erotica',
            self::Fantasy => 'Fantasy',
            self::GraphicNovel => 'Graphic Novel',
            self::HistoricalFiction => 'Historical Fiction',
            self::History => 'History',
            self::Horror => 'Horror',
            self::Humor => 'Humor',
            self::LiteraryFiction => 'Literary Fiction',
            self::Memoir => 'Memoir',
            self::Mystery => 'Mystery',
            self::Nonfiction => 'Nonfiction',
            self::Other => 'Other',
            self::Paranormal => 'Paranormal',
            self::Poetry => 'Poetry',
            self::ReligionSpirituality => 'Religion & Spirituality',
            self::Romance => 'Romance',
            self::ScienceFiction => 'Science Fiction',
            self::SelfHelp => 'Self-help',
            self::ThrillerSuspense => 'Thriller & Suspense',
            self::Western => 'Western',
            self::YoungAdult => 'Young Adult',
        };
    }
}
