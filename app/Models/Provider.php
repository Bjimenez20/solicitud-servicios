<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'nit',
        'city',
        'address',
        'phone',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_provider')->withTimestamps();
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'provider_id');
    }
}
