<?php

namespace AstraPrefixed\Expose\Export;

class Loopback extends \AstraPrefixed\Expose\Export
{
    public function render()
    {
        return $this->getData();
    }
}
