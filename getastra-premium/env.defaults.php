<?php

namespace AstraPrefixed;

\putenv('ASTRA_APP_NAME=Astra Gatekeeper');
\putenv('ASTRA_APP_ENV=prod');
\putenv('ASTRA_APP_DEBUG=false');
\putenv('ASTRA_APP_URL=http://localhost');
\putenv('ASTRA_APP_LOG_LEVEL=400');
//monolog level = error
\putenv('ASTRA_DB_CONNECTION=sqlite');
//    putenv('ASTRA_DB_HOST=127.0.0.1');
//    putenv('ASTRA_DB_PORT=3306');
//    putenv('ASTRA_DB_DATABASE=mysql_db_name');
//    putenv('ASTRA_DB_USERNAME=root');
//    putenv('ASTRA_DB_PASSWORD=root');
//    putenv('ASTRA_DB_PREFIX=gk_');
\putenv('ASTRA_OAUTH_TOKEN_ENDPOINT=https://api.getastra.com/oauth/v2/token');
\putenv('ASTRA_OAUTH_AUTHORIZATION_ENDPOINT=https://api.getastra.com/oauth/v2/authorize_app');
//remove
\putenv('SITE_ID=5eda7c5f-39e2-4f1c-9204-c3e342664b13');
//remove
\putenv('ASTRA_CUSTOM_BLOCK_PAGE_PATH=');
\putenv('ASTRA_SENTRY_DSN=https://3c5c90aebe164dd7ad8343b59041f719@o1243586.ingest.sentry.io/6409087');
\putenv('ASTRA_CORS_ALLOWED_ORIGINS=*');
\putenv('ASTRA_API_URL_HTTPS=https://api.getastra.com/');
\putenv('ASTRA_API_URL_HTTP=http://api.getastra.com/');
\putenv('ASTRA_DASHBOARD_URL_HTTPS=https://my.getastra.com/');
\putenv('ASTRA_IP_WHITELIST=18.209.148.132,3.89.175.45');
\putenv('ASTRA_REQUEST_SIGNING_KEY=true');
//putenv('ASTRA_STORAGE_ROOT=/var/www/html');
