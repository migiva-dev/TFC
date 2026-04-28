<?php
// =====================================================
// ARCHIVO: includes/google-calendar.php
// Descripción: Funciones para conectar con la API
//              de Google Calendar usando cuenta
//              de servicio (sin login de usuario).
//              NO subir a GitHub (datos sensibles).
// =====================================================

// -- Credenciales de la cuenta de servicio --
define('GOOGLE_CALENDAR_ID',    'miguelceacfp@gmail.com');
define('GOOGLE_CLIENT_EMAIL',   'dioni-calendar@peluqueria-tfc.iam.gserviceaccount.com');
define('GOOGLE_PRIVATE_KEY_ID', '23f6c536623a313b39a732c17f763d16cf13154e');
define('GOOGLE_PRIVATE_KEY',    "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDIvcOF+MmYWvkp\nf2/1RW0pTgCTs7zf8L316vCH7240K7z1SC2KVnCSrM5tTlt+cGlRFYpY3oVDAm18\nBgS8WOuq8mcO6I3FEzAFyQvwpHhVQLDIAvaStwmVxSyOe1hioYbhsc632pEx1nKW\n5IH5d5tJiSWulaIwreLKXFdstHWK4Osnr69rHzn0Zjm/hO+x86HvxT92JMz8UIUk\n8LTmR0myAulKPuodsbjbzRIsXk5Ry7onrsNRtml/lpZWly2wJqnGfH0DUcLqBw10\nPplA7PJL/yOnI33Dvsul86kr2UkOazQqGGIV1atHUJpV8HJKKGqw/FBVeKO13I3a\neXY50f//AgMBAAECggEAOrzwH9z23CpOevqLn4izkuD8oTt4GkzewL0Aqb4BEKn1\nrg3zKWX2payD8I77jalF39B+PVfLRZrJv38hVp1sRAaO6llY1mAxbpfv8velMEjw\nEW5wiOifGOBrk7t61s1t4s987eY6EwutVwLBF2aMnT1Qtd+Z6EWRNWZEvI2nUT3O\nXSBMo3kB76ENEipYLIir6VvkdBxsbCUJrOimJwEsKcylKsygIskoYj/dCxi2THIt\nIm3omPdPqRzJJgP04b9qwdQdoCUX2IGo83gYI4b3Z7yh6M3lmPU1KPaoS+hga1pq\nm5WikXgMglFd1L+5BMECh71+t8EexQf+72huZN/FIQKBgQD/50cDL9NiKHVh4Xry\nohxnnPFavDibBHKJ4bynSlfd0rQh9VlFUa7GEV/tyIqFoSqY1uMcKZrPn5HJWc1/\nqkWVhsl3BJrwWDn3iYBqN8axQ+keZJmAYhLdOhSj6Lbhy+4mkl4HBbUgEIeLuerx\nORSkiOARS3XFxJ9vlW7JF1zG4QKBgQDI0Sg+ZMBwsf7xyh2ifGi05KYX3mplV/H7\nUQ5Tf9EFnTq6ViJ47ls21B1p+3nlM+dcl+pSzvW9Xb8m6dtIXoDysqRFy9ehJdDH\nAV0CkPzUtaolhS7kliG6LouTMDxRP+1zgQsqebiybjHiJZbTO/goDZxj68Gev+h9\nClF6m9MC3wKBgQDZrB7JZSsHOabvRV7ReHd04xiht6zmn1fWVP+lxSUMeKfkos/2\nmM5ziF/y4TyDAl47d73jPLiElxTpEswsJefekrJX3MD2Y/Way4lB0IPgUQXAZo3z\nmHi3WSopak/1rQO8J5B3pLE2vxitD836tjUK56rAy1E4klGUFqYPwyGVwQKBgAdm\nZbQ+5DSC6qEgUI0/RmB8GH7CrGsFnZ4kM4HyMNKjI5ngOK76IZRYpqjUIn7Gtdzc\noD7i731F6hhV/8Wf7eKDoLgNtelzSkk7DKFelGKGOzncuYik26NPucb3vRFFaMub\n0ryC9cJleyMZPYCVMiTPs5afBmiCK+AHtE7vVQxDAoGAQVJEMwyecFhW2SckLZaq\nUB7rXhDNVqJsrQ/yLrPIs6riLZ2ar5fttln51yT94RZVRKBn/Ftf1GY5ghJ7SZOh\nuyszJyIwOBuI1h4cDH/X/18n/3KDpfzy1tTMTguEoRU2AWu1ZhCGtzY2MGIBKjKp\nFdS8HasFObveAGDbIBrdP7M=\n-----END PRIVATE KEY-----\n");

