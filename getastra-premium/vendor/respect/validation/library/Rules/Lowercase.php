<?php

/*
 * This file is part of Respect/Validation.
 *
 * (c) Alexandre Gomes Gaigalas <alexandre@gaigalas.net>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */
namespace AstraPrefixed\Respect\Validation\Rules;

class Lowercase extends AbstractRule
{
    public function validate($input)
    {
        return $input === \mb_strtolower($input, \mb_detect_encoding($input));
    }
}