<?php

namespace AstraPrefixed\GetAstra\Plugins\Scanner\Validation\Rules;

use AstraPrefixed\Respect\Validation\Rules\AbstractRule;
class MatchesPassword extends AbstractRule
{
    /**
     * @var string
     */
    protected $password;
    /**
     * MatchesPassword constructor.
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }
    public function validate($input)
    {
        return \password_verify($input, $this->password);
    }
}
