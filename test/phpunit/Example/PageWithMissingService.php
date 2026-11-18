<?php
namespace GT\ServiceContainer\Test\Example;

use LogicException;

class PageWithMissingService {
	public function go(Greeter $greeter):void {
		throw new LogicException("The callback must not run when its dependency is missing.");
	}
}
