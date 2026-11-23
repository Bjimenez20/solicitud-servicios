<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
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

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'client_provider')->withTimestamps();
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'client_id');
    }
}
