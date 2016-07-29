<?php
namespace Eggbe\DelegapiClient\Watcher\Abstracts;

use \Eggbe\Helper\Src;

abstract class AWatcher {

	/**
	 * @var string
	 */
	protected $key = null;

	/**
	 * @return string
	 */
	public function getKey() {
		return is_null($this->key) ? Src::frcm(preg_replace('/^.*\\\/',
			null, get_class($this))) : $this->key;
	}

	/**
	 * @return return mixed
	 */
	abstract function watch();

}

