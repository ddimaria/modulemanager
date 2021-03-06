<?php namespace Levare\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Pluralizer;
use Levare\Modules\Facades\modules;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CopyModulesCommand extends Command {

    /**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'modules:copy';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Copy an existing module.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$moduleOld = strtolower($this->argument('moduleOld'));
		$moduleNew = strtolower($this->argument('moduleNew'));
		$verbose = $this->option('verbose');
		$seed = $this->option('seed');
		
		$pathOld = modules::getPath($moduleOld);
		$pathNew = module_path() . '/' . $moduleNew;
		
		$singularOld = Pluralizer::singular($moduleOld);
		$pluralOld = Pluralizer::plural($moduleOld);		
		$singularNew = Pluralizer::singular($moduleNew);
		$pluralNew = Pluralizer::plural($moduleNew);
		
		$app = \App::make('app');
		$app['files']->copyDirectory($pathOld, $pathNew);
		$files = $app['files']->allFiles($pathNew);
		
		foreach ($files as $file) {
			$filePath = $file->getRealPath();
			$filePathNew = $this->replaceModuleName($filePath, $singularOld, $singularNew, $pluralOld, $pluralNew);
			rename($filePath, $filePathNew);
			$this->replaceModuleInFile($filePathNew, $singularOld, $singularNew, $pluralOld, $pluralNew);
			
			if ($verbose) {
				$this->line($filePathNew);
			}
		}
		
		if ($seed) {
			$app['modules']->modules += array($moduleNew => $pathNew);
			$app['modules']->before();
			
			$artisan = new \Illuminate\Foundation\Artisan(\App::make('app'));
			//$artisan->call('dump-autoload');
			$seedClass = ($seed === true) ? ucfirst($singularNew) . 'TableSeeder' : $seed;
			$artisan->call('db:seed', array('--class' => $seedClass));
			
			if ($verbose) {
				$this->line($seedClass . ' seeded.');
			}
		}
		
		$this->line($moduleNew . ' successfully created.');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('moduleOld', InputArgument::REQUIRED, 'The module to copy'),
			array('moduleNew', InputArgument::REQUIRED, 'The module to create'),
		);
	}
	
	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('seed', null, InputOption::VALUE_OPTIONAL, 'Seed the database: assign a name of the seed class to override the default class convention \'ModuleTableSeeder\'.', false),
		);
	}
	
	/**
	 * Replace the name of the old module with the new one in a string.
	 *
	 * @param  string  $str				The string to search
	 * @param  string  $singularOld		The singular name of the old module
	 * @param  string  $singularNew		The singular name of the new module
	 * @param  string  $pluralOld		The plural name of the old module
	 * @param  string  $pluralNew		The plural name of the new module
	 * @return string
	 */
	protected function replaceModuleName($str, $singularOld, $singularNew, $pluralOld, $pluralNew)
	{
		return str_replace(
			array($pluralOld, ucfirst($pluralOld), $singularOld, ucfirst($singularOld)),
			array($pluralNew, ucfirst($pluralNew), $singularNew, ucfirst($singularNew)),
			$str
		);
	}	
	
	/**
	 * Replace the name of the old module with tne new one in a file.
	 *
	 * @param  string  $filePath		The full path to the file
	 * @param  string  $singularOld		The singular name of the old module
	 * @param  string  $singularNew		The singular name of the new module
	 * @param  string  $pluralOld		The plural name of the old module
	 * @param  string  $pluralNew		The plural name of the new module
	 * @return string
	 */
	protected function replaceModuleInFile($filePath, $singularOld, $singularNew, $pluralOld, $pluralNew)
	{
		$str = file_get_contents($filePath);
		$str = $this->replaceModuleName($str, $singularOld, $singularNew, $pluralOld, $pluralNew);
		file_put_contents($filePath, $str);
	}
}
