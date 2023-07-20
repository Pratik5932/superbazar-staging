<?php

namespace AstraPrefixed;

use AstraPrefixed\Phinx\Migration\AbstractMigration;
class BaseCoreMigration extends AbstractMigration
{
    public function change()
    {
        // Core schema:
        // Site - Matches Site entity in astra/api
        $siteTable = $this->table('sites', ['id' => \false, 'primary_key' => 'id']);
        $siteTable->addColumn('id', 'uuid')->addColumn('url', 'string')->addColumn('domain', 'string')->addColumn('name', 'string')->addColumn('connected', 'boolean')->addColumn('disconnected_message', 'string', ['null' => \true])->addColumn('worker_version', 'string', ['null' => \true])->addColumn('created_at', 'datetime')->addColumn('updated_at', 'datetime')->addColumn('php_version', 'string', ['null' => \true])->addColumn('type', 'string', ['null' => \true])->addColumn('version', 'string', ['null' => \true])->addColumn('locale', 'string', ['null' => \true])->addColumn('favorite', 'boolean')->addColumn('paused', 'boolean')->addColumn('last_synced_at', 'datetime', ['null' => \true])->addColumn('notes', 'string', ['null' => \true])->addColumn('settings', 'string', ['null' => \true])->addColumn('api_url', 'string', ['null' => \true])->addColumn('scans', 'string', ['null' => \true])->addColumn('scanner_issues', 'string', ['null' => \true])->addColumn('created_by', 'string')->addColumn('client_api_token', 'string', ['null' => \true])->addColumn('disable_pretty_urls', 'boolean', ['null' => \true])->addColumn('options', 'string', ['null' => \true])->addColumn('plugins', 'string', ['null' => \true])->addColumn('ipRules', 'string', ['null' => \true])->addColumn('subscription', 'string', ['null' => \true])->addColumn('logins', 'string', ['null' => \true])->addColumn('tags', 'string', ['null' => \true])->addColumn('threats', 'string', ['null' => \true])->addColumn('exceptions', 'string', ['null' => \true])->addColumn('client', 'string', ['null' => \true])->addColumn('is_agency', 'boolean', ['null' => \true])->addColumn('credentials', 'string', ['null' => \true])->addColumn('cms', 'string', ['null' => \true])->addColumn('team', 'string', ['null' => \true])->create();
        $pluginsTable = $this->table('plugins');
        $pluginsTable->addColumn('iri', 'string')->addColumn('version', 'string')->addColumn('firstparty', 'boolean')->create();
    }
}
\class_alias('AstraPrefixed\\BaseCoreMigration', 'BaseCoreMigration', \false);
