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
 * Validator for Israel subdivision code.
 *
 * ISO 3166-1 alpha-2: IL
 *
 * @link https://salsa.debian.org/iso-codes-team/iso-codes
 */
class IlSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        'D',
        // HaDarom
        'HA',
        // Hefa
        'JM',
        // Yerushalayim Al Quds
        'M',
        // HaMerkaz
        'TA',
        // Tel-Aviv
        'Z',
    ];
    public $compareIdentical = \true;
}
