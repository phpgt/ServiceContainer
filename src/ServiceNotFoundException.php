<?php
namespace GT\ServiceContainer;

use Psr\Container\NotFoundExceptionInterface;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

class ServiceNotFoundException extends ServiceContainerException implements NotFoundExceptionInterface {
	public function __construct(
		string $className,
		int $code = 0,
		?Throwable $previous = null
	) {
		$message = "Service can not be located within Container: \"$className\" - docs: https://www.php.gt/servicecontainer/notfound";
		parent::__construct($message, $code, $previous);
	}

	public function addParameterContext(ReflectionParameter $parameter):void {
		$function = $parameter->getDeclaringFunction();
		$callable = $function->getName();
		if($function instanceof ReflectionMethod) {
			$callable = $function->getDeclaringClass()->getName() . "::$callable";
		}

		$this->message .= "\nRequested by parameter \$" . $parameter->getName()
			. " of $callable()";
		if($file = $function->getFileName()) {
			$this->message .= " declared in $file:" . $function->getStartLine();
		}
	}
}
