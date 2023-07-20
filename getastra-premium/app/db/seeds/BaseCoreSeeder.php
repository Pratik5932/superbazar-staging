<?php

namespace AstraPrefixed;

use AstraPrefixed\Phinx\Seed\AbstractSeed;
class BaseCoreSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $options = [];
        $plugins = [];
        $optionTable = $this->table('gk_option');
        $pluginTable = $this->table('gk_plugin');
        $optionTable->insert($options)->save();
        $pluginTable->insert($plugins)->save();
    }
}
\class_alias('AstraPrefixed\\BaseCoreSeeder', 'BaseCoreSeeder', \false);
