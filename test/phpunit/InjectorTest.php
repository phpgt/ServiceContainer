<?php
namespace GT\ServiceContainer\Test;

use DateTime;
use GT\ServiceContainer\Container;
use GT\ServiceContainer\Injector;
use GT\ServiceContainer\ServiceNotFoundException;
use GT\ServiceContainer\Test\Example\Greeter;
use GT\ServiceContainer\Test\Example\PageWithMissingService;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;

class InjectorTest extends TestCase {
	public function testInvoke_missingServicePreservesExceptionAndAddsFunctionContext():void {
		$function = function(Greeter $greeter):void {
			self::fail("The callback must not run without its dependency.");
		};
		$declaration = new ReflectionFunction($function);
		$previous = new RuntimeException("Original cause");
		$original = new ServiceNotFoundException(Greeter::class, 123, $previous);
		$trace = $original->getTrace();
		$container = self::createMock(Container::class);
		$container->expects(self::once())->method("get")
			->with(Greeter::class)->willThrowException($original);
		$sut = new Injector($container);

		try {
			$sut->invoke(null, $function);
		}
		catch(ServiceNotFoundException $exception) {
			self::assertSame($original, $exception);
			self::assertSame($trace, $exception->getTrace());
			self::assertSame(123, $exception->getCode());
			self::assertSame($previous, $exception->getPrevious());
			self::assertStringContainsString('$greeter', $exception->getMessage());
			self::assertStringContainsString($declaration->getName() . "()", $exception->getMessage());
			self::assertStringContainsString(
				$declaration->getFileName() . ":" . $declaration->getStartLine(),
				$exception->getMessage(),
			);
			return;
		}

		self::fail("Expected the missing service exception.");
	}

	public function testInvoke_missingLoaderDependencyIncludesBothDeclarations():void {
		$loader = function(DateTime $clock):Greeter {
			self::fail("The loader must not run without its dependency.");
		};
		$container = new Container();
		$container->setLoader(Greeter::class, $loader);
		$sut = new Injector($container);
		$page = new PageWithMissingService();
		$loaderDeclaration = new ReflectionFunction($loader);
		$pageDeclaration = new ReflectionMethod($page, "go");

		try {
			$sut->invoke($page, "go");
		}
		catch(ServiceNotFoundException $exception) {
			$message = $exception->getMessage();
			self::assertStringContainsString('"' . DateTime::class . '"', $message);
			self::assertStringContainsString('$clock', $message);
			self::assertStringContainsString('$greeter', $message);
			self::assertStringContainsString(PageWithMissingService::class . "::go", $message);
			foreach([$loaderDeclaration, $pageDeclaration] as $declaration) {
				self::assertStringContainsString(
					$declaration->getFileName() . ":" . $declaration->getStartLine(),
					$message,
				);
			}
			return;
		}

		self::fail("Expected the missing loader dependency exception.");
	}

	public function testInvoke_missingServiceIdentifiesApplicationDeclaration():void {
		$page = new PageWithMissingService();
		$declaration = new ReflectionMethod($page, "go");
		$sut = new Injector(new Container());

		try {
			$sut->invoke($page, "go");
		}
		catch(ServiceNotFoundException $exception) {
			$message = $exception->getMessage();
			self::assertStringContainsString(Greeter::class, $message);
			self::assertStringContainsString(PageWithMissingService::class . "::go", $message);
			self::assertStringContainsString('$greeter', $message);
			self::assertStringContainsString(
				$declaration->getFileName() . ":" . $declaration->getStartLine(),
				$message,
			);
			return;
		}

		self::fail("Expected the missing service to prevent callback invocation.");
	}

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
