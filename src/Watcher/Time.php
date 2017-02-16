<?php
namespace Eggbe\DelegapiClient\Watcher;

use \Eggbe\DelegapiClient\Watcher\Abstracts\AWatcher;

class Time extends AWatcher {

	/**
	 * @return string
	 */
	public function watch(){
		return time();
	}

}

