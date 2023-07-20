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
 * Validator for Namibia subdivision code.
 *
 * ISO 3166-1 alpha-2: NA
 *
 * @link https://salsa.debian.org/iso-codes-team/iso-codes
 */
class NaSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'CA',
        // Caprivi
        'ER',
        // Erongo
        'HA',
        // Hardap
        'KA',
        // Karas
        'KH',
        // Khomas
        'KU',
        // Kunene
        'OD',
        // Otjozondjupa
        'OH',
        // Omaheke
        'OK',
        // Okavango
        'ON',
        // Oshana
        'OS',
        // Omusati
        'OT',
        // Oshikoto
        'OW',
    ];
    public $compareIdentical = \true;
}