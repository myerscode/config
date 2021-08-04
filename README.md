#  Config
> A PHP utility that creates a configuration object from multi file, cross-referencing php array maps or yaml files

[![Latest Stable Version](https://poser.pugx.org/myerscode/config/v/stable)](https://packagist.org/packages/myerscode/config)
[![Total Downloads](https://poser.pugx.org/myerscode/config/downloads)](https://packagist.org/packages/myerscode/config)
[![License](https://poser.pugx.org/myerscode/config/license)](https://packagist.org/packages/myerscode/config)
![Tests](https://github.com/myerscode/config/workflows/Tests/badge.svg?branch=main)

## Why this package is helpful?

This package will allow you to build a config object, that you can simply retrieve values from. You manage configuration 
across multiple PHP or YAML files, with the ability to cross-reference properties in order to build up complex values.

## Install

You can install this package via composer:

``` bash
composer require myerscode/config
```

## Usage


### Creating store
The config data store is static, so all you need to do is start loading values into it
```php
(new Config())->loadFiles([
'app.config.php',
'db.config.yaml',
]);

(new Config())->loadFile('cache.config.php');
```

### Retrieving a value
```php
// class instance
$config = (new Config())->value('app.name');

// helper function
$config = config('app.name');
```

### Get all store value
```php
// class instance
$config = (new Config())->values();

// helper function
$config = config();
```

### Accessing the store
As the store is static, accessing the values can be done simply by calling the `store` helper or using the helper without a key.
```php
// new instance
$config = (new Config())->store();
// static builder
$config = Config::store();
```

## Issues and Contributing

We are very happy to receive pull requests to add functionality or fixes.

Bug reports and feature requests can be submitted on the [Github Issue Tracker](https://github.com/myerscode/config/issues).

Please read the Myerscode [contributing](https://github.com/myerscode/docs/blob/main/CONTRIBUTING.md) guide for information on our Code of Conduct.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
