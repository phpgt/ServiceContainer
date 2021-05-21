<?php
namespace Gt\ServiceContainer;

use Throwable;

class ServiceNotFoundException extends ServiceContainerException {
	public function __construct(
		string $className,
		int $code = 0,
		Throwable $previous = null
	) {
		$message = "Service can not be located within Container: \"$className\" - docs: https://www.php.gt/servicecontainer/notfound";
		parent::__construct($message, $code, $previous);
	}
}
