<?php
namespace Gt\ServiceContainer\Test;

use Gt\ServiceContainer\Container;
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
}
