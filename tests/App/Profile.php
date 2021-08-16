<?php

namespace Touhidurabir\MultiKyeRouteBinding\Tests\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Touhidurabir\MultiKyeRouteBinding\HasMultipleRouteBindingKeys;

class Profile extends Model {

    use SoftDeletes;

    use HasMultipleRouteBindingKeys;

    /**
     * The model associated table
     *
     * @var string
     */
    protected $table = 'profiles';


    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The attributes that will be used for multiple key binding on route models
     *
     * @var array
     */
    protected $routeBindingKeys = [
        'employee_number'
    ];

}