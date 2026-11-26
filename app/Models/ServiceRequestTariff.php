<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestTariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'total_units',
        'vehicle_type',
        'transport_price',
        'unit_installation_price',
        'installation_price',
        'total_price',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }
}
