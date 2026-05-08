<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['super_admin', 'admin']);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function customerConversations()
    {
        return $this->hasMany(Conversation::class, 'user_id');
    }

    public function assignedConversations()
    {
        return $this->hasMany(Conversation::class, 'admin_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public static function adminRecipients()
    {
        return static::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['super_admin', 'admin']);
            })
            ->get();
    }
}