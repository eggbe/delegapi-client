<?php
namespace Eggbe\DelegapiClient\Parser;

use \Eggbe\DelegapiClient\Parser\Abstracts\AParser;

class Json extends AParser {

	public function parse(string $Response): array {
		if (is_null($Response = json_decode($Response, true))){
			throw new \Exception('Invalid response!');
		}

		return $Response;
	}
}
