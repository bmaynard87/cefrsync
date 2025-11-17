<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'native_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get users who have this as their native language
     */
    public function nativeLanguageUsers(): HasMany
    {
        return $this->hasMany(User::class, 'native_language_id');
    }

    /**
     * Get users who have this as their target language
     */
    public function targetLanguageUsers(): HasMany
    {
        return $this->hasMany(User::class, 'target_language_id');
    }

    /**
     * Get chat sessions with this as native language
     */
    public function nativeLanguageSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class, 'native_language_id');
    }

    /**
     * Get chat sessions with this as target language
     */
    public function targetLanguageSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class, 'target_language_id');
    }

    /**
     * Scope to only get active languages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get language by key
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }
}
