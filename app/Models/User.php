<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\Hash;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar', 'email_verified_at', 'is_admin', 'must_change_password', 'security_question', 'security_answer', 'password_changed_at'])]
#[Hidden(['password', 'remember_token', 'security_answer'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Check if user has admin privileges.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Check if the user has a configured security question.
     */
    public function hasSecurityQuestion(): bool
    {
        return !empty($this->security_question) && !empty($this->security_answer);
    }

    /**
     * Verify the supplied security answer (case-insensitive and trimmed).
     */
    public function verifySecurityAnswer(string $answer): bool
    {
        if (empty($this->security_answer)) {
            return false;
        }

        $clean = strtolower(trim($answer));
        return Hash::check($clean, $this->security_answer);
    }

    /**
     * Set a new hashed security answer.
     */
    public function setSecurityAnswer(string $answer): void
    {
        $this->security_answer = Hash::make(strtolower(trim($answer)));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_admin'             => 'boolean',
            'must_change_password' => 'boolean',
            'password_changed_at'  => 'datetime',
        ];
    }
}
