<?php
namespace Gt\ServiceContainer\Test;

use DateTime;
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
		$dateTime = new DateTime("1988-04-05 17:24");

		$container = self::createMock(Container::class);
		$container->method("get")
			->with(Greeter::class)
			->willReturn($greeter);
		$sut = new Injector($container);

		$exampleClass = new class {
			/** @noinspection PhpUnused */
			public function helloHtml(
				Greeter $greeter,
				DateTime $dateOfBirth,
			):string {
				return "<p>" . $greeter->greet() . " You were born on a " . $dateOfBirth->format("l") . ".</p>";
			}
		};

		$return = $sut->invoke(new $exampleClass(), "helloHtml", [
			DateTime::class => $dateTime,
		]);

		self::assertSame("<p>Hello, you! You were born on a Tuesday.</p>", $return);
	}

	public function testInvoke_noClass():void {
		$invocationList = [];

		$greeter = self::createMock(Greeter::class);
		$container = self::createMock(Container::class);
		$container->method("get")
			->with(Greeter::class)
			->willReturn($greeter);
		$function = function(Greeter $greeterThing) use(&$invocationList):void {
			array_push($invocationList, $greeterThing);
		};

		$sut = new Injector($container);
		$sut->invoke(null, $function);
		self::assertCount(1, $invocationList);
		self::assertSame($greeter, $invocationList[0]);
	}
}
