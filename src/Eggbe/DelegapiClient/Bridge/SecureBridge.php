<?php
namespace Eggbe\DelegapiClient\Bridge;

use \Eggbe\Helper\Hash;
use \Eggbe\ClientBridge\Bridge;

class SecureBridge extends  Bridge{

	/**
	 * @var string
	 */
	protected $hash = null;

	/**
	 * @param string $url
	 * @param string $hash
	 * @throws \Exception
	 */
	public function __construct($url, $hash) {
		parent::__construct($url);
		if (!Hash::validate($hash, Hash::VALIDATE_MD5)){
			throw new \Exception('Invalid hash format!');
		}
		$this->hash = $hash;
	}

}

