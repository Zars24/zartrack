<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'customer_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, config('zartrack.admin_roles'), true);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function getRoleLabelAttribute(): string
    {
        return config('zartrack.roles.'.$this->role, ucfirst($this->role));
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            'driver' => route('driver.dashboard'),
            'customer' => route('customer.dashboard'),
            'super_admin', 'ops_admin', 'doc_admin', 'finance' => route('admin.dashboard'),
            default => '/',
        };
    }

    public function canManageOperations(): bool
    {
        return $this->hasRole('super_admin', 'ops_admin');
    }
}
