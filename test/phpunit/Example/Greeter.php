<?php
namespace Gt\ServiceContainer\Test\Example;

class Greeter implements GreetingInterface {
	public function greet(string $name):string {
		return "Hello, $name!";
	}
}
