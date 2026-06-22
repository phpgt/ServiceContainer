<?php
namespace Gt\ServiceContainer;

use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;

class Injector {
	public function __construct(
		private Container $container
	) {
	}

	/**
	 * @param object|null $instance The instance of the object containing
	 * the method to invoke.
	 * @param string $functionName The method name to invoke.
	 * @param array<string, mixed> $extraArgs An associative array where the
	 * keys will match the method parameters by *name*, for passing values
	 * of PHP's inbuilt types like scalar values.
	 * @return mixed The return value of the invoked method.
	 */
	public function invoke(
		?object $instance,
		string|callable $functionName,
		array $extraArgs = []
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
			array_push($arguments, $this->resolveParameter($refParam, $extraArgs));
		}

		if($instance) {
			array_unshift($arguments, $instance);
		}

		return $refFunction->invoke(...$arguments);
	}

	/** @param array<string, mixed> $extraArgs */
	private function resolveParameter(
		ReflectionParameter $refParam,
		array $extraArgs,
	):mixed {
		$refType = $refParam->getType();
		if(!$refType instanceof ReflectionNamedType) {
			return $this->resolveUntypedParameter($refParam, $extraArgs);
		}

		/** @var class-string $refParamTypeName */
		$refParamTypeName = $refType->getName();

		if(array_key_exists($refParam->getName(), $extraArgs)) {
			return $extraArgs[$refParam->getName()];
		}
		if(array_key_exists($refParamTypeName, $extraArgs)) {
			return $extraArgs[$refParamTypeName];
		}
		if($refParam->isDefaultValueAvailable()) {
			return $refParam->getDefaultValue();
		}
		if($refType->isBuiltin()) {
			return $this->resolveBuiltinParameter($refType, $refParamTypeName);
		}

		return $this->resolveServiceParameter($refType, $refParamTypeName);
	}

	/** @param array<string, mixed> $extraArgs */
	private function resolveUntypedParameter(
		ReflectionParameter $refParam,
		array $extraArgs,
	):mixed {
		if($refParam->isDefaultValueAvailable()) {
			return $refParam->getDefaultValue();
		}

		return $extraArgs[$refParam->getName()] ?? null;
	}

	/** @param class-string $refParamTypeName */
	private function resolveBuiltinParameter(
		ReflectionNamedType $refType,
		string $refParamTypeName,
	):mixed {
		if($refType->allowsNull()) {
			return null;
		}

		throw new ServiceNotFoundException($refParamTypeName);
	}

	/** @param class-string $refParamTypeName */
	private function resolveServiceParameter(
		ReflectionNamedType $refType,
		string $refParamTypeName,
	):mixed {
		try {
			return $this->container->get($refParamTypeName);
		}
		catch(ServiceNotFoundException $exception) {
			if($refType->allowsNull()) {
				return null;
			}

			throw $exception;
		}
	}
}
