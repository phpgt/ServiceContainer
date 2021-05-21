<?php
namespace Gt\ServiceContainer;

use ReflectionClass;
use ReflectionNamedType;

class Injector {
	public function __construct(
		private Container $container
	) {
	}

	/**
	 * @param object $instance The instance of the object containing the
	 * method to invoke.
	 * @param string $methodName The method name to invoke.
	 * @param array<string, mixed> $extraArgs An associative array where the
	 * keys will match the method parameters by *name*, for passing values
	 * of PHP's inbuilt types like scalar values.
	 * @return mixed The return value of the invoked method.
	 */
	public function invoke(
		object $instance,
		string $methodName,
		array $extraArgs = []
	):mixed {
		$arguments = [];

		$refClass = new ReflectionClass($instance);
		$refMethod = $refClass->getMethod($methodName);
		foreach($refMethod->getParameters() as $refParam) {
			/** @var ReflectionNamedType|null $refType */
			$refType = $refParam->getType();
			if(is_null($refType)
			|| $refType->isBuiltin()) {
				array_push(
					$arguments,
					$extraArgs[$refParam->getName()]
				);
			}
			else {
				array_push(
					$arguments,
					$this->container->get($refType)
				);
			}
		}

		return $refMethod->invoke($instance, ...$arguments);
	}
}
