<?php namespace Levare\Modules;

/**
 * Diese Klasse bearbeitet alle Module relevanten Aufrufe
 * 
 * I want to thank these people for their outstanding amendments
 * Edwin Luijten <https://github.com/Edwin-Luijten>
 * Ryun S. <https://github.com/ryun>
 * Steve <https://github.com/stevemo>
 *
 * @package Levare/Modules
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @license BSD-3-Clause
 * @version 1.1.4
 * @access public
 */

use App;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository;
use Illuminate\Support\ClassLoader;

class Modules {

	/**
	 * Enthält die Filesystem Instanz
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	public $files;

	/**
	 * Enthält die Config Instanz
	 *
	 * @var Illuminate\Config\Repository
	 */
	public $config;

	/**
	 * Enthält alle Module
	 *
	 * @var array
	 */
	public $modules = array();

	/**
	 * Erstellt eine neue Instanz der Modules Klasse
	 *
	 * @param Illuminate\Filesystem\Filesystem $files
	 * @param Illuminate\Config\Repository $config
	 * @return void
	 */
	public function __construct(Filesystem $files, Repository $config)
	{
		$this->files = $files;
		$this->config = $config;

		// Prüft ob der gesetzte Modules Ordner existiert
		if(!$this->checkModulePath()) return false;

		// Verarbeitet alle Module und pass das Array an
		$this->parse();

		// Registriert alle Module
		$this->registerFolders();
	}

	/**
	 * Prüft ob der Module Pfad existiert
	 *
	 * @return boolean
	 */
	private function checkModulePath()
	{
		$path = str_finish(base_path(), '/').$this->config->get('modules::module_folder_location');
		return (is_readable($path)) ? true : false;
	}

	/**
	 * Laden aller benötigen Dateien aus den Module beim booten dieser Klasse
	 *
	 * @return void
	 */
	public function before()
	{
        $modules = array_keys($this->modules);
		foreach($modules as $module)
		{
            $this->loadModuleFiles($module);
		}
	}

	/**
	 * Alle Module aus dem Modules Ordner verarbeiten und den Pfad abschneidem
	 *
	 * @return void
	 */
	private function parse()
	{
		$modules = $this->modules();
		foreach($modules as $module)
		{
			//Remove module_path from module name
			$moduleName = last(explode('/', str_replace('\\', '/', $module)));

			$this->modules[$moduleName] = $module;
		}
	}

	/**
	 * Listet alle Module mit dem absoluten Pfad auf
	 *
	 * @return array
	 */
	private function modules()
	{
		return $this->files->directories(module_path());
	}

	/**
	 * Gibt einen spezifischen Module Pfad aus
	 *
	 * @param string $module;
	 * @return string
	 */
	public function getPath($module)
	{
		if(array_key_exists($module, $this->modules))
		{
			return str_finish($this->modules[$module], '/');
		}

		return null;
	}

    /**
     * Load the module.json file
     * then register the required files
     * and the global namespace
     *
     *
     * @return void
     */
    private function loadModuleFiles($module)
    {
        $file = $this->getJSONFile($module);
        $this->loadRequiredFiles($file, $module);
        $this->registerGlobalNamespace($file, $module);
    }

	/**
	 * try to load the the module.json file.
     * if it doesn't exist then try to create it.
	 *
	 * @return array
	 */
	public function getJSONFile($module)
	{
        $path = str_finish($this->modules[$module], '/');

        try
        {
            $content = $this->files->get($path.'module.json');
            return json_decode($content);
        }
        catch (\Illuminate\Filesystem\FileNotFoundException $e)
        {
            $this->createFile($module);
            return $this->getJSONFile($module);
        }
	}

    /**
     * Erstellt die module.json Datei
     * 
     * @param string $module
     * @return void
     */
    private function createFile($module)
    {
        $module = $this->modules[$module];
        if($this->files->isWritable($module))
        {
            $this->files->copy(__DIR__.'/../templates/module.json', $module.'/module.json');
        }
        else
        {
            return App::abort('403', "Please set writable permissions to " . $module . "\n");
        }
    }

    /**
     * Prüft ob der Autoload Bereich in der module.json existiert
     * wenn ja, dann wird dieser durchlaufen und alle benötigten
     * Dateien eingelesen
     *
     *
     * @param  string $files
     * @param  string $module
     * @return void
     */
    private function loadRequiredFiles($files, $module)
    {
        if(property_exists($files, 'autoload'))
        {
            foreach ($files->autoload as $file)
            {
                include $this->modules[$module] . '/' . $file;
            }
        }
    }

    /**
     * Prüft ob der Global Bereich in der module.json existiert
     * wenn ja, dann wird dieser durchlaufen und alle benötigten
     * Ordner dem ClassLoader hinzugefügt.
     *
     *
     * @param  string $files
     * @param  string $module
     * @return void
     */
    private function registerGlobalNamespace($files, $module)
    {
        $directories = array();

        if(property_exists($files, 'global'))
        {
            foreach ($files->global as $global)
            {
                $directories = $this->getPath($module).$global;
            }
            ClassLoader::addDirectories($directories);
        }
    }

	/**
	 * Registrieren aller Ordner welche zu einem Namespace hinzugefügt werden können
	 *
	 * @return void
	 */
	private function registerFolders()
	{
		foreach($this->modules as $name => $path)
		{
			$name = strtolower($name);
			App::make('view')->addNamespace($name, $path.'/views');
			App::make('config')->addNamespace($name, $path.'/config');
			App::make('translator')->addNamespace($name, $path.'/lang');
		}
	}

}
