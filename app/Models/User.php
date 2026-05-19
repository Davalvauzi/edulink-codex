<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\AiMessage;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const GENERAL_KELAS = 'umum';

    public const KELAS_OPTIONS = [
        self::GENERAL_KELAS => 'Kelas Umum',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'kelas',
        'password',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ai_tutor_paid_at' => 'datetime',
            'ai_tutor_payment_requested_at' => 'datetime',
            'ai_tutor_payment_sender_name' => 'string',
        ];
    }

    public function hasUnlimitedAiAccess(): bool
    {
        return $this->ai_tutor_paid_at !== null;
    }

    public function hasRequestedAiPayment(): bool
    {
        return $this->ai_tutor_payment_requested_at !== null && $this->ai_tutor_paid_at === null;
    }

    public function remainingAiChats(): int
    {
        $limit = config('ai.unpaid_chat_limit', 5);

        if ($this->hasUnlimitedAiAccess()) {
            return PHP_INT_MAX;
        }

        $used = AiMessage::query()
            ->where('role', 'assistant')
            ->whereHas('conversation', fn ($query) => $query->where('user_id', $this->id))
            ->count();

        return max(0, $limit - $used);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'created_by');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'created_by');
    }

    public function materialSubsections(): HasMany
    {
        return $this->hasMany(MaterialSubsection::class, 'created_by');
    }

    public function subsectionProgress(): HasMany
    {
        return $this->hasMany(MaterialSubsectionProgress::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'created_by');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }

    public static function kelasOptions(): array
    {
        return self::KELAS_OPTIONS;
    }

    public static function isValidKelas(?string $kelas): bool
    {
        return array_key_exists($kelas, self::KELAS_OPTIONS);
    }

    public static function kelasLabel(?string $kelas): string
    {
        return self::KELAS_OPTIONS[$kelas] ?? (string) $kelas;
    }
}
