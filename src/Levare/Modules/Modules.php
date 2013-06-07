<?php namespace Levare\Modules;

use App;
use Illuminate\Filesystem\Filesystem;

class Modules {

	/**
	 * Enthält die Filesysten Instanz
	 * 
	 * @var Illuminate\Filesystem\Filesystem
	 */
	public $files;

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
	 * @return void
	 */
	public function __construct(Filesystem $files)
	{
		$this->files = $files;

		// Verarbeitet alle Module und pass das Array an
		$this->parse();

		// Prüft ob alle benötigten Dateien im Module vorhanden sind
		$this->checkModules();
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
			$this->loadRequiredFiles($module);
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
			$moduleName = last(explode('/', $module));
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
	 * Prüft ob alle benötigten Dateien im Module vorhanden sind.
	 * Wenn nicht dann werden diese angelegt
	 * 
	 * @return void
	 */
	private function checkModules()
	{
		$out = '';
		foreach($this->modules() as $module)
		{
			if($this->files->isWritable($module))
			{
				if(!$this->files->exists($path = $module.'/module.json'))
				{
					$this->files->copy(__DIR__.'/../templates/module.json', $module.'/module.json');
				}

				if(!$this->files->exists($path = $module.'/routes.php'))
				{
					$this->files->copy(__DIR__.'/../templates/routes.php', $module.'/routes.php');
				}

			}
			else
			{
				$out .= "Please set writable permissions to " . $module . "\n";
			}
		}

		if($out !== '')
		{
			return App::abort('403', $out);
		}
		else
		{
			$this->registerFolders();
		}
	}

	/**
	 * Gibt die Module JSON Datein als Array aus
	 * 
	 * @return array
	 */
	public function getJSONFile($module)
	{
		$path = str_finish($this->modules[$module], '/');
		$content = $this->files->get($path.'module.json');
		return json_decode($content);
	}

	/**
	 * Laden der Dateien aus dem Autoload Array
	 * 
	 * @param string $module
	 * @return void
	 */
	private function loadRequiredFiles($module)
	{
		foreach($this->getJSONFile($module)->autoload as $autoload)
		{
			include $this->modules[$module] . '/' . $autoload;
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