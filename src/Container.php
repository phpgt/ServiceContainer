<?php
namespace Gt\ServiceContainer;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface {
	/** @var array<string, mixed> */
	private array $instances;
	/** @var array<string, mixed> */
	private array $interfaces;

	public function __construct() {
		$this->instances = [];
		$this->interfaces = [];
	}

	public function set(mixed $value):void {
		if(is_object($value)) {
			$id = get_class($value);
		}
		else {
			$id = (string)$value;
		}

		$this->instances[$id] = $value;

		$classList = array_merge(class_parents($value), class_implements($value));
		foreach($classList as $baseClassName) {
			$this->interfaces[$baseClassName] = $value;
		}
	}

	public function get(string $id):mixed {
		if(!$this->has($id)) {
			throw new ServiceNotFoundException($id);
		}

		return $this->instances[$id]
			?? $this->interfaces[$id];
	}

	public function has(string $id):bool {
		return !is_null(
			$this->instances[$id]
			?? $this->interfaces[$id]
			?? null
		);
	}
}
