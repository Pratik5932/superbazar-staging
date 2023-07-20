<?php

/*
 * This file is part of Respect/Validation.
 *
 * (c) Alexandre Gomes Gaigalas <alexandre@gaigalas.net>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */
namespace AstraPrefixed\Respect\Validation\Rules\SubdivisionCode;

use AstraPrefixed\Respect\Validation\Rules\AbstractSearcher;
/**
 * Validator for Tuvalu subdivision code.
 *
 * ISO 3166-1 alpha-2: TV
 *
 * @link https://salsa.debian.org/iso-codes-team/iso-codes
 */
class TvSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'FUN',
        // Funafuti
        'NIT',
        // Niutao
        'NKF',
        // Nukufetau
        'NKL',
        // Nukulaelae
        'NMA',
        // Nanumea
        'NMG',
        // Nanumanga
        'NUI',
        // Nui
        'VAI',
    ];
    public $compareIdentical = \true;
}
