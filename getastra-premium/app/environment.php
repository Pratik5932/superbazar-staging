<?php

namespace AstraPrefixed;

/**
 *
 *
 * @author Ananda Krishna
 * @date   5/27/22
 */
class AstraEnvironment
{
    private $originalErrorReporting = \E_ALL & ~\E_DEPRECATED & ~\E_NOTICE & ~\E_WARNING;
    private $originalDisplayErrors = 0;
    private $originalDisplayStartupErrors = 0;
    private $originalDefaultMimeType;
    private $originalServerHttps = null;
    private $originalComposerAutoloadFiles = null;
    public function create()
    {
        // Store original error reporting & logging configuration
        $this->originalErrorReporting = \error_reporting();
        $this->originalDisplayErrors = \ini_get('display_errors');
        $this->originalDisplayStartupErrors = \ini_get('display_startup_errors');
        \error_reporting(\E_ALL & ~\E_DEPRECATED & ~\E_NOTICE & ~\E_WARNING);
        \ini_set('display_errors', 0);
        \ini_set('display_startup_errors', 0);
        // Set default timezone to server time if available and UTC in other cases
        \date_default_timezone_set(\date_default_timezone_get());
        // Need to restore the default MIME type since Slim stops PHP from sending a Content-Type automatically
        $this->originalDefaultMimeType = \ini_get('default_mimetype');
        // If the application is behind a reverse proxy or load balancer, the correct HTTPS value is required for Slim to work
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            if (\strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== \false) {
                $this->originalServerHttps = $_SERVER['HTTPS'];
                $_SERVER['HTTPS'] = 'on';
            }
        }
        // Store the old composer autoload files
        if (isset($GLOBALS['__composer_autoload_files'])) {
            $this->originalComposerAutoloadFiles = $GLOBALS['__composer_autoload_files'];
            $GLOBALS['__composer_autoload_files'] = [];
        }
    }
    public function destroy()
    {
        // Restore original error reporting & logging configuration
        \error_reporting($this->originalErrorReporting);
        \ini_set('display_errors', $this->originalDisplayErrors);
        \ini_set('display_startup_errors', $this->originalDisplayStartupErrors);
        // Restores the default MIME Type
        \ini_set('default_mimetype', $this->originalDefaultMimeType);
        // Restore original HTTPS server var
        if (!\is_null($this->originalServerHttps)) {
            $_SERVER['HTTPS'] = $this->originalServerHttps;
        }
        // Reset the __composer_autoload_files global so that composer loads the files again. Should not be a problem since our namespaces are prefixed.
        // Restoring the original values if the application still needs it
        if (!\is_null($this->originalComposerAutoloadFiles)) {
            $GLOBALS['__composer_autoload_files'] = $this->originalComposerAutoloadFiles;
        }
        // Cleanup to avoid conflict with user app's composer
        if (\is_null($this->originalComposerAutoloadFiles)) {
            unset($GLOBALS['__composer_autoload_files']);
        }
    }
}
/**
 *
 *
 * @author Ananda Krishna
 * @date   5/27/22
 */
\class_alias('AstraPrefixed\\AstraEnvironment', 'AstraEnvironment', \false);
