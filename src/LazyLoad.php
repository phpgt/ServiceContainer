<?php
namespace Gt\ServiceContainer;

use Attribute;

#[Attribute]
class LazyLoad {
	public function __construct(private ?string $className = null) {}

	public function getClassName():?string {
		return $this->className;
	}
}
