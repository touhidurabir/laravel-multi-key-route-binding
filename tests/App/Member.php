<?php

namespace Touhidurabir\MultiKyeRouteBinding\Tests\App;

use Illuminate\Database\Eloquent\Model;
use Touhidurabir\MultiKyeRouteBinding\HasMultipleRouteBindingKeys;

class Member extends Model
{
    use HasMultipleRouteBindingKeys;

    /**
     * The connection name for the model (not the default connection).
     *
     * @var string
     */
    protected $connection = 'secondary';


    /**
     * The model associated table
     *
     * @var string
     */
    protected $table = 'members';


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
        'email',
    ];
}
