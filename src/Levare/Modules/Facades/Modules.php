<?php namespace Levare\Modules\Facades;

/**
 * Diese Klasse erlaubt es normale Methoden aufrufe auch statisch zu machen
 * 
 * @package Levare/Modules
 * @author Florian Uhlrich <f.uhlrich@levare-cms.de>
 * @copyright Copyright (c) 2013 by Levare Project Team
 * @license BSD-3-Clause
 * @version 1.1.0
 * @access public
 */

use Illuminate\Support\Facades\Facade;

class Modules extends Facade {

	/**
	 * Get the registered name of the component
	 * 
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'modules';
	}
}