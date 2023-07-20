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
 * Validator for Costa Rica subdivision code.
 *
 * ISO 3166-1 alpha-2: CR
 *
 * @link https://salsa.debian.org/iso-codes-team/iso-codes
 */
class CrSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'A',
        // Alajuela
        'C',
        // Cartago
        'G',
        // Guanacaste
        'H',
        // Heredia
        'L',
        // Limón
        'P',
        // Puntarenas
        'SJ',
    ];
    public $compareIdentical = \true;
}
