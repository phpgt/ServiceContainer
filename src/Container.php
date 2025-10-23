<?php
namespace Gt\ServiceContainer;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;

class Container implements ContainerInterface {
	/** @var array<string, mixed> */
	private array $instances;
	/** @var array<string, mixed> */
	private array $interfaces;
	private Injector $injector;

	public function __construct(?Injector $injector = null) {
		$this->instances = [];
		$this->interfaces = [];
		$this->injector = $injector ?? new Injector($this);
	}

	public function set(mixed...$serviceList):void {
		foreach($serviceList as $service) {
			if(!is_object($service)) {
				$type = gettype($service);
				$valueString = "";
				if(!is_null($service)) {
					$valueString = " with value '$service'";
				}
				throw new ServiceContainerException("Values within the ServiceContainer must be objects, but a $type was supplied$valueString");
			}

			$id = get_class($service);
			$this->instances[$id] = $service;

			$classList = array_merge(class_parents($service), class_implements($service));
			foreach($classList as $baseClassName) {
				$this->interfaces[$baseClassName] = $service;
			}
		}
	}

	public function setLoader(string $className, callable $callback):void {
		$this->instances[$className] = $callback;
	}

	public function addLoaderClass(object $object):void {
		$refClass = new ReflectionClass($object);
		foreach($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refPublicMethod) {
			$type = $refPublicMethod->getReturnType();
			if(!$type) {
				continue;
			}
			/** @phpstan-ignore-next-line Why can't PHPStan see getName() ? */
			$className = $type->getName();
			$callback = $refPublicMethod->getClosure($object);

			$this->setLoader(
				$className,
				$callback
			);
			$classList = array_merge(class_parents($className), class_implements($className));
			foreach($classList as $baseClassName) {
				$this->setLoader($baseClassName, $callback);
			}
		}
	}

	/**
	 * @template T
	 * @param class-string<T> $id
	 * @return null|T
	 */
	public function get(string $id):mixed {
// This first "has" check is temporary, while all repositories switch from Gt to GT.
		if(!$this->has($id)) {
			if(str_starts_with($id, "GT\\")
				&& !str_starts_with($id, "GT\\Website\\")) {
				$id = preg_replace('/^GT\\\/', "Gt\\", $id);
			}
		}
		if(!$this->has($id)) {
			throw new ServiceNotFoundException($id);
		}

		$object = $this->instances[$id]
			?? $this->interfaces[$id];

		if(is_callable($object)) {
			$this->instances[$id] = $this->injector->invoke(null, $object);
			return $this->instances[$id];
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
