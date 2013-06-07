<?php 

/**
 * Gibt den Pfad zum Modules Ordner zurück
 * 
 * @return string
 */
if(!function_exists('module_path'))
{
	function module_path()
	{
		return str_finish(app()->make('path.base'), '/') . 'levare/Modules';
	}
}