<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $beta_reading_match_id
 * @property int $sender_id
 * @property string $body
 * @property Carbon|null $created_at
 */
#[Fillable(['sender_id', 'body'])]
class ConversationMessage extends Model
{
    public const UPDATED_AT = null;
}
