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
 * Validator for Eritrea subdivision code.
 *
 * ISO 3166-1 alpha-2: ER
 *
 * @link https://salsa.debian.org/iso-codes-team/iso-codes
 */
class ErSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'AN',
        // Ansabā
        'DK',
        // Janūbī al Baḩrī al Aḩmar
        'DU',
        // Al Janūbī
        'GB',
        // Qāsh-Barkah
        'MA',
        // Al Awsaţ
        'SK',
    ];
    public $compareIdentical = \true;
}
