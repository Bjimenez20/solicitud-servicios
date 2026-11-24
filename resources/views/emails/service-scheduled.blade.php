@php
$req = $serviceRequest;
$installer = $req->installer;
$client = $req->client;

$installerName = $installer?->name ?? 'Técnico por asignar';
$rawPhoto = $installer?->photo ?? $installer?->avatar ?? $installer?->image ?? null;

if ($rawPhoto) {
$installerPhotoUrl = str_starts_with($rawPhoto, 'http')
? $rawPhoto
: asset('storage/' . ltrim($rawPhoto, '/'));
} else {
$installerPhotoUrl = 'https://ui-avatars.com/api/?' . http_build_query([
'name' => $installerName,
'background' => '1b3a6b',
'color' => 'ffffff',
'size' => 160,
'bold' => 'true',
]);
}

$dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = [
1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
];

$fechaCarbon = $req->scheduled_date ? \Carbon\Carbon::parse($req->scheduled_date) : null;
$formattedDate = $fechaCarbon
? ucfirst($dias[$fechaCarbon->dayOfWeek]) . ' ' . $fechaCarbon->day . ' de ' . $meses[(int) $fechaCarbon->month] . ' de ' . $fechaCarbon->year
: 'Por confirmar';

$formattedTime = $req->scheduled_time
? \Carbon\Carbon::parse($req->scheduled_time)->format('h:i A')
: 'Por confirmar';
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Confirmación de Servicio #{{ $req->id }}</title>
</head>

<body style="margin:0; padding:24px; background-color:#f4f6f9; font-family:Arial, Helvetica, sans-serif; color:#1e293b;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e2e8f0;">
        <tr>
            <td style="background-color:#1b3a6b; color:#ffffff; padding:20px 24px;">
                <h2 style="margin:0; font-size:18px;">Confirmación de Programación — Solicitud #{{ $req->id }}</h2>
                <p style="margin:6px 0 0; font-size:13px; color:#cbd5e1;">
                    Tipo de servicio: <strong style="color:#ffffff;">{{ ucfirst($req->type) }}</strong>
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding:24px;">
                <p style="margin-top:0; font-size:14px;">
                    Hola <strong>{{ $client?->business_name ?? $req->creator?->name ?? 'Cliente' }}</strong>, tu servicio ha sido programado con fecha y hora definitivas y cuenta con un técnico asignado:
                </p>

                <!-- Fecha y Hora Final -->
                <table width="100%" cellpadding="12" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:20px;">
                    <tr>
                        <td width="50%" style="font-size:13px;">
                            <div style="color:#64748b; font-size:11px; text-transform:uppercase; font-weight:bold;">Fecha Final</div>
                            <div style="font-size:15px; font-weight:bold; color:#0f172a; margin-top:4px;">{{ $formattedDate }}</div>
                        </td>
                        <td width="50%" style="font-size:13px;">
                            <div style="color:#64748b; font-size:11px; text-transform:uppercase; font-weight:bold;">Hora Final</div>
                            <div style="font-size:15px; font-weight:bold; color:#0f172a; margin-top:4px;">{{ $formattedTime }}</div>
                        </td>
                    </tr>
                </table>

                <!-- Datos y Foto / Avatar del Técnico -->
                <h3 style="font-size:14px; text-transform:uppercase; color:#1b3a6b; margin:0 0 10px;">Técnico / Instalador Asignado</h3>
                <table width="100%" cellpadding="14" cellspacing="0" style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; margin-bottom:20px;">
                    <tr>
                        <td width="96" valign="top" style="padding-right:14px;">
                            <img src="{{ $installerPhotoUrl }}" alt="Avatar de {{ $installerName }}"
                                width="84" height="84"
                                style="width:84px; height:84px; object-fit:cover; border-radius:50%; border:2px solid #1b3a6b; display:block;">
                        </td>
                        <td valign="top" style="font-size:13px; line-height:1.5;">
                            <div style="font-size:15px; font-weight:bold; color:#0f172a;">{{ $installerName }}</div>
                            @if(!empty($installer?->document) || !empty($installer?->identification))
                            <div><strong>Identificación:</strong> {{ $installer->document ?? $installer->identification }}</div>
                            @endif
                            @if(!empty($installer?->phone))
                            <div><strong>Teléfono:</strong> {{ $installer->phone }}</div>
                            @endif
                            @if(!empty($installer?->email))
                            <div><strong>Correo:</strong> {{ $installer->email }}</div>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Ubicación y Equipos -->
                <p style="font-size:13px; margin:0 0 8px;">
                    <strong>Dirección:</strong> {{ $client?->address ?? 'N/A' }} ({{ $client?->city ?? 'N/A' }})
                </p>

                @if($req->items->isNotEmpty())
                <p style="font-size:13px; margin:12px 0 6px;"><strong>Equipos / Ítems asociados:</strong></p>
                <ul style="margin:0; padding-left:18px; font-size:13px;">
                    @foreach($req->items as $item)
                    <li>{{ $item->quantity }}x {{ $item->product?->name ?? 'Producto' }}</li>
                    @endforeach
                </ul>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>