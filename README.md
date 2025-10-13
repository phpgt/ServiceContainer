Centralised container of a project's core objects.
==================================================

When PHP applications have a lot of classes, it's important to use [dependency injection][dependency-injection] techniques to keep your code maintainable and testable; rather than constructing objects within the functions they are used, pass the objects into the function via parameters.

Within this repository, a `Container` is an object that can be assigned pre-constructed instances of objects, and an `Injector` is an object that can automatically invoke other objects' methods with the matching instances from the Container.

There are no rules to how this repository should be used. You may want to have a single Container accessible to a single layer of your code, or you may want to have multiple Containers within your application to provide a different pool of object instances to different areas of your code.

This repository is used within [WebEngine][webengine] to automatically invoke Page Logic functions, allowing the developer to have a single point within the application that is responsible for the object access of all first and third party classes.

***

<a href="https://github.com/PhpGt/ServiceContainer/actions" target="_blank">
	<img src="https://badge.status.php.gt/servicecontainer-build.svg" alt="Build status" />
</a>
<a href="https://app.codacy.com/gh/PhpGt/ServiceContainer" target="_blank">
	<img src="https://badge.status.php.gt/servicecontainer-quality.svg" alt="Code quality" />
</a>
<a href="https://app.codecov.io/gh/PhpGt/ServiceContainer" target="_blank">
	<img src="https://badge.status.php.gt/servicecontainer-coverage.svg" alt="Code coverage" />
</a>
<a href="https://packagist.org/packages/PhpGt/ServiceContainer" target="_blank">
	<img src="https://badge.status.php.gt/servicecontainer-version.svg" alt="Current version" />
</a>
<a href="http://www.php.gt/servicecontainer" target="_blank">
	<img src="https://badge.status.php.gt/servicecontainer-docs.svg" alt="PHP.G/ServiceContainer documentation" />
</a>

[dependency-injection]: https://martinfowler.com/articles/injection.html
[webengine]: https://www.php.gt/webengine

# Proudly sponsored by

[JetBrains Open Source sponsorship program](https://www.jetbrains.com/community/opensource/)

[![JetBrains logo.](https://resources.jetbrains.com/storage/products/company/brand/logos/jetbrains.svg)](https://www.jetbrains.com/community/opensource/)