// -------------------------------------------------------
// Genera un token JWT para autenticarse con Google
// JWT = JSON Web Token, es el método que usa Google
// para verificar que somos quien decimos ser
// -------------------------------------------------------
function google_get_access_token() {

    // Cabecera del JWT
    $header = base64_encode(json_encode([
        'alg' => 'RS256',
        'typ' => 'JWT'
    ]));

    // Tiempo actual y expiración (1 hora)
    $now = time();

    // Cuerpo del JWT con los permisos que pedimos
    $payload = base64_encode(json_encode([
        'iss'   => GOOGLE_CLIENT_EMAIL,
        'scope' => 'https://www.googleapis.com/auth/calendar',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $now + 3600,
        'iat'   => $now
    ]));

    // Firma del JWT con la clave privada
    $data = $header . '.' . $payload;
    openssl_sign($data, $firma, GOOGLE_PRIVATE_KEY, 'SHA256');
    $firma_b64 = base64_encode($firma);

    // JWT completo
    $jwt = $data . '.' . $firma_b64;

    // Pedimos el token de acceso a Google
    $respuesta = file_get_contents('https://oauth2.googleapis.com/token', false,
        stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt
                ])
            ]
        ])
    );

    $datos = json_decode($respuesta, true);
    return $datos['access_token'] ?? null;
}

// -------------------------------------------------------
// Crea un evento en Google Calendar
// Devuelve el ID del evento creado o null si falla
// -------------------------------------------------------
function google_crear_evento($cliente, $servicio, $fecha, $hora, $notas = '') {

    $token = google_get_access_token();
    if (!$token) return null;

    // Calculamos la hora de fin sumando la duración del servicio
    $inicio = $fecha . 'T' . $hora . ':00';
    $fin    = $fecha . 'T' . date('H:i', strtotime($hora) + ($servicio['duracion'] * 60)) . ':00';

    // Datos del evento
    $evento = [
        'summary'     => '💈 ' . $servicio['nombre'] . ' — ' . $cliente['nombre'] . ' ' . $cliente['apellidos'],
        'description' => 'Cliente: ' . $cliente['nombre'] . ' ' . $cliente['apellidos'] . "\n" .
                         'Teléfono: ' . $cliente['telefono'] . "\n" .
                         'Servicio: ' . $servicio['nombre'] . "\n" .
                         'Precio: ' . $servicio['precio'] . '€' .
                         (!empty($notas) ? "\nNotas: " . $notas : ''),
        'start'       => ['dateTime' => $inicio, 'timeZone' => 'Europe/Madrid'],
        'end'         => ['dateTime' => $fin,    'timeZone' => 'Europe/Madrid'],
        'colorId'     => '1' // Azul lavanda
    ];

    // Llamada a la API de Google Calendar
    $respuesta = file_get_contents(
        'https://www.googleapis.com/calendar/v3/calendars/' .
        urlencode(GOOGLE_CALENDAR_ID) . '/events',
        false,
        stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Authorization: Bearer {$token}\r\nContent-Type: application/json",
                'content' => json_encode($evento)
            ]
        ])
    );

    $datos = json_decode($respuesta, true);
    return $datos['id'] ?? null;
}

// -------------------------------------------------------
// Elimina un evento de Google Calendar por su ID
// Se llama cuando se cancela una reserva
// -------------------------------------------------------
function google_eliminar_evento($event_id) {

    if (empty($event_id)) return false;

    $token = google_get_access_token();
    if (!$token) return false;

    $contexto = stream_context_create([
        'http' => [
            'method' => 'DELETE',
            'header' => "Authorization: Bearer {$token}"
        ]
    ]);

    @file_get_contents(
        'https://www.googleapis.com/calendar/v3/calendars/' .
        urlencode(GOOGLE_CALENDAR_ID) . '/events/' . $event_id,
        false,
        $contexto
    );

    return true;
}

// -------------------------------------------------------
// Obtiene los eventos de Google Calendar de la semana
// actual para mostrarlos en el dashboard del admin
// -------------------------------------------------------
function google_obtener_eventos_semana() {

    $token = google_get_access_token();
    if (!$token) return [];

    // Inicio y fin de la semana actual
    $inicio_semana = date('Y-m-d', strtotime('monday this week')) . 'T00:00:00Z';
    $fin_semana    = date('Y-m-d', strtotime('saturday this week')) . 'T23:59:59Z';

    $url = 'https://www.googleapis.com/calendar/v3/calendars/' .
           urlencode(GOOGLE_CALENDAR_ID) . '/events?' .
           http_build_query([
               'timeMin'      => $inicio_semana,
               'timeMax'      => $fin_semana,
               'orderBy'      => 'startTime',
               'singleEvents' => 'true'
           ]);

    $respuesta = file_get_contents($url, false,
        stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer {$token}"
            ]
        ])
    );

    $datos = json_decode($respuesta, true);
    return $datos['items'] ?? [];
}
?>