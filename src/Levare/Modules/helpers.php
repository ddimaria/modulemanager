<?php 

/**
 * Diese Datei stellt Helfer bereit welche benötigt werden
 * 
 * @package Levare/Modules
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @license BSD-3-Clause
 * @version 1.1.4
 * @access public
 */


/**
 * Gibt den Pfad zum Modules Ordner zurück
 * 
 * @return string
 */
if(!function_exists('module_path'))
{
	function module_path()
	{
		$moduleFolder = Config::get('modules::module_folder_location');
		
		return str_finish(app()->make('path.base'), '/') . $moduleFolder;
	}
}