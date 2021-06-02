<?php
namespace Gt\ServiceContainer;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ServiceContainerException extends RuntimeException implements ContainerExceptionInterface {
}
