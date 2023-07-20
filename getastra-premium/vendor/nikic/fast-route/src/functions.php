<?php

namespace AstraPrefixed\FastRoute;

if (!\function_exists('AstraPrefixed\\FastRoute\\simpleDispatcher')) {
    /**
     * @param callable $routeDefinitionCallback
     * @param array $options
     *
     * @return Dispatcher
     */
    function simpleDispatcher(callable $routeDefinitionCallback, array $options = [])
    {
        $options += ['routeParser' => 'AstraPrefixed\\FastRoute\\RouteParser\\Std', 'dataGenerator' => 'AstraPrefixed\\FastRoute\\DataGenerator\\GroupCountBased', 'dispatcher' => 'AstraPrefixed\\FastRoute\\Dispatcher\\GroupCountBased', 'routeCollector' => 'AstraPrefixed\\FastRoute\\RouteCollector'];
        /** @var RouteCollector $routeCollector */
        $routeCollector = new $options['routeCollector'](new $options['routeParser'](), new $options['dataGenerator']());
        $routeDefinitionCallback($routeCollector);
        return new $options['dispatcher']($routeCollector->getData());
    }
    /**
     * @param callable $routeDefinitionCallback
     * @param array $options
     *
     * @return Dispatcher
     */
    function cachedDispatcher(callable $routeDefinitionCallback, array $options = [])
    {
        $options += ['routeParser' => 'AstraPrefixed\\FastRoute\\RouteParser\\Std', 'dataGenerator' => 'AstraPrefixed\\FastRoute\\DataGenerator\\GroupCountBased', 'dispatcher' => 'AstraPrefixed\\FastRoute\\Dispatcher\\GroupCountBased', 'routeCollector' => 'AstraPrefixed\\FastRoute\\RouteCollector', 'cacheDisabled' => \false];
        if (!isset($options['cacheFile'])) {
            throw new \LogicException('Must specify "cacheFile" option');
        }
        if (!$options['cacheDisabled'] && \file_exists($options['cacheFile'])) {
            $dispatchData = (require $options['cacheFile']);
            if (!\is_array($dispatchData)) {
                throw new \RuntimeException('Invalid cache file "' . $options['cacheFile'] . '"');
            }
            return new $options['dispatcher']($dispatchData);
        }
        $routeCollector = new $options['routeCollector'](new $options['routeParser'](), new $options['dataGenerator']());
        $routeDefinitionCallback($routeCollector);
        /** @var RouteCollector $routeCollector */
        $dispatchData = $routeCollector->getData();
        if (!$options['cacheDisabled']) {
            \file_put_contents($options['cacheFile'], '<?php return ' . \var_export($dispatchData, \true) . ';');
        }
        return new $options['dispatcher']($dispatchData);
    }
}
