<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['nativeLanguage', 'targetLanguage'];

    protected $fillable = [
        'user_id',
        'native_language_id',
        'target_language_id',
        'proficiency_level',
        'localize_insights',
        'localize_corrections',
        'title',
        'last_message_at',
        'conversation_summary',
        'topics_discussed',
        'user_context',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'topics_discussed' => 'array',
        'user_context' => 'array',
        'localize_insights' => 'boolean',
        'localize_corrections' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nativeLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'native_language_id');
    }

    public function targetLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'target_language_id');
    }

    /**
     * Override getAttribute to provide backward compatibility for language strings
     */
    public function getAttribute($key)
    {
        if ($key === 'native_language') {
            return $this->nativeLanguage?->name;
        }

        if ($key === 'target_language') {
            return $this->targetLanguage?->name;
        }

        return parent::getAttribute($key);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(LanguageInsight::class);
    }
}
