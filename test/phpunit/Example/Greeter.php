<?php
namespace Gt\ServiceContainer\Test\Example;

class Greeter {
	public function greet(string $name):string {
		return "Hello, $name!";
	}
}
