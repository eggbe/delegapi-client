<?php
namespace Eggbe\DelegapiClient\Wrapper;

class Collection implements \Iterator {

	/**
	 * @var array
	 */
	private $Items = [];

	/**
	 * @param array $Items
	 */
	public final function __construct(array $Items) {
		foreach ($Items as $Item) {
			$this->Items[] = new Item($Item);
		}
	}

	/**
	 * @return Item
	 */
	public final function current() {
		return current($this->Items);
	}

	/**
	 * @return int
	 */
	public final function key() {
		return key($this->Items);
	}

	/**
	 * @return Item
	 */
	public final function next() {
		return next($this->Items);
	}

	/**
	 * @return Item
	 */
	public final function rewind() {
		return reset($this->Items);
	}

	/**
	 * @return bool
	 */
	public final function valid() {
		return $this->key() !== false && $this->key() !== null;
	}

	/**
	 * @return array
	 */
	public final function toArray() {
		return $this->Items;
	}

	/**
	 * @param string|array $name
	 * @param string $key
	 * @return array
	 */
	public final function lists($name, $key = null) {
		$Lists = [];
		foreach ($this->Items as $Item) {

			$value = is_array($name)
				? Arr::only($Item->toArray(), $name)
					: $Item->{$name};

			if (!empty($value) > 0) {
				if (is_null($key)) {
					$Lists[] = $value;
				} elseif (isset($Item->{$key})) {
					$Lists[$Item->{$key}] = $value;
				}

			}
		}
		return $Lists;
	}

}
