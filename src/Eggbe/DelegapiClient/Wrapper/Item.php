<?php
namespace Eggbe\DelegapiClient\Wrapper;

class Item {

	/**
	 * @var array
	 */
	private $Fields = [];

	/**
	 * @param array $Fields
	 */
	public final function __construct(array $Fields) {
		foreach ($Fields as $name => $value) {
			if (is_array($value)) {
				$this->Fields[$name] = new self($value);
			} else {
				$this->Fields[$name] = $value;
			}
		}
	}

	/**
	 * @return array
	 */
	public final function toArray() {
		return $this->Fields;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public final function __get($name){
		return array_key_exists($name, $this->Fields) ? $this->Fields[$name] : null;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public final function __isset($name){
		return array_key_exists($name, $this->Fields);
	}

	/**
	 * @return bool
	 */
	public final function isError(){
		return array_key_exists('error', $this->Fields) && (bool)$this->Fields['error'];
	}

}
