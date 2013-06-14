<?php namespace Levare\Modules;

/**
 * Diese Klasse startet den ModuleManager
 * 
 * @package Levare/Modules
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @license BSD-3-Clause
 * @version 1.1.0
 * @access public
 */

use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('levare/modules');
		require_once 'helpers.php';

		// Laden aller Autoloader Dateien aus den Modulen
		$this->app['modules']->before();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['modules'] = $this->app->share(function($app)
		{
			return new Modules($app['files'], $app['config'], $app['view']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('Modules');
	}

}