<?php
namespace Eggbe\DelegapiClient;

use \Eggbe\Helper\Arr;
use \Eggbe\Helper\Hash;
use \Eggbe\Helper\Src;

use \Eggbe\ClientBridge\Bridge;

use \Eggbe\DelegapiClient\Watcher\Abstracts\AWatcher;

class Client {

	/**
	 * @var string
	 */
	private $url = null;

	/**
	 * @var string
	 */
	private $token = null;

	/**
	 * @param string $url
	 * @param string $token
	 * @throws \Exception
	 */
	public function __construct($url, $token) {
		if (!filter_var($url = strtolower(trim($url)), FILTER_VALIDATE_URL)) {
			throw new \Exception('Invalid url!');
		}
		$this->url = $url;

		if (strlen($token) != 32 || !ctype_xdigit($token)) {
			throw new \Exception('Invalid security token!');
		}
		$this->token = $token;
	}

	/**
	 * @var \Closure
	 */
	private $Wrapper = null;

	/**
	 * @param \Closure $Wrapper
	 * @throws \Exception
	 */
	public final function addWrapper(\Closure $Wrapper) {
		if (!is_null($this->Wrapper)) {
			throw new \Exception('Wrapper is already registered!');
		}

		$this->Wrapper = $Wrapper;
	}

	/**
	 * @var array
	 */
	private $Watchers = [];

	/**
	 * @param AWatcher $Watcher
	 * @throws \Exception
	 */
	public final function addWatcher(AWatcher $Watcher) {

		/**
		 * Three keys are always use for system needs so we have to be
		 * sure that nobody will be able to break this logic.
		 */
		if (in_array($Watcher->getKey(), ['namespace', 'method', 'params'])) {
			throw new \Exception('Can\'t use reserved key "' . $Watcher->getKey() . '" for custom needs!');
		}

		/**
		 * To prevent all kind of potential errors, the reassigning
		 * of already existing keys isn't possible.
		 */
		if (Arr::has($this->Watchers, $Watcher->getKey())) {
			throw new \Exception('Watcher for key "' . $Watcher->getKey() . '" is already registered!');
		}

		$this->Watchers[$Watcher->getKey()] = $Watcher;
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
			$Bridge = (new Bridge($this->url))->withToken($this->token)
				->withMethod($name)->withNamespace($this->namespace)->withParams($Args);

			if (count($this->Watchers) > 0) {
				foreach ($this->Watchers as $Watcher) {
					$Bridge->with($Watcher->getKey(), $Watcher->watch());
				}
			}

			return !is_null($this->Wrapper)
				? $this->Wrapper($Bridge->send()) : $Bridge->send();

		} finally {
			$this->namespace = null;
		}

	}

}


