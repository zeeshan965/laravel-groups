<?php

namespace Psycho\Groups;

use Illuminate\Support\ServiceProvider;

class GroupsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     *
     */
    public function boot ()
    {
        $this -> publishMigrations ();
        $this -> publishConfig ();
    }

    public function register ()
    {
        $this -> registerGroups ();
    }

    private function registerGroups ()
    {
        $this -> app -> bind ( 'groups', function () {
            return $this -> app -> make ( 'Psycho\Groups\Groups' );
        } );
    }

    /**
     * Publish package's migrations.
     *
     * @return void
     */
    public function publishMigrations ()
    {
        $timestamp = date ( 'Y_m_d_His', time () );
        $stub = __DIR__ . '/../database/migrations/create_groups_tables.php';
        $target = $this -> app -> databasePath () . '/migrations/' . $timestamp . '_create_groups_tables.php';
        $this -> publishes ( [ $stub => $target ], 'groups.migrations' );
    }

    /**
     * Publish package's config file.
     *
     * @return void
     */
    public function publishConfig ()
    {
        $this -> publishes ( [
            __DIR__ . '/../config' => config_path (),
        ], 'groups.config' );
    }
}
