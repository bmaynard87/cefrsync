<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['nativeLanguage', 'targetLanguage'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'google_id',
        'email_verified_at',
        'native_language_id',
        'target_language_id',
        'proficiency_level',
        'auto_update_proficiency',
        'localize_insights',
        'localize_corrections',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'native_language_key',
        'target_language_key',
    ];

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'auto_update_proficiency' => 'boolean',
            'localize_insights' => 'boolean',
            'localize_corrections' => 'boolean',
        ];
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Get the user's native language
     */
    public function nativeLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'native_language_id');
    }

    /**
     * Get the user's target language
     */
    public function targetLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'target_language_id');
    }

    /**
     * Get the native language key accessor
     */
    public function getNativeLanguageKeyAttribute(): ?string
    {
        return $this->nativeLanguage?->key;
    }

    /**
     * Get the target language key accessor
     */
    public function getTargetLanguageKeyAttribute(): ?string
    {
        return $this->targetLanguage?->key;
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

    /**
     * Mutator to set native language by key
     */
    public function setNativeLanguageKeyAttribute(string $key): void
    {
        $language = Language::findByKey($key);
        if ($language) {
            $this->native_language_id = $language->id;
        }
    }

    /**
     * Mutator to set target language by key
     */
    public function setTargetLanguageKeyAttribute(string $key): void
    {
        $language = Language::findByKey($key);
        if ($language) {
            $this->target_language_id = $language->id;
        }
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function languageInsights(): HasMany
    {
        return $this->hasMany(LanguageInsight::class);
    }
}
