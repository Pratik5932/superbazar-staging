<?php

namespace AstraPrefixed\GetAstra\Plugins\Scanner\Validation\Exceptions;

use AstraPrefixed\Respect\Validation\Exceptions\ValidationException;
class ExistsInTableException extends ValidationException
{
    public static $defaultTemplates = [self::MODE_DEFAULT => [self::STANDARD => 'has already been taken'], self::MODE_NEGATIVE => [self::STANDARD => 'This does not exist']];
}