<?php

namespace AstraPrefixed;

// Don't redefine the functions if included multiple times.
if (!\function_exists('AstraPrefixed\\GuzzleHttp\\uri_template')) {
    require __DIR__ . '/functions.php';
}
