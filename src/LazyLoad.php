<?php
namespace Gt\ServiceContainer;

use Attribute;

#[Attribute]
class LazyLoad {
	public function __construct(private string $className) {}

	public function getClassName():string {
		return $this->className;
	}
}
