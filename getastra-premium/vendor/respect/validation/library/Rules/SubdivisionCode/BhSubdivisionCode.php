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
 * Validator for Bahrain subdivision code.
 *
 * ISO 3166-1 alpha-2: BH
 *
 * @link https://salsa.debian.org/iso-codes-team/iso-codes
 */
class BhSubdivisionCode extends AbstractSearcher
{
    public $haystack = [
        '13',
        // Al Manāmah (Al ‘Āşimah)
        '14',
        // Al Janūbīyah
        '15',
        // Al Muḩarraq
        '16',
        // Al Wusţá
        '17',
    ];
    public $compareIdentical = \true;
}
