<?php
namespace Gt\ServiceContainer;

class Container {
	/** @var array<string, mixed> */
	private array $instances;

	public function __construct() {
		$this->instances = [];
	}

	public function set(string $className, mixed $value):void {
		$this->instances[$className] = $value;
	}

	public function get(string $className):mixed {
		return $this->instances[$className];
	}
}
