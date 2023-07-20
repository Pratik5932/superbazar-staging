<?php

namespace AstraPrefixed;

// Define root path
use AstraPrefixed\Dotenv\Dotenv;
use AstraPrefixed\Dotenv\Repository\RepositoryInterface;
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
\defined('ASTRAROOT') ?: \define('ASTRAROOT', \dirname(__DIR__) . \DIRECTORY_SEPARATOR);
if (!\function_exists('AstraPrefixed\\astraFullCopy')) {
    function astraFullCopy($source, $target)
    {
        if (\is_dir($source)) {
            if (!\file_exists($target)) {
                \mkdir($target);
            }
            $d = \dir($source);
            while (\FALSE !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if (\is_dir($Entry)) {
                    astraFullCopy($Entry, $target . '/' . $entry);
                    continue;
                }
                \copy($Entry, $target . '/' . $entry);
            }
            $d->close();
        } else {
            \copy($source, $target);
        }
    }
}
// Define doc root
$astraPath = __DIR__ . \DIRECTORY_SEPARATOR;
$astraBasePath = '';
$astraPossibleBreakWords = ['/wp-content/', '/catalog/controller/extension/', '/sites/all/modules/', '/modules/', '/app/code/', '/astra', '/getastra-premium'];
foreach ($astraPossibleBreakWords as $astraPossibleBreakWord) {
    if (\false !== \strpos($astraPath, $astraPossibleBreakWord)) {
        $astraBasePath = \strstr($astraPath, $astraPossibleBreakWord, \true) . \DIRECTORY_SEPARATOR;
        break;
    }
}
if (empty($astraBasePath)) {
    $astraBasePath = $_SERVER['DOCUMENT_ROOT'] ?? \getcwd();
}
\defined('ASTRA_DOC_ROOT') ?: \define('ASTRA_DOC_ROOT', $astraBasePath);
// Load .env file
if (\file_exists(\ASTRAROOT . '.env')) {
    if (\file_exists(\ASTRAROOT . '.env.local')) {
        $astraDotenv = \AstraPrefixed\Dotenv\Dotenv::createImmutable(\ASTRAROOT, ['.env', '.env.local'], null, \false);
    } else {
        $astraDotenv = Dotenv::createImmutable(\ASTRAROOT);
    }
    $astraDotenv->load();
} else {
    include_once \ASTRAROOT . 'env.defaults.php';
}
\defined('ASTRA_APP_ID') ?: \define('ASTRA_APP_ID', \md5(__FILE__ . CommonHelper::customGetEnv('ASTRA_APP_ENV')));
$astraDirRequired = ['db', 'cache', 'logs', 'options'];
if (!\defined('ASTRA_STORAGE_ROOT')) {
    $astraStorageRoot = \ASTRAROOT . 'astraStorage' . \DIRECTORY_SEPARATOR;
    $astraTempVar = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'astraVar_' . \ASTRA_APP_ID . \DIRECTORY_SEPARATOR;
    $flagFilePath = $astraTempVar . 'astraOptionsTransferred.flag.';
    // Move the storage path to the plugin itself v2.1.46 onwards
    if (\is_writable(\ASTRAROOT) && \file_exists($astraTempVar) && !\file_exists($flagFilePath . 'started')) {
        @\file_put_contents($flagFilePath . 'started', 'true');
        astraFullCopy($astraTempVar, $astraStorageRoot);
        @\file_put_contents($flagFilePath . 'completed', 'true');
    }
    if (\file_exists($flagFilePath . 'started') && !\file_exists($flagFilePath . 'completed')) {
        // Transfer is happening
        $astraStorageRoot = $astraTempVar;
    }
    //if (file_exists($flagFilePath.'completed')) {
    //rename($astraTempVar, rtrim($astraTempVar, DIRECTORY_SEPARATOR).'-deleted');
    //}
    if (!\is_writable(\ASTRAROOT)) {
        $astraStorageRoot = $astraTempVar;
    }
    if (!empty(CommonHelper::customGetEnv('ASTRA_STORAGE_ROOT'))) {
        $dirName = CommonHelper::customGetEnv('ASTRA_STORAGE_ROOT');
        if (!\is_dir($dirName)) {
            @\mkdir($dirName, 0755, \true);
        }
        if (\is_dir($dirName)) {
            $astraStorageRoot = \rtrim($dirName, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        }
    }
    \define('ASTRA_STORAGE_ROOT', $astraStorageRoot);
}
foreach ($astraDirRequired as $astraFolderName) {
    $astraFolderPath = \ASTRA_STORAGE_ROOT . $astraFolderName;
    if (!\is_dir($astraFolderPath)) {
        \mkdir($astraFolderPath, 0755, \true);
    }
}
return ['settings' => [
    'displayErrorDetails' => 'true' === \getenv('ASTRA_APP_DEBUG') ? \true : \false,
    // set to false in production
    'addContentLengthHeader' => \false,
    // Allow the web server to send the content-length header
    'determineRouteBeforeAppMiddleware' => \true,
    // App Settings
    'app' => ['name' => CommonHelper::customGetEnv('ASTRA_APP_NAME') ?? 'Application secured by Astra Security Suite', 'url' => CommonHelper::customGetEnv('ASTRA_APP_URL'), 'env' => CommonHelper::customGetEnv('ASTRA_APP_ENV') ?? 'production', 'customBlockPagePath' => CommonHelper::customGetEnv('ASTRA_CUSTOM_BLOCK_PAGE_PATH'), 'isRequestSigningEnabled' => CommonHelper::customGetEnv('ASTRA_REQUEST_SIGNING_KEY') ?? \true, 'publicKeyPath' => ['key12' => \ASTRAROOT . 'app/config/request_signing/key.pub.pem', 'key13' => \ASTRAROOT . 'app/config/request_signing/key-secondary.pub.pem']],
    'astraWhitelistedIps' => CommonHelper::customGetEnv('ASTRA_IP_WHITELIST'),
    // Monolog settings
    'logger' => [
        'name' => CommonHelper::customGetEnv('ASTRA_APP_NAME'),
        //'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__.'/../var/logs/app.log', //$tempDirPath
        'path' => !empty(CommonHelper::customGetEnv('docker')) ? 'php://stdout' : \ASTRA_STORAGE_ROOT . 'logs/app.log',
        'level' => 100,
    ],
    // Database settings
    'database' => [
        'driver' => CommonHelper::customGetEnv('ASTRA_DB_CONNECTION') ?? 'sqlite',
        //'database' => $_ENV['ASTRA_DB_DATABASE'] ?? ASTRAROOT.'var/db/secure.sqlite',
        'database' => CommonHelper::customGetEnv('ASTRA_DB_DATABASE') ?? \ASTRA_STORAGE_ROOT . 'db/secure-' . \ASTRA_APP_ID . '.sqlite',
        'host' => CommonHelper::customGetEnv('ASTRA_DB_HOST') ?? '127.0.0.1',
        'port' => CommonHelper::customGetEnv('ASTRA_DB_PORT') ?? '3306',
        'username' => CommonHelper::customGetEnv('ASTRA_DB_USERNAME') ?? 'root',
        'password' => CommonHelper::customGetEnv('ASTRA_DB_PASSWORD') ?? 'root',
        'prefix' => CommonHelper::customGetEnv('ASTRA_DB_PREFIX') ?? 'gk_',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],
    'cors' => CommonHelper::customGetEnv('ASTRA_CORS_ALLOWED_ORIGINS') ?? '*',
    // plugins
    'plugins' => ['path' => CommonHelper::customGetEnv('PLUGINS_PATH') ?? __DIR__ . '/../plugins/'],
    'oauth' => [
        //'clientId' => $_ENV['OAUTH_CLIENT_ID'],
        //'clientSecret' => $_ENV['OAUTH_CLIENT_SECRET'],
        'tokenEndpoint' => CommonHelper::customGetEnv('ASTRA_OAUTH_TOKEN_ENDPOINT'),
        //'redirectUri' => $_ENV['OAUTH_REDIRECT_URI'],
        'authorizeEndpoint' => CommonHelper::customGetEnv('ASTRA_OAUTH_AUTHORIZATION_ENDPOINT'),
    ],
    'sentry' => ['dsn' => CommonHelper::customGetEnv('ASTRA_SENTRY_DSN')],
    // relay/api
    'relay' => ['api_url_https' => CommonHelper::customGetEnv('ASTRA_API_URL_HTTPS') ?? 'https://api.getastra.com/api/', 'api_url_http' => CommonHelper::customGetEnv('ASTRA_API_URL_HTTP') ?? 'http://api.getastra.com/api/'],
    'phinx' => ['paths' => ['migrations' => ['app/db/migrations', 'plugins/*/Migrations', 'plugins/*/db/migrations'], 'seeds' => ['app/db/seeds', 'plugins/*/Seeds', 'plugins/*/db/seeds']], 'environments' => ['default_migration_table' => 'astra_migrations', 'default_database' => 'production', 'production' => ['adapter' => CommonHelper::customGetEnv('ASTRA_DB_CONNECTION') ?? 'sqlite', 'name' => CommonHelper::customGetEnv('ASTRA_DB_DATABASE') ?? \ASTRA_STORAGE_ROOT . 'db/secure.sqlite', 'host' => CommonHelper::customGetEnv('ASTRA_DB_HOST') ?? '127.0.0.1', 'port' => CommonHelper::customGetEnv('ASTRA_DB_PORT') ?? '3306', 'user' => CommonHelper::customGetEnv('ASTRA_DB_USERNAME') ?? 'root', 'pass' => CommonHelper::customGetEnv('ASTRA_DB_PASSWORD') ?? 'root', 'prefix' => CommonHelper::customGetEnv('ASTRA_DB_PREFIX') ?? 'gk_', 'charset' => 'utf8', 'collation' => 'utf8_unicode_ci', 'suffix' => '']]],
]];
