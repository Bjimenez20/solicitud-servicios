<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    use HasFactory;

    // Permite la asignación masiva en estos campos
    protected $fillable = [
        'service_request_id',
        'product_id',
        'quantity',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}