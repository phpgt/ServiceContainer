<?php
namespace Gt\ServiceContainer\Test;

use DateTime;
use DateTimeInterface;
use Gt\ServiceContainer\Container;
use Gt\ServiceContainer\LazyLoad;
use Gt\ServiceContainer\ServiceContainerException;
use Gt\ServiceContainer\ServiceNotFoundException;
use Gt\ServiceContainer\Test\Example\Greeter;
use Gt\ServiceContainer\Test\Example\GreetingInterface;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase {
	public function testSetGet():void {
		$greeter = new Greeter();
		$sut = new Container();
		$sut->set($greeter);
		self::assertSame($greeter, $sut->get(Greeter::class));
	}

	public function testGet_unknown():void {
		$sut = new Container();
		self::expectException(ServiceNotFoundException::class);
		$sut->get(Greeter::class);
	}

	public function testGet_interface():void {
		$greeter = new Greeter();
		$sut = new Container();
		$sut->set($greeter);
		self::assertSame($greeter, $sut->get(GreetingInterface::class));
	}

	public function testSet_string():void {
		$string = "Test String!";
		$sut = new Container();
		self::expectException(ServiceContainerException::class);
		self::expectExceptionMessage("Values within the ServiceContainer must be objects, but a string was supplied with value 'Test String!'");
		$sut->set($string);
	}

	public function testSet_null():void {
		$sut = new Container();
		self::expectException(ServiceContainerException::class);
		self::expectExceptionMessage("Values within the ServiceContainer must be objects, but a NULL was supplied");
		$sut->set(null);
	}

	public function testSetLoader():void {
		$greeterCallback = function():GreetingInterface {
			return new Greeter();
		};

		$sut = new Container();
		$sut->setLoader(Greeter::class, $greeterCallback);

		$greeter = $sut->get(Greeter::class);
		self::assertInstanceOf(GreetingInterface::class, $greeter);
	}

	public function testSetLoader_oneInstance():void {
		$greeterCallback = function():GreetingInterface {
			return new Greeter();
		};

		$sut = new Container();
		$sut->setLoader(Greeter::class, $greeterCallback);

		$greeter1 = $sut->get(Greeter::class);
		$greeter2 = $sut->get(Greeter::class);

		self::assertSame($greeter1, $greeter2);
	}

	public function testSetLoaderClass():void {
		$loaderClass = new class {
			#[LazyLoad(Greeter::class)]
			public function doTheGreetThing():GreetingInterface {
				return new Greeter();
			}
		};

		$sut = new Container();
		$sut->addLoaderClass($loaderClass);

		$greeter = $sut->get(Greeter::class);
		self::assertInstanceOf(Greeter::class, $greeter);
	}

	public function testSetLoaderClass_multipleClasses():void {
		$loaderClass = new class {
			#[LazyLoad(DateTime::class)]
			public function getSomeSortOfDate():DateTimeInterface {
				return new DateTime();
			}

			#[LazyLoad(Greeter::class)]
			public function doTheGreetThing():GreetingInterface {
				return new Greeter();
			}
		};

		$sut = new Container();
		$sut->addLoaderClass($loaderClass);

		$greeter = $sut->get(Greeter::class);
		$dateTime = $sut->get(DateTimeInterface::class);
		self::assertInstanceOf(Greeter::class, $greeter);
		self::assertInstanceOf(DateTime::class, $dateTime);
	}

	public function testSetLoader_nullable():void {
		$sut = new Container();
		$callback = function():?Greeter {
			return null;
		};
		$sut->setLoader(Greeter::class, $callback);

		$greeterOrNull = $sut->get(Greeter::class);
		self::assertNull($greeterOrNull);
	}

	public function testSetLoaderClass_nullable():void {
		$loaderClass = new class {
			#[LazyLoad(Greeter::class)]
			public function getGreeterOrNull():?Greeter {
				return null;
			}
		};
		$sut = new Container();
		$sut->addLoaderClass($loaderClass);

		$greeterOrNull = $sut->get(Greeter::class);
		self::assertNull($greeterOrNull);
	}

	/**
	 * This is the webengine-style loader, where the name of the class
	 * is obtained from the go() function parameters.
	 */
	public function testSetLoaderClass_nullable_loadFromAnotherClass():void {
		$loaderClass = new class {
			#[LazyLoad(Greeter::class)]
			public function getGreeterOrNull():?Greeter {
				return null;
			}
		};
		$sut = new Container();
		$sut->addLoaderClass($loaderClass);

		$exampleClass = new class {
			public function go(?Greeter $greeter):void {
				if($greeter) {
					echo $greeter->greet("Cody");
				}
			}
		};

		$typeNameArray = [];

		$refClass = new \ReflectionClass($exampleClass);
		foreach($refClass->getMethods() as $refMethod) {
			foreach($refMethod->getParameters() as $refParam) {
				$refType = $refParam->getType();
				array_push($typeNameArray, $refType->getName());
			}
		}

		$greeterOrNull = $sut->get($typeNameArray[0]);
		self::assertNull($greeterOrNull);
	}
}
