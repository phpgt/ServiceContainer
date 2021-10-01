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
		if(!is_object($value)) {
			$type = gettype($value);
			$valueString = "";
			if(!is_null($value)) {
				$valueString = " with value '$value'";
			}
			throw new ServiceContainerException("Values within the ServiceContainer must be objects, but a $type was supplied$valueString");
		}

		$id = get_class($value);
		$this->instances[$id] = $value;

		$classList = array_merge(class_parents($value), class_implements($value));
		foreach($classList as $baseClassName) {
			$this->interfaces[$baseClassName] = $value;
		}
	}

	public function setLoader(string $className, callable $callback):void {
		$this->instances[$className] = $callback;
	}

	public function get(string $id):mixed {
		if(!$this->has($id)) {
			throw new ServiceNotFoundException($id);
		}

		$object = $this->instances[$id]
			?? $this->interfaces[$id];

		if(is_callable($object)) {
			return $object();
		}

		return $object;
	}

	public function has(string $id):bool {
		return !is_null(
			$this->instances[$id]
			?? $this->interfaces[$id]
			?? null
		);
	}
}
