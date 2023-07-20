<?php

namespace AstraPrefixed;

// Don't redefine the functions if included multiple times.
if (!\function_exists('AstraPrefixed\\GuzzleHttp\\Promise\\promise_for')) {
    require __DIR__ . '/functions.php';
}
