<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'provider_id',
        'product_id',
        'distributor_name',
        'distributor_nit',
        'associated_client',
        'client_nit',
        'city',
        'executive',
        'type',
        'segment',
        'negotiation_date',
        'responsible_name',
        'responsible_phone',
        'responsible_email',
        'message_subject',
        'scheduled_date',
        'scheduled_time',
        'address',
        'notes',
        'status',
        'provider_notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'negotiation_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(RequestItem::class);
    }
}
