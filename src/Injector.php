<?php
namespace Gt\ServiceContainer;

use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;

class Injector {
	public function __construct(
		private Container $container
	) {
	}

	/** @param array<string, object> $extraArgs */
	public function invoke(
		?object $instance,
		string|callable $functionName,
		array $extraArgs = [],
	):mixed {
		$arguments = [];

		if($instance) {
			$refClass = new ReflectionClass($instance);
			$refFunction = $refClass->getMethod($functionName);
		}
		else {
			$refFunction = new ReflectionFunction($functionName);
		}

		foreach($refFunction->getParameters() as $refParam) {
			/** @var ReflectionNamedType|null $refType */
			$refType = $refParam->getType();

// Check if we have a match in $extraArgs, otherwise get from the container:
			/** @var class-string $className */
			$className = $refType->getName();
			if(array_key_exists($className, $extraArgs)) {
				array_push(
					$arguments,
					$extraArgs[$className],
				);
			}
			else {
				array_push(
					$arguments,
					$this->container->get($className)
				);
			}

		}

		if($instance) {
			array_unshift($arguments, $instance);
		}

		return $refFunction->invoke(...$arguments);
	}
}
