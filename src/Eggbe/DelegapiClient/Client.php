<?php
namespace Eggbe\DelegapiClient;

use \Eggbe\Helpers\Arr;
use \Eggbe\Helpers\Hash;
use \Eggbe\Helpers\Code;

use \Eggbe\DelegapiClient\Wrapper\Item;
use \Eggbe\DelegapiClient\Wrapper\Collection;
use \Eggbe\DelegapiClient\Bridge\SecureBridge;

class Client {

	/**
	 * @var string
	 */
	private $url = null;

	/**
	 * @var string
	 */
	private $hash = null;

	/**
	 * @var array
	 */
	private $Attachable = [];

	/**
	 * @param array $Config
	 * @throws \Exception
	 */
	public function __construct(array $Config) {
		if (!Arr::has(($Config = array_change_key_case($Config, CASE_LOWER)), 'url', 'hash')) {
			throw new \Exception('Invalid configuration [1]!');
		}

		if (!filter_var($Config['url'] . FILTER_VALIDATE_URL)) {
			throw new \Exception('Invalid configuration [2]!');
		}
		$this->url = $Config['url'];

		if (!Hash::validate($Config['hash'], Hash::VALIDATE_MD5)) {
			throw new \Exception('Invalid configuration [3]!');
		}
		$this->hash = $Config['hash'];

		if (Arr::has($Config, 'attach')) {
			if (!is_array($Config['attach'])) {
				throw new \Exception('Invalid configuration [4]!');
			}
			$this->Attachable = Arr::simplify($Config['attach']);
		}

	}

	/**
	 * @var string
	 */
	private $namespace = null;

	/**
	 * @param string $name
	 * @param array $Args
	 * @return Client|string
	 */
	public function __call($name, array $Args = []) {

		if (is_null($this->namespace)){
			$this->namespace = Code::fromCamelNotation($name, '-');
			return $this;
		}

		$Response = (new SecureBridge($this->url, $this->hash))
			->to($name)->where($this->namespace)->with($Args)
				->attach($this->Attachable)->send();

		$this->namespace = null;

		if (array_key_exists('item', $Response)){
			return new Item($Response['item']);
		}

		if (array_key_exists('items', $Response)){
			return new Collection($Response['items']);
		}

		if (array_key_exists('result', $Response)){
			return $Response['result'];
		}

		return $Response;
	}

}


