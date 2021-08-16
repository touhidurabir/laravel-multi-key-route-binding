<?php

namespace Touhidurabir\ModelUuid\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use Touhidurabir\MultiKyeRouteBinding\Facades\MultiKyeRouteBinding;
use Touhidurabir\MultiKyeRouteBinding\MultiKyeRouteBindingServiceProvider;
use Touhidurabir\MultiKyeRouteBinding\Tests\App\User;
use Touhidurabir\MultiKyeRouteBinding\Tests\App\Profile;

/**
 *  TO-DO: Need better testing.
 *  Factories, Mocks, etc, but this does the job for now.
 */
class LaravelIntegrationTest extends TestCase {

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app) {

        return [
            MultiKyeRouteBindingServiceProvider::class,
        ];
    }   
    
    
    /**
     * Override application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app) {
        
        return [
            'MultiKyeRouteBinding' => MultiKyeRouteBinding::class,
        ];
    }


    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app) {

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app.url', 'http://localhost/');
        $app['config']->set('app.debug', false);
        $app['config']->set('app.key', env('APP_KEY', '1234567890123456'));
        $app['config']->set('app.cipher', 'AES-128-CBC');
    }


    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations() {

        $this->loadMigrationsFrom(__DIR__ . '/App/database/migrations');
        
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback', ['--database' => 'testbench'])->run();
        });
    }


    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     *
     * @return void
     */
    protected function defineRoutes($router) {
        
        Route::get('/users/{user}', function(User $user) {
            
            if ( $user ) {

                return response()->json([
                    'id' => $user->id,
                    'message' => 'resource found'
                ], 200);
            }

            return response()->json([
                'message' => 'resource not found'
            ], 404);

        })->middleware('bindings');;
    }


    /**
     * @test
     */
    public function the_test_route_return_200_if_model_resource_found_using_default_key_binding() {

        $user = User::create([
            'email'    => uniqid() . '@localhost.com',
            'password' => bcrypt('password'),
        ]);

        $this->get("/users/{$user->id}")->assertStatus(200);
    }


    /**
     * @test
     */
    public function the_test_route_return_404_if_model_resource_not_found_using_default_key_binding() {

        $user = User::create([
            'email'    => uniqid() . '@localhost.com',
            'password' => bcrypt('password'),
        ]);

        $this->get("/users/10001")->assertStatus(404);
    }


    /**
     * @test
     */
    public function it_find_model_resource_on_keybind() {

        $user1 = User::create([
            'email'    => uniqid() . '@localhost.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'email'    => uniqid() . '@localhost.com',
            'username' => uniqid() . '_username',
            'password' => bcrypt('password'),
        ]);

        $this->get("/users/{$user1->email}")->assertStatus(200);
        $this->get("/users/{$user2->username}")->assertStatus(200);
    }


    /**
     * @test
     */
    public function it_can_not_find_model_resource_on_keybind_if_no_match_found() {

        $user1 = User::create([
            'email'    => uniqid() . '@localhost.com',
            'password' => bcrypt('password'),
        ]);

        $this->get("/users/someemail@email.com")->assertStatus(404);
        $this->get("/users/someusername")->assertStatus(404);
    }
}