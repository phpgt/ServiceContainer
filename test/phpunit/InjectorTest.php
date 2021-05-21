<?php
namespace Gt\ServiceContainer\Test;

use Gt\ServiceContainer\Container;
use Gt\ServiceContainer\Injector;
use Gt\ServiceContainer\Test\Example\Greeter;
use PHPUnit\Framework\TestCase;

class InjectorTest extends TestCase {
	public function testInvoke():void {
		$greeter = self::createMock(Greeter::class);
		$greeter->method("greet")
			->willReturnCallback(function(string $name) {
				return "Hello, $name!";
			});
		$container = self::createMock(Container::class);
		$container->method("get")
			->willReturn($greeter);
		$sut = new Injector($container);

		$exampleClass = new class {
			/** @noinspection PhpUnused */
			public function helloHtml(
				Greeter $greeter,
				string $name
			):string {
				return "<h1>" . $greeter->greet($name) . "</h1>";
			}
		};

		$return = $sut->invoke(new $exampleClass(), "helloHtml", [
			"name" => "PHPUnit",
		]);

		self::assertSame("<h1>Hello, PHPUnit!</h1>", $return);
	}
}
