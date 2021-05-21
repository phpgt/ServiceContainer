<?php
namespace Gt\ServiceContainer;

class Container {
	/** @var array<string, mixed> */
	private array $instances;
	/** @var array<string, mixed> */
	private array $interfaces;

	public function __construct() {
		$this->instances = [];
		$this->interfaces = [];
	}

	public function set(string $className, mixed $value):void {
		$this->instances[$className] = $value;

		foreach(class_implements($className) as $interface) {
			$this->interfaces[$interface] = $value;
		}
	}

	public function get(string $className):mixed {
		$value = $this->instances[$className]
			?? $this->interfaces[$className]
			?? null;

		if(is_null($value)) {
			throw new ServiceNotFoundException($className);
		}

		return $value;
	}
}
