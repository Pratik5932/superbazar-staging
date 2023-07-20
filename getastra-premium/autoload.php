<?php

namespace AstraPrefixed;

/**
 * Astra Website Protection - Autoload file
 */
$astraRealAutoloadPath = __DIR__ . \DIRECTORY_SEPARATOR . 'autoload_real.php';
if (\file_exists($astraRealAutoloadPath)) {
    include_once $astraRealAutoloadPath;
}
