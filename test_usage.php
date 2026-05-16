<?php
$url = 'http://localhost/smart-water-billing/api/submit_usage.php';
$data = json_encode(['meter_id' => 'YOUR_METER_ID_HERE', 'water_used' => 5.0]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "Response from API:\n";
print_r(json_decode($response, true));