<?php
namespace GT\ServiceContainer;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ServiceContainerException extends RuntimeException implements ContainerExceptionInterface {
}
