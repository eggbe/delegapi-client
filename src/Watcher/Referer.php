<?php
namespace Eggbe\DelegapiClient\Watcher;

Use \Eggbe\DelegapiClient\Watcher\Abstracts\AWatcher;

class Referer extends AWatcher {

	/**
	 * @return string
	 */
	public function watch(){
		return array_key_exists('HTTP_HOST', $_SERVER)
			? $_SERVER['HTTP_HOST'] : null;
	}

}

