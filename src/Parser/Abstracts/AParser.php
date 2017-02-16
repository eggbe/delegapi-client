<?php
namespace Eggbe\DelegapiClient\Parser\Abstracts;

abstract class AParser {

	/**
	 * @param string $input
	 * @return array
	 */
	abstract public function parse(string $input): array;
}
