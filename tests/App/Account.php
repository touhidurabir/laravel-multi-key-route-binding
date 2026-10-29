<?php

namespace Touhidurabir\MultiKyeRouteBinding\Tests\App;

use Illuminate\Database\Eloquent\Model;
use Touhidurabir\MultiKyeRouteBinding\HasMultipleRouteBindingKeys;

class Account extends Model
{
    use HasMultipleRouteBindingKeys;

    /**
     * The model associated table
     *
     * @var string
     */
    protected $table = 'accounts';


    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';


    /**
     * The "type" of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';


    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;


    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * Use the model's own route key name (its primary key) instead of 'id'
     *
     * @var bool
     */
    protected $useModelRouteKeyName = true;


    /**
     * The attributes that will be used for multiple key binding on route models
     *
     * @var array
     */
    protected $routeBindingKeys = [
        'email',
    ];
}
