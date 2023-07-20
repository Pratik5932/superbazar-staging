<?php

namespace AstraPrefixed\GetAstra\Plugins\Scanner\Transformers;

use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Option;
use AstraPrefixed\League\Fractal\TransformerAbstract;
class OptionTransformer extends TransformerAbstract
{
    /**
     * @var int|null
     */
    protected $requestUserId;
    /**
     * OptionTransformer constructor.
     */
    public function __construct()
    {
    }
    public function transform(Option $option)
    {
        //var_dump($option->name);exit;
        return ['name' => $option->name, 'val' => $option->val, 'group' => $option->group, 'autoload' => $option->autoload];
    }
}
