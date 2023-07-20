<?php

/*
 * This file is part of Respect/Validation.
 *
 * (c) Alexandre Gomes Gaigalas <alexandre@gaigalas.net>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */
namespace AstraPrefixed\Respect\Validation\Exceptions;

/**
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class InfiniteException extends ValidationException
{
    /**
     * @var array
     */
    public static $defaultTemplates = [self::MODE_DEFAULT => [self::STANDARD => '{{name}} must be an infinite number'], self::MODE_NEGATIVE => [self::STANDARD => '{{name}} must not be an infinite number']];
}
