#!/bin/bash
php ./magento module:enable Vnecoms_Sms
php ./magento module:enable Vnecoms_SmsBulkSms
php ./magento module:enable Vnecoms_SmsClickatell
php ./magento module:enable Vnecoms_SmsGlobal
php ./magento module:enable Vnecoms_SmsMessagebird
php ./magento module:enable Vnecoms_SmsNexmo
php ./magento module:enable Vnecoms_SmsTeleSign
php ./magento module:enable Vnecoms_SmsTwilio
php ./magento module:enable Vnecoms_SmsKapsystem

php ./magento setup:upgrade
chmod -R 777 ../var/cache
chmod -R 777 ../var/log
chmod -R 777 ../var/generation