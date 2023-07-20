<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AstraPrefixed\Carbon\Laravel;

use AstraPrefixed\Carbon\Carbon;
use AstraPrefixed\Carbon\CarbonImmutable;
use AstraPrefixed\Carbon\CarbonInterval;
use AstraPrefixed\Carbon\CarbonPeriod;
use AstraPrefixed\Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use AstraPrefixed\Illuminate\Events\Dispatcher;
use AstraPrefixed\Illuminate\Events\EventDispatcher;
use AstraPrefixed\Illuminate\Support\Carbon as IlluminateCarbon;
use AstraPrefixed\Illuminate\Support\Facades\Date;
use Throwable;
class ServiceProvider extends \AstraPrefixed\Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->updateLocale();
        if (!$this->app->bound('events')) {
            return;
        }
        $service = $this;
        $events = $this->app['events'];
        if ($this->isEventDispatcher($events)) {
            $events->listen(\class_exists('AstraPrefixed\\Illuminate\\Foundation\\Events\\LocaleUpdated') ? 'Illuminate\\Foundation\\Events\\LocaleUpdated' : 'locale.changed', function () use($service) {
                $service->updateLocale();
            });
        }
    }
    public function updateLocale()
    {
        $app = $this->app && \method_exists($this->app, 'getLocale') ? $this->app : app('translator');
        $locale = $app->getLocale();
        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);
        CarbonPeriod::setLocale($locale);
        CarbonInterval::setLocale($locale);
        if (\class_exists(IlluminateCarbon::class)) {
            IlluminateCarbon::setLocale($locale);
        }
        if (\class_exists(Date::class)) {
            try {
                $root = Date::getFacadeRoot();
                $root->setLocale($locale);
            } catch (Throwable $e) {
                // Non Carbon class in use in Date facade
            }
        }
    }
    public function register()
    {
        // Needed for Laravel < 5.3 compatibility
    }
    protected function isEventDispatcher($instance)
    {
        return $instance instanceof EventDispatcher || $instance instanceof Dispatcher || $instance instanceof DispatcherContract;
    }
}
