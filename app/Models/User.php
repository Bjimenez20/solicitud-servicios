<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    public function providerProfile()
    {
        return $this->hasOne(Provider::class);
    }

    public function clientProfile()
    {
        return $this->hasOne(Client::class);
    }

    public function isClient(): bool
    {
        return $this->role === 'cliente';
    }

    public function isProvider(): bool
    {
        return $this->role === 'proveedor';
    }

    public function isBodega()
    {
        return $this->role === 'bodega';
    }

    public function isWarehouse(): bool
    {
        return $this->role === 'bodega';
    }

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'client_id');
    }
}
