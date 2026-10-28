<?php

namespace Touhidurabir\MultiKyeRouteBinding;

use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Allow Extend Model Route Binding via multipel key
|--------------------------------------------------------------------------
| This trait allow extending the laravel route model binding by allowing
| multiple keys/columns at the runtime . Thus allow the reduce the code
| to be writter to handle model resolveing based on multiple keys/columns.
|
| How to use it :
| Apply the Trait in model and set a public/protected property named
| $routeBindingKeys that is an array what contains a keys/columns to
| type mapping
|
| Example :
| use Touhidurabir\MultiKyeRouteBinding\HasMultipleRouteBindingKeys;
|
| use HasMultipleRouteBindingKeys;
|
| public $routeBindingKeys = [
|      'column1',
|      'column2',
| ];
|
| The route key is 'id' by default. Set $useModelRouteKeyName = true to use
| the model's own route key name (its primary key) instead.
|
*/
trait HasMultipleRouteBindingKeys
{
    /**
     * Map the extra route bindings key if define in model class
     *
     * @return array
     */
    protected function getBindingKeys()
    {

        return property_exists($this, 'routeBindingKeys') && is_array($this->routeBindingKeys)
               ? $this->routeBindingKeys
               : [];
    }


    /**
     * Get the route key for the model.
     *
     * Returns 'id' by default. Set the model property $useModelRouteKeyName
     * to true to use the model's own route key name instead (its primary key,
     * unless a parent class customises it).
     *
     * @return string
     */
    public function getRouteKeyName()
    {

        if (property_exists($this, 'useModelRouteKeyName') && $this->useModelRouteKeyName === true) {

            return parent::getRouteKeyName();
        }

        return 'id';
    }


    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed        $value
     * @param  string|null  $field
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {

        if ($field) {

            return $this->newQuery()->where($field, $value)->first();
        }

        return $this->resolveRouteBindingQuery($this, $value)->first();
    }


    /**
     * Retrieve the model for a bound value, including soft deleted models.
     *
     * @param  mixed        $value
     * @param  string|null  $field
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {

        if ($field) {

            return $this->newQuery()->where($field, $value)->withTrashed()->first();
        }

        return $this->resolveRouteBindingQuery($this, $value)->withTrashed()->first();
    }


    /**
     * Get the query for retrieving the model for a bound value.
     *
     * Laravel uses this for every route model binding path, including
     * withTrashed() routes and scoped child bindings, so the extra binding
     * keys are applied here. They are only used when no explicit field is
     * given, or when the given field is the route key itself (which is what
     * Laravel passes for a scoped child binding without a custom key).
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Contracts\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed        $value
     * @param  string|null  $field
     *
     * @return \Illuminate\Contracts\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {

        $routeKeyName = $this->getRouteKeyName();

        if ($field && ! in_array($field, [$routeKeyName, $this->qualifyColumn($routeKeyName)], true)) {

            return $query->where($field, $value);
        }

        $bindingKeys = $this->getBindingKeys();

        $modelTableName = $this->getTable();

        $schema = Schema::connection($this->getConnectionName());

        // group the ORs, so they can not escape the constraints already on the query
        return $query->where(function ($query) use ($value, $routeKeyName, $bindingKeys, $modelTableName, $schema) {

            $query->where($this->qualifyColumn($routeKeyName), $value);

            foreach ($bindingKeys as $bindingKey) {

                if ($schema->hasColumn($modelTableName, $bindingKey)) {
                    $query->orWhere($this->qualifyColumn($bindingKey), $value);
                }
            }
        });
    }
}
