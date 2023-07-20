<?php

namespace AstraPrefixed;

if (!\defined('ASTRA_API_ROUTE')) {
    \define('ASTRA_API_ROUTE', isset($_GET['astraRoute']));
}
$gkErrors = [];
if (\version_compare(\PHP_VERSION, '7.1.0') < 0) {
    $gkErrors['errors']['php_version'] = 'PHP version should be greater than 7.1.0';
}
if (!\defined('PDO::ATTR_DRIVER_NAME')) {
    $gkErrors['errors']['pdo_missing'] = 'pdo server extension required';
} else {
    // Neither is available
    if (!\in_array('mysql', \PDO::getAvailableDrivers(), \TRUE) && !\in_array('sqlite', \PDO::getAvailableDrivers(), \TRUE)) {
        $gkErrors['errors']['pdo_missing'] = 'pdo_sqlite OR pdo_mysql server extension required';
    }
    // pdo_sqlite is there but not sqlite
    if (!\in_array('mysql', \PDO::getAvailableDrivers(), \TRUE) && \in_array('sqlite', \PDO::getAvailableDrivers(), \TRUE) && !\class_exists('SQLite3')) {
        $gkErrors['errors']['sqlite_missing'] = 'SQLite extension required';
    }
    // pdo_mysql is there but not configured
    if (!\in_array('sqlite', \PDO::getAvailableDrivers(), \TRUE) && \in_array('mysql', \PDO::getAvailableDrivers(), \TRUE) && empty(\getenv('ASTRA_DB_CONNECTION'))) {
        $gkErrors['errors']['pdo_missing'] = 'pdo_sqlite & SQLite server extensions required (easy)';
        $gkErrors['errors']['mysql_creds'] = 'If pdo_sqlite cannot be installed, then MySQL database connection details are required (advance)';
    }
}
if (!\in_array('curl', \get_loaded_extensions())) {
    $gkErrors['errors']['curl_missing'] = 'curl server extension required';
}
if (!\function_exists('opcache_get_status')) {
    $gkErrors['recommendations']['opcache_missing'] = 'OP cache not found';
} else {
    $astraOpCacheStatus = @\opcache_get_status();
    if (empty($astraOpCacheStatus['opcache_enabled']) || $astraOpCacheStatus['opcache_enabled'] != \true) {
        $gkErrors['recommendations']['opcache_disabled'] = 'OP cache disabled, please enable it for Astra to work';
    }
}
// $dir = __DIR__.'/var';
if (!\is_writable(__DIR__)) {
    $gkErrors['errors']['folder_permission'] = 'Plugin folder should be writable, please update the folder permissions';
}
if (\count($gkErrors) > 0 && \ASTRA_API_ROUTE && $_GET['astraRoute'] === 'api/status') {
    if (!isset($_SERVER['HTTP_X_TOKEN'])) {
        \header('Content-Type: application/json');
        echo \json_encode($gkErrors);
        exit;
    }
}
if (isset($gkErrors['errors']) && \count($gkErrors['errors']) > 0 && \ASTRA_API_ROUTE && $_GET['astraRoute'] === 'api/login') {
    if (!isset($_SERVER['HTTP_X_TOKEN'])) {
        \header('Content-Type: text/html');
        $html = "<html>";
        $html .= "<head>";
        $html .= "<title>Astra Installation</title>";
        $html .= "<style>\n        table {\n          font-family: arial, sans-serif;\n          border-collapse: collapse;\n          margin-top: 15px;\n        }\n        html, body {\n            text-align: center;\n            padding: 30px;\n            font-family:arial, sans-serif;\n        }\n        table {\n            margin: 0 auto;\n        }\n        td, th {\n          border: 1px solid #dddddd;\n          text-align: left;\n          padding: 8px;\n        }\n        \n        h2 {\n            color: #164db3;\n        }\n\n        tr:nth-child(even) {\n          background-color: #dae7fd;\n        }\n        </style>";
        $html .= "</head>";
        $html .= "<h2>Almost there... please check the system requirements</h2>";
        $html .= "<p>Please resolve the issues listed below, to get started.</p><p><a target='_blank' href='https://help.getastra.com/en/article/troubleshooting-astra-website-protection-installation-nlwtcg/'>View Help Article</a></p><br/>";
        $html .= "<table>";
        $html .= "<tr><th>Error Code</th><th>Error Message</th><th>Type</th></tr>";
        foreach ($gkErrors['errors'] as $errorKey => $errorVal) {
            $html .= "<tr><td>{$errorKey}</td><td>{$errorVal}</td><td><strong>Required</strong></td></tr>";
        }
        if (isset($gkErrors['recommendations'])) {
            foreach ($gkErrors['recommendations'] as $errorKey => $errorVal) {
                $html .= "<tr><td>{$errorKey}</td><td>{$errorVal}</td><td>Recommended</td></tr>";
            }
        }
        $html .= "</table>";
        $html .= "</body></html>";
        echo $html;
        //echo json_encode($gkErrors);
        exit;
    }
}
return $gkErrors;
