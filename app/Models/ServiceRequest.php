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

    public function tariff()
    {
        return $this->hasOne(ServiceRequestTariff::class, 'service_request_id');
    }

    public function calculateTariffData(): array
    {
        $totalUnits = (int) $this->items->sum('quantity');

        if ($totalUnits <= 4) {
            $vehicleType = 'Moto';
            $transportRate = 25000;
        } elseif ($totalUnits <= 9) {
            $vehicleType = 'Carro';
            $transportRate = 65000;
        } else {
            $vehicleType = 'Camión';
            $transportRate = 140000;
        }

        $unitInstallationPrice = 35000;
        $installationTotal = $totalUnits * $unitInstallationPrice;

        return [
            'total_units'             => $totalUnits,
            'vehicle_type'            => $vehicleType,
            'transport_price'          => $transportRate,
            'unit_installation_price' => $unitInstallationPrice,
            'installation_price'      => $installationTotal,
            'total_price'             => $transportRate + $installationTotal,
        ];
    }

    public function syncTariff(): ServiceRequestTariff
    {
        $this->loadMissing('items');
        $data = $this->calculateTariffData();

        return $this->tariff()->updateOrCreate(
            ['service_request_id' => $this->id],
            $data
        );
    }

    public function getTariffSummaryAttribute(): array
    {
        $totalUnits = (int) $this->items->sum('quantity');

        if ($totalUnits <= 4) {
            $vehicleIcon = 'bi-bicycle';
        } elseif ($totalUnits <= 9) {
            $vehicleIcon = 'bi-car-front-fill';
        } else {
            $vehicleIcon = 'bi-truck';
        }

        $savedTariff = $this->relationLoaded('tariff') ? $this->tariff : $this->tariff()->first();
        $data = $savedTariff
            ? [
                'total_units'             => (int) $savedTariff->total_units,
                'vehicle_type'            => $savedTariff->vehicle_type,
                'transport_price'          => (float) $savedTariff->transport_price,
                'unit_installation_price' => (float) $savedTariff->unit_installation_price,
                'installation_price'      => (float) $savedTariff->installation_price,
                'total_price'             => (float) $savedTariff->total_price,
            ]
            : $this->calculateTariffData();

        return array_merge($data, [
            'vehicle_icon'       => $vehicleIcon,
            'transport_rate'     => $data['transport_price'],
            'installation_total' => $data['installation_price'],
            'grand_total'        => $data['total_price'],
        ]);
    }
}
