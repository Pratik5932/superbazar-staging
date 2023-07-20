<?php

namespace AstraPrefixed\GetAstra\Plugins\Scanner\Validation\Exceptions;

use AstraPrefixed\Respect\Validation\Exceptions\ValidationException;
class MatchesPasswordException extends ValidationException
{
    public static $defaultTemplates = [self::MODE_DEFAULT => [self::STANDARD => 'is invalid']];
}
