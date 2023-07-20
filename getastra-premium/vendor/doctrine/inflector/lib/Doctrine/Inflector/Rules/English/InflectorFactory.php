<?php

declare (strict_types=1);
namespace AstraPrefixed\Doctrine\Inflector\Rules\English;

use AstraPrefixed\Doctrine\Inflector\GenericLanguageInflectorFactory;
use AstraPrefixed\Doctrine\Inflector\Rules\Ruleset;
final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : Ruleset
    {
        return Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : Ruleset
    {
        return Rules::getPluralRuleset();
    }
}
