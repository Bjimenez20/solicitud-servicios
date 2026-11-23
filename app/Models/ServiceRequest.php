<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by_user_id',
        'client_id',
        'provider_id',
        'installer_id',
        'type',
        'executive',
        'segment',
        'negotiation_date',
        'scheduled_date',
        'scheduled_time',
        'message_subject',
        'notes',
        'status',
        'provider_notes',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function installer()
    {
        return $this->belongsTo(Installer::class, 'installer_id');
    }

    public function items()
    {
        return $this->hasMany(RequestItem::class, 'service_request_id');
    }
}
