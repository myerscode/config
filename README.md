#  Config
> A PHP utility that creates a configuration object from multi file, cross-referencing php array maps or yaml files

[![Latest Stable Version](https://poser.pugx.org/myerscode/config/v/stable)](https://packagist.org/packages/myerscode/config)
[![Total Downloads](https://poser.pugx.org/myerscode/config/downloads)](https://packagist.org/packages/myerscode/config)
[![License](https://poser.pugx.org/myerscode/config/license)](https://packagist.org/packages/myerscode/config)
![Tests](https://github.com/myerscode/config/workflows/Tests/badge.svg?branch=main)
[![codecov](https://codecov.io/gh/myerscode/config/graph/badge.svg)](https://codecov.io/gh/myerscode/config)

## Why this package is helpful?

This package will allow you to build a config object, that you can simply retrieve values from. You manage configuration 
across multiple PHP or YAML files, with the ability to cross-reference properties in order to build up complex values.

## Install

You can install this package via composer:

``` bash
composer require myerscode/config
```

## Usage

### Creating a Config Store
To get started ll you need to do is you need to create a `Config` instance and start loading data into files into it.

```php
$config = new Config();

$config->loadFiles([
'config/app.php',
'config/db.yaml',
]);

$config->loadFile('config/cache.php');

$config->loadData(['api_key' => 'abc123']);

// example config object
[
    'name' => 'myerscode',
    'db_name' => 'myerscode_db',
    'api_key' => 'abc123',
]
```

### Namespaced Configuration
By default, config from files will be merged recursivly when loaded in. If you want to 
give each file a top level namespace call the `loadFilesWithNamespace` and `loadFileWithNamespace` methods to have each
file be loaded into a namespace using their filename
```php
$config = new Config();

$config->loadFilesWithNamespace([
'config/app.php',
'config/db.yaml',
]);

$config->loadFileWithNamespace('config/cache.php');

// example config object
[
    'app' => [...],
    'db' => [...],
    'cache' => [...],
]
```


### Retrieving a value
Retrieve a single value from the store by using the `value` method and passing in a key.

Using `dot notation` you can access deep values of a config element, or retrieve the entire
object by calling its top level namespace.

```php
$config->value('app.name');

$config->value('app');

$config->value('api_key');
```

### Get all store value
Get all the values from the store as an array using the `values` method.
```php
$config->values();
```

### Accessing the store
Accessing the values directly is done by calling the `store` method.
```php
$store = $config->store();
```

## Config Syntax
A basic config file, is a PHP file that will just return an array or a YAML file.

```php
// app.config.php
return [
    'name' => 'Fred Myerscough',
    'settings' => [
        'a',
        'b',
        'c'
    ],
];
```

### Cross Referencing Values

```php 
// app.config.php
return [
    'name' => 'myerscode',
    'env' => 'myerscode',
];

// db.config.php
return [
    'db' => [
        'setting' => [
            'name' => '${env}_${name}_db',
        ]
    ],
    'db_name' => '${db.config.name}'
];
```

## Issues and Contributing

We are very happy to receive pull requests to add functionality or fixes.

Bug reports and feature requests can be submitted on the [Github Issue Tracker](https://github.com/myerscode/config/issues).

Please read the Myerscode [contributing](https://github.com/myerscode/docs/blob/main/CONTRIBUTING.md) guide for information on our Code of Conduct.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
