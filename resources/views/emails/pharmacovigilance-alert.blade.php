<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacovigilance Alert</title>
</head>

<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
    <h2>Pharmacovigilance Alert</h2>

    <p>Dear {{ $payload['customer']->name }},</p>

    <p>
        A medication safety alert has been identified for one of the medications in your order.
    </p>

    <p><strong>Details:</strong></p>
    <ul>
        <li><strong>Order:</strong> #{{ $payload['order']->id }}</li>
        <li><strong>Medication:</strong> {{ $payload['medication']?->name ?? 'Medication linked to this lot' }}</li>
        <li><strong>Affected lot number:</strong> {{ $payload['lot_number'] }}</li>
    </ul>

    <p><strong>Recommended action:</strong> {{ $payload['recommended_action'] }}</p>

    <p>
        Please contact the pharmacy for clinical guidance and follow-up.
    </p>

    <p>Sincerely,<br>Pharmacovigilance Team</p>
</body>

</html>
