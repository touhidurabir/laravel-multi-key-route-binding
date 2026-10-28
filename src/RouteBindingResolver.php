<?php

namespace Touhidurabir\MultiKyeRouteBinding;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;

class RouteBindingResolver
{
    /**
     * Find a model the same way route model binding does, using its extra binding keys
     *
     * @param  string|\Illuminate\Database\Eloquent\Model $model    model class name or instance
     * @param  mixed                                       $value
     * @param  string|null                                 $field    an explicit column, like {user:email}
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolve($model, $value, $field = null)
    {

        return $this->resolveModel($model)->resolveRouteBinding($value, $field);
    }


    /**
     * Resolve the model instance and make sure it uses the HasMultipleRouteBindingKeys trait
     *
     * @param  string|\Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \InvalidArgumentException
     */
    protected function resolveModel($model)
    {

        if (is_string($model) && is_subclass_of($model, Model::class)) {

            $model = new $model();
        }

        if (! $model instanceof Model || ! in_array(HasMultipleRouteBindingKeys::class, class_uses_recursive($model))) {

            throw new InvalidArgumentException(
                'The given model must be an Eloquent model class or instance using the '
                    . HasMultipleRouteBindingKeys::class . ' trait.'
            );
        }

        return $model;
    }
}
