<?php
namespace Eggbe\DelegapiClient;

use \Able\Helpers\Arr;
use \Able\Helpers\Src;
use \Able\Helpers\Str;
use \Able\Helpers\Hash;

use \Eggbe\ClientBridge\Bridge;

use \Eggbe\DelegapiClient\Parser\Json;
use \Eggbe\DelegapiClient\Parser\Abstracts\AParser;

use \Eggbe\DelegapiClient\Watcher\Abstracts\AWatcher;

class Client {

	/**
	 * @var string
	 */
	private $url = null;

	/**
	 * @var array
	 */
	private $Arguments = [];

	/**
	 * @param string $name
	 * @param string $value
	 * @throws \Exception
	 */
	public final function addArgument(string $name, string $value){
		/**
		 * Some keys are reserved for system use so we have to be sure
		 * about nobody will be able to break down this mechanism.
		 */
		if (in_array($name, ['namespace', 'method', 'params'])) {
			throw new \Exception('Can\'t use reserved key "' . $name . '" for custom needs!');
		}

		/**
		 * To prevent potential errors it's impossible
		 * to reassign an already existing argument.
		 */
		if (Arr::has($this->Arguments, $name)) {
			throw new \Exception('Argument with name "' . $name . '" is already registered!');
		}

		$this->Arguments[$name] = $value;
	}

	/**
	 * @param string $url
	 * @param array $Arguments
	 * @throws \Exception
	 */
	public function __construct($url, array $Arguments = []) {
		if (!filter_var($url = strtolower(trim($url)), FILTER_VALIDATE_URL)) {
			throw new \Exception('Invalid url!');
		}
		$this->url = $url;

		foreach ($Arguments as $name => $value){
			$this->addArgument($name, Str::cast($value));
		}
	}

	/**
	 * @var null
	 */
	private $Parser = null;

	/**
	 * @param AParser $Parser
	 * @throws \Exception
	 */
	public final function registerParser(AParser $Parser){
		if (!is_null($this->Parser)) {
			throw new \Exception('Parser is already registered!');
		}

		$this->Parser = $Parser;
	}

	/**
	 * @var \Closure
	 */
	private $Wrapper = null;

	/**
	 * @param \Closure $Wrapper
	 * @throws \Exception
	 */
	public final function registerWrapper(\Closure $Wrapper) {
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
		 * Some keys are reserved for system use so we have to be sure
		 * about nobody will be able to break down this mechanism.
		 */
		if (in_array($Watcher->getKey(), ['namespace', 'method', 'params'])) {
			throw new \Exception('Can\'t use reserved key "' . $Watcher->getKey() . '" for custom needs!');
		}

		/**
		 * To prevent potential errors it's impossible
		 * to reassign an already existing watcher.
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
			$this->namespace = Src::fcm($name, '-');
			return $this;
		}

		try {
			$Bridge = (new Bridge($this->url, Bridge::RM_POST))->withMethod($name)
				->withNamespace($this->namespace)->withParams($Args);

			foreach ($this->Arguments as $name => $value) {
				$Bridge->with($name, $value);
			}

			foreach ($this->Watchers as $Watcher) {
				$Bridge->with($Watcher->getKey(), $Watcher->watch());
			}

			if (is_null($this->Parser)){
				$this->Parser = new Json();
			}

			return !is_null($this->Wrapper) ? $this->Wrapper->call($this,
				$this->Parser->parse($Bridge->send())) : $Bridge->send();

		} finally {
			$this->namespace = null;
		}

	}

}


