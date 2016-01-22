<?php
namespace Eggbe\DelegapiClient;

use \Eggbe\Helper\Arr;
use \Eggbe\Helper\Hash;
use \Eggbe\Helper\Src;

use \Eggbe\DelegapiClient\Wrapper\Item;
use \Eggbe\DelegapiClient\Wrapper\Collection;
use \Eggbe\DelegapiClient\Bridge\SecureBridge;
use \Eggbe\DelegapiClient\Watcher\Abstracts\AWatcher;

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
	private $Watchers = [];

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

		if (!Hash::validate($Config['hash'], Hash::HASH_TYPE_MD5)) {
			throw new \Exception('Invalid configuration [3]!');
		}
		$this->hash = $Config['hash'];

		if (Arr::has($Config, 'watch') && is_array($Config['watch'])) {
			foreach($Config['watch'] as $key => $class){
				if (Arr::has($this->Watchers, ($key = strtolower(trim($key))))){
					throw new \Exception('Watcher for key "' . $key . '" already exists!');
				}
				if (!is_subclass_of($class, AWatcher::class)){
					throw new \Exception('Invalid configuration [4]!');
				}
				$this->Watchers[$key] = new $class();
			}
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
	 * @throws \Exception
	 */
	public function __call($name, array $Args = []) {

		if (is_null($this->namespace)) {
			$this->namespace = Src::frcm($name, '-');
			return $this;
		}

		try {

			$Bridge = (new SecureBridge($this->url, $this->hash))
				->to($name)->where($this->namespace)->with($Args);

			if (count($this->Watchers) > 0) {
				foreach ($this->Watchers as $key => $Watcher) {
					$Bridge->attach([$key => $Watcher->watch()]);
				}
			}

			$Response = $Bridge->send();

			$this->namespace = null;

			if (array_key_exists('item', $Response)) {
				return new Item($Response['item']);
			}

			if (array_key_exists('items', $Response)) {
				return new Collection($Response['items']);
			}

			if (array_key_exists('result', $Response)) {
				return $Response['result'];
			}

			return $Response;

		} catch (\Exception $Exception) {
			$this->namespace = null;
			throw $Exception;
		}

	}

}


