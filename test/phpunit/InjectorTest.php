<?php
namespace GT\ServiceContainer\Test;

use DateTime;
use GT\ServiceContainer\Container;
use GT\ServiceContainer\Injector;
use GT\ServiceContainer\ServiceNotFoundException;
use GT\ServiceContainer\Test\Example\Greeter;
use PHPUnit\Framework\TestCase;

class InjectorTest extends TestCase {
	public function testInvoke():void {
		$greeter = self::createStub(Greeter::class);
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

		$greeter = self::createStub(Greeter::class);
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

	public function testInvoke_usesDefaultScalarParameter():void {
		$function = function(string $name = "World"):string {
			return "Hello, $name!";
		};

		$sut = new Injector(new Container());
		self::assertSame("Hello, World!", $sut->invoke(null, $function));
	}

	public function testInvoke_usesExtraArgByParameterName():void {
		$function = function(string $name = "World"):string {
			return "Hello, $name!";
		};

		$sut = new Injector(new Container());
		self::assertSame(
			"Hello, Cron!",
			$sut->invoke(null, $function, ["name" => "Cron"])
		);
	}

	public function testInvoke_usesExtraArgByParameterType():void {
		$dateTime = new DateTime("2026-06-22");
		$function = function(DateTime $dateTime):string {
			return $dateTime->format("Y-m-d");
		};

		$sut = new Injector(new Container());
		self::assertSame(
			"2026-06-22",
			$sut->invoke(null, $function, [DateTime::class => $dateTime])
		);
	}

	public function testInvoke_usesExtraArgForUntypedParameter():void {
		$function = function($name = "World"):string {
			return "Hello, $name!";
		};

		$sut = new Injector(new Container());
		self::assertSame(
			"Hello, Cron!",
			$sut->invoke(null, $function, ["name" => "Cron"])
		);
	}

	public function testInvoke_usesDefaultUntypedParameter():void {
		$function = function($name = "World"):string {
			return "Hello, $name!";
		};

		$sut = new Injector(new Container());
		self::assertSame("Hello, World!", $sut->invoke(null, $function));
	}

	public function testInvoke_setsMissingUntypedParameterToNull():void {
		$function = function($name):mixed {
			return $name;
		};

		$sut = new Injector(new Container());
		self::assertNull($sut->invoke(null, $function));
	}

	public function testInvoke_setsMissingNullableBuiltinParameterToNull():void {
		$function = function(?string $name):mixed {
			return $name;
		};

		$sut = new Injector(new Container());
		self::assertNull($sut->invoke(null, $function));
	}

	public function testInvoke_throwsWhenRequiredBuiltinParameterIsMissing():void {
		$function = function(string $name):string {
			return $name;
		};

		$sut = new Injector(new Container());

		self::expectException(ServiceNotFoundException::class);
		$sut->invoke(null, $function);
	}

	public function testInvoke_setsMissingNullableServiceParameterToNull():void {
		$function = function(?Greeter $greeter):mixed {
			return $greeter;
		};

		$sut = new Injector(new Container());
		self::assertNull($sut->invoke(null, $function));
	}

	public function testInvoke_throwsWhenRequiredServiceParameterIsMissing():void {
		$function = function(Greeter $greeter):Greeter {
			return $greeter;
		};

		$sut = new Injector(new Container());

		self::expectException(ServiceNotFoundException::class);
		$sut->invoke(null, $function);
	}
}
