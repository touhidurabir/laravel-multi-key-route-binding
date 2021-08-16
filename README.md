# Laravel Model UUID

A simple package to handle the multiple key/column based route model binding for laravel package

## Installation

Require the package using composer:

```bash
composer require touhidurabir/laravel-multi-key-route-binding
```


## Usage

Use the trait **HasMultipleRouteBindingKeys** in model where uuid needed to attach

```php
use Touhidurabir\MultiKyeRouteBinding\HasMultipleRouteBindingKeys;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    
    use HasMultipleRouteBindingKeys;
}
```

And then add the protected property **$routeBindingKeys** which will contails the list of other route binding keys. 

```php
/**
 * The attributes that will be used for multiple key binding on route models
 *
 * @var array
 */
protected $routeBindingKeys = [
    'email',
    'username'
];
```

By default column **id** is considered as the primary key for route model binding . But it is always possible to override/customize the primary key for route binding [as per laravel doc](https://laravel.com/docs/8.x/routing#customizing-the-default-key-name)

```php
/**
 * Get the route key for the model.
 *
 * @return string
 */
public function getRouteKeyName()
{
    return 'slug';
}
```




> The package also provide a bit of safe guard by checking if the model table has the given binding key column . 
>
> If the binding key column not found for model table schema, it will skip that binding key.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](./LICENSE.md)
