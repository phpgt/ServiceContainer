<?php
namespace Gt\ServiceContainer\Test\Example;

class UriGreeter {
	public function __construct(private Greeter $greeter) {

	}

	public function greet(string $uri):string {
		return $this->greeter->greet("you are on page $uri");
	}
}
