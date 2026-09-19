<?php

namespace App\Enums;

enum ContentTopic: string
{
    case SexualContent = 'sexual_content';
    case SexualViolence = 'sexual_violence';
    case Abuse = 'abuse';
    case Violence = 'violence';
    case SelfHarm = 'self_harm';
    case Suicide = 'suicide';
    case EatingDisorders = 'eating_disorders';
    case SubstanceUse = 'substance_use';
    case Discrimination = 'discrimination';

    public function label(): string
    {
        return match ($this) {
            self::SexualContent => 'Sexual content',
            self::SexualViolence => 'Sexual violence',
            self::Abuse => 'Abuse',
            self::Violence => 'Violence',
            self::SelfHarm => 'Self-harm',
            self::Suicide => 'Suicide',
            self::EatingDisorders => 'Eating disorders',
            self::SubstanceUse => 'Substance use',
            self::Discrimination => 'Discrimination',
        };
    }
}
