<?php namespace Bolo\Bolo\Tests;

use System\Classes\PluginManager;
use System\Classes\UpdateManager;

class TestCase extends \TestCase
{

    public function createApplication()
    {
        $app = require __DIR__ . '/../../../../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $app['cache']->setDefaultDriver('array');
        $app->setLocale('en');

        /*
         * Store database in memory
         */
//        $app['config']->set('database.default', 'sqlite');
//        $app['config']->set('database.connections.sqlite', [
//            'driver'   => 'sqlite',
//            'database' => ':memory:',
//            'prefix'   => ''
//        ]);

        /*
         * Modify the plugin path away from the test context
         */
        //$app->setPluginsPath(realpath(base_path().\Config::get('cms.pluginsPath')));

        return $app;
    }

    protected static $firstTime = true;

    public function setUp()
    {
        if (!$this->app) {
            $this->app = $this->createApplication();
        }

        UpdateManager::instance()->bindContainerObjects();
        PluginManager::instance()->bindContainerObjects();
        if (!static::$firstTime) {
            PluginManager::instance()->registerAll(true);
            PluginManager::instance()->bootAll(true);
        }

        static::$firstTime = false;
    }
}
