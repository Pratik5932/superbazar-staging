<?php

namespace AstraPrefixed;

// Don't redefine the functions if included multiple times.
if (!\function_exists('AstraPrefixed\\GuzzleHttp\\Psr7\\str')) {
    require __DIR__ . '/functions.php';
}
