<?php
namespace Eggbe\DelegapiClient\Wrapper\Abstracts;

use \Eggbe\DelegapiClient\Wrapper\Item;
use \Eggbe\DelegapiClient\Wrapper\Collection;

abstract class AWrapper {

	/**
	 * @param array $Data
	 * @return string
	 */
	abstract function wrap(array $Data);

}
