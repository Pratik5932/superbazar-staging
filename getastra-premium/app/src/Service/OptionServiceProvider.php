<?php

namespace AstraPrefixed\GetAstra\Client\Service;

use AstraPrefixed\Pimple\Container;
use AstraPrefixed\Pimple\ServiceProviderInterface;
class OptionServiceProvider implements ServiceProviderInterface
{
    private const OPTIONS_SEED = ['optionsSeeded' => \true, 'serverKeys' => ['primary' => '', 'backup' => '']];
    public function register(Container $pimple)
    {
        $optionProvider = new OptionService();
        $gatekeeperOptions = $optionProvider->getPersistentOptions('gatekeeper' . ASTRA_APP_ID);
        if (!$gatekeeperOptions->get('optionsSeeded', \false)) {
            foreach (self::OPTIONS_SEED as $key => $value) {
                $gatekeeperOptions->set($key, $value);
            }
        }
        $pimple['optionProvider'] = function ($c) use($optionProvider) {
            return $optionProvider;
        };
        $pimple['options'] = function ($c) use($gatekeeperOptions) {
            return $gatekeeperOptions;
        };
    }
}
