<?php
require_once 'vendor/autoload.php'; // Убедись, что composer установил Google Client

function addBookingToGoogleCalendar($bookingData) {
    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/admin/google-service-account.json'); // Путь к JSON-файлу
    $client->addScope(Google_Service_Calendar::CALENDAR);

    $service = new Google_Service_Calendar($client);

    $calendarId = '9c1b55b269bef2d5bcfc8cfa980692edb4202fa136b8d0bd0125ee7e9f2a3be7@group.calendar.google.com'; // Замени на свой настоящий Calendar ID

    foreach ($bookingData['dates'] as $d) {
        $date = $d['date'];
        $slot = $d['slot'] === 'custom' ? ($d['time'] ?? 'custom') : $d['slot'];

        // Если кастомное время, задаём время начала и окончания
        if ($d['slot'] === 'custom' && !empty($d['time'])) {
            list($startTime, $endTime) = explode('-', $d['time']);
            $startDateTime = $date . 'T' . $startTime . ':00+02:00';
            $endDateTime = $date . 'T' . $endTime . ':00+02:00';

            $event = new Google_Service_Calendar_Event([
                'summary' => 'Rezervācija - ' . $bookingData['name'],
                'description' => "Sektors: {$bookingData['sector']}\nTelefons: {$bookingData['phone']}\nE-pasts: {$bookingData['email']}\nTips: {$slot}",
                'start' => ['dateTime' => $startDateTime, 'timeZone' => 'Europe/Riga'],
                'end' => ['dateTime' => $endDateTime, 'timeZone' => 'Europe/Riga'],
            ]);
        } else {
            // Обычный день без времени
            $event = new Google_Service_Calendar_Event([
                'summary' => 'Rezervācija - ' . $bookingData['name'],
                'description' => "Sektors: {$bookingData['sector']}\nTelefons: {$bookingData['phone']}\nE-pasts: {$bookingData['email']}\nTips: {$slot}",
                'start' => ['date' => $date, 'timeZone' => 'Europe/Riga'],
                'end' => ['date' => $date, 'timeZone' => 'Europe/Riga'],
            ]);
        }

        $service->events->insert($calendarId, $event);
    }
}
