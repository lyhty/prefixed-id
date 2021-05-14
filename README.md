# Prefixed IDs

This package will provide models the ability to have a prefixed id in your system, while
still having the database value being just the usual id.

## Config values

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

Model with a prefixed id set up would behave in a following way:
`project.test/humans/H-1`

web.php

```php
use Illuminate\Support\Facades\Route;

Route::get('humans/{human}', [HumanController::class, 'show']);
```

> You can apply an optional prefix to the binding in the config. E.g. `humans/{pid.human}`, 'pid.' being
> the applied prefix. This would keep the Laravel's default binding to the model's key without the prefix.

HumanController.php

```php
public function show(Request $request, Human $human)
{
    // 
}
```

As you can see, setting up the model binding works the same as without the prefixed id.

The package also provides more generic routing:
`project.test/resources/D-1`

web.php

```php
use Illuminate\Support\Facades\Route;

Route::get('resources/{prefixedModel}', [ResourceController::class, 'show']);
```

> You can change the route binding name in the config.

ResourceController.php

```php
public function show(Request $request, Model $model)
{
    $model->pid; // Prints "D-1"
}
```

This way, you can have one route for returning any type of a model, as long as it is
set up in config.

## Prefixed id helper class

You can utilize PrefixedId class to find instances in a following way:

```php
use SirMathays\PrefixedId\Facades\PrefixedId;

$human = PrefixedId::findModel('H-1');
$dog = PrefixedId::findModel('D-1');
```

The find methods would return instances of `App\Models\Human` and `App\Models\Dog` respectively.
What the helper class returns are based on the prefixes connected to the models in the config file.
