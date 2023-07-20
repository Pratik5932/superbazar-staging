<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Middleware;

//use Interop\Container\ContainerInterface;
use AstraPrefixed\GetAstra\Plugins\Scanner\Models\Option;
use AstraPrefixed\Psr\Container\ContainerInterface;
class DBSchema
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;
    /**
     * @var \Illuminate\Database\Schema
     */
    private $schema;
    private $db;
    /**
     * HaltResponse constructor.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->schema = $container->get('schema');
        $this->db = $container->get('db');
    }
    public function exists()
    {
        return $this->schema->hasTable('options');
    }
    public function createIfMissing()
    {
        if (!$this->exists()) {
            $this->dropAllTables();
            $this->createAllTables();
            $this->seedTables();
        }
        //$this->container->get('dispatcher')->dispatch('gk.schema.init');
    }
    public function __invoke($request, $response, $next)
    {
        //$this->dropAllTables();
        if (!$this->schema->hasTable('options')) {
            $this->createAllTables();
            $this->seedTables();
        } else {
            if (!$this->schema->hasColumn('issues', 'deleteKey')) {
                $this->schema->table('issues', function ($table) {
                    $table->string('deleteKey')->nullable();
                });
            }
            if (!$this->schema->hasColumn('issues', 'fileContents')) {
                $this->schema->table('issues', function ($table) {
                    $table->text('fileContents')->nullable();
                });
            }
        }
        //$this->container->get('dispatcher')->dispatch('gk.schema.init');
        return $next($request, $response);
    }
    protected function dropAllTables()
    {
        //$this->schema->dropIfExists('ipBlocks');
        $this->schema->dropIfExists('options');
        $this->schema->dropIfExists('issues');
        $this->schema->dropIfExists('pendingIssues');
        $this->schema->dropIfExists('ipHostnameCache');
    }
    protected function createAllTables()
    {
        // Options table
        $this->schema->create('options', function ($t) {
            $t->string('name', 100)->primary();
            $t->longtext('val');
            $t->string('group', 30)->default('core');
            $t->enum('autoload', ['yes', 'no'])->default('yes');
        });
        // IP Blocking table
        //        $this->schema->create('ipBlocks', function ($t) {
        //            $t->bigIncrements('id')->unsigned();
        //            $t->integer('type')->unsigned()->default(0);
        //            $t->string('ipAddress', 45)->default('0.0.0.0');
        //            $t->bigInteger('blockedTime');
        //            $t->string('reason');
        //            $t->integer('lastAttempt')->unsigned()->default(0);
        //            $t->integer('blockedHits')->unsigned()->default(0);
        //            $t->bigInteger('expiration')->unsigned()->default(0);
        //            $t->text('parameters');
        //
        //            $t->index('type');
        //            $t->index('ipAddress');
        //            $t->index('expiration');
        //        });
        // Issues table
        $this->schema->create('issues', function ($t) {
            $t->increments('id')->unsigned();
            //$t->integer('time')->unsigned();
            //$t->integer('lastUpdated')->unsigned();
            $t->text('path');
            $t->string('status', 10);
            $t->string('type', 20);
            $t->string('severity', 8);
            $t->char('ignorePath', 32);
            $t->char('ignoreChecksum', 32);
            $t->string('shortMsg');
            $t->string('deleteKey');
            $t->text('longMsg');
            $t->text('data');
            $t->text('fileContents');
            $t->nullableTimestamps();
            //$t->index('lastUpdated');
            $t->index('status');
            $t->index('ignorePath');
            $t->index('ignoreChecksum');
        });
        // Pending Issues
        $this->schema->create('pendingIssues', function ($t) {
            $t->increments('id')->unsigned();
            $t->integer('time')->unsigned();
            $t->integer('lastUpdated')->unsigned();
            $t->string('status', 10);
            $t->string('type', 20);
            $t->string('severity');
            $t->char('ignorePath', 32);
            $t->char('ignoreChecksum', 32);
            $t->string('shortMsg');
            $t->string('longMsg');
            $t->text('data');
            $t->index('lastUpdated');
            $t->index('status');
            $t->index('ignorePath');
            $t->index('ignoreChecksum');
        });
        // IP Hostname Cache
        $this->schema->create('ipHostnameCache', function ($t) {
            $t->string('ipAddress', 45)->default('0.0.0.0');
            $t->string('host');
            $t->integer('lastUpdated')->unsigned();
            $t->primary('ipAddress');
        });
    }
    protected function seedTables()
    {
        // Options Table
        $rows = [['name' => 'active_plugins', 'val' => [], 'autoload' => 'yes', 'group' => 'core']];
        foreach ($rows as $row) {
            Option::create($row);
        }
    }
}
