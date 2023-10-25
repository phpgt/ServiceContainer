<?php
namespace Gt\ServiceContainer\Test\Example;

class Greeter implements GreetingInterface {
	public function greet(string $name = "you"):string {
		return "Hello, $name!";
	}
}
