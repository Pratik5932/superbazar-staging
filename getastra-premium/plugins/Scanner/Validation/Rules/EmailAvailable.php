<?php

namespace AstraPrefixed\GetAstra\Plugins\Scanner\Validation\Rules;

use AstraPrefixed\GetAstra\Plugins\Scanner\Models\User;
use AstraPrefixed\Respect\Validation\Rules\AbstractRule;
class EmailAvailable extends AbstractRule
{
    public function validate($input)
    {
        return !User::where('email', $input)->exists();
    }
}
