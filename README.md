<p>
  <img src="https://matti.suoraniemi.com/storage/lyhty-prefixed-id.png" width="400">
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lyhty/prefixed-id.svg?label=&style=flat-square&logo=packagist&logoColor=white)](https://packagist.org/packages/lyhty/prefixed-id)
[![PHP](https://img.shields.io/packagist/php-v/lyhty/prefixed-id?style=flat-square&label=&logo=php&logoColor=white)](https://packagist.org/packages/lyhty/prefixed-id)
[![Laravel](https://img.shields.io/static/v1?label=&message=^7.0||^8.0||^9.0&color=red&style=flat-square&logo=laravel&logoColor=white)](https://packagist.org/packages/lyhty/prefixed-id)
[![Total Downloads](https://img.shields.io/packagist/dt/lyhty/prefixed-id.svg?style=flat-square)](https://packagist.org/packages/lyhty/prefixed-id)
[![License](https://img.shields.io/packagist/l/lyhty/prefixed-id.svg?style=flat-square)](https://packagist.org/packages/lyhty/prefixed-id)
<!-- [![Tests](https://img.shields.io/github/workflow/status/lyhty/prefixed-id/Run%20tests?style=flat-square)](https://github.com/lyhty/prefixed-id/actions/workflows/php.yml) -->
<!-- [![StyleCI](https://github.styleci.io/repos/523255216/shield)](https://github.styleci.io/repos/523255216) -->

<!-- CUTOFF -->

This package will provide models the ability to have a prefixed id in your system, while
still having the database value being just the usual id.

## Config

Run following command to publish the config file:

```bash
php artisan vendor:publish --provider="Lyhty\PrefixedId\PrefixedIdServiceProvider"
```

You should set up the models that you want to have ids with prefixes, you should also apply
HasPrefixedId trait to the model.

## Example

Model with a prefixed id would work the following way:

```php
use App\Models\Human;

$human = Human::pidFindOrFail("H-1");
$human->pid; // Prints "H-1"
$human->id; // Prints 1
```

## Route binding

The package automatically binds the models set up in the config file to use prefixes in routing.

### Model specific binding

A model with a prefixed id set up would behave in a following way: `project.test/humans/H-1`

**web.php**

```php
use Illuminate\Support\Facades\Route;

Route::get('humans/{human}', [HumanController::class, 'show']);
```

> You can apply an optional prefix to the binding in the config. E.g. `humans/{pid_human}` with `pid_` being
> the applied prefix. This would keep the Laravel's default binding to the model's key without the prefix.

**HumanController.php**

```php
public function show(Request $request, Human $human)
{
    // 
}
```

As you can see, setting up the model binding works the same as without the prefixed id.

### Generic model binding

The package also provides more generic routing. An example follows:

**web.php**

```php
use Illuminate\Support\Facades\Route;

Route::get('resources/{prefixedModel}', [ResourceController::class, 'show']);
```

> You can change the route binding name in the config.

**ResourceController.php**

```php
public function show(Request $request, Model $model)
{
    //
}
```

With a route setup like this, both `project.test/resources/H-1` and `project.test/resources/D-1` would work and 
would return a Human model instance and a Dog model instance respectively.

This way you can have one route for returning any type of a model, as long as it is
set up in config.

## Prefixed id helper class

You can utilize PrefixedId class to find instances in a following way:

```php
use Lyhty\PrefixedId\Facades\PrefixedId;

$human = PrefixedId::findModel('H-1');
$dog = PrefixedId::findModel('D-1');
```

The find methods would return instances of `App\Models\Human` and `App\Models\Dog` respectively.
