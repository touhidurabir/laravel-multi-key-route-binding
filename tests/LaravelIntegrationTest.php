<?php

namespace Touhidurabir\MultiKyeRouteBinding\Tests;

use PHPUnit\Framework\Attributes\Test;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Touhidurabir\MultiKyeRouteBinding\Facades\MultiKyeRouteBinding;
use Touhidurabir\MultiKyeRouteBinding\MultiKyeRouteBindingServiceProvider;
use Touhidurabir\MultiKyeRouteBinding\Tests\App\User;
use Touhidurabir\MultiKyeRouteBinding\Tests\App\Profile;
use Touhidurabir\MultiKyeRouteBinding\Tests\App\Account;
use Touhidurabir\MultiKyeRouteBinding\Tests\App\Member;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 *  TO-DO: Need better testing.
 *  Factories, Mocks, etc, but this does the job for now.
 */
class LaravelIntegrationTest extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {

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
    protected function getPackageAliases($app)
    {

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
    protected function defineEnvironment($app)
    {

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // a second connection, used by models that are not on the default one
        $app['config']->set('database.connections.secondary', [
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
    protected function defineDatabaseMigrations()
    {

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
    protected function defineRoutes($router)
    {

        Route::get('/users/{user}', function (User $user) {

            if ($user) {

                return response()->json([
                    'id' => $user->id,
                    'message' => 'resource found'
                ], 200);
            }

            return response()->json([
                'message' => 'resource not found'
            ], 404);

        })->middleware(SubstituteBindings::class);

        Route::get('/trashed-users/{user}', function (User $user) {

            return response()->json(['id' => $user->id], 200);

        })->middleware(SubstituteBindings::class)->withTrashed();

        Route::get('/trashed-users/by-id/{user:id}', function (User $user) {

            return response()->json(['id' => $user->id], 200);

        })->middleware(SubstituteBindings::class)->withTrashed();

        Route::get('/users/{user}/profiles/{profile}', function (User $user, Profile $profile) {

            return response()->json(['id' => $profile->id], 200);

        })->middleware(SubstituteBindings::class)->scopeBindings();

        Route::get('/accounts/{account}', function (Account $account) {

            return response()->json(['code' => $account->code], 200);

        })->middleware(SubstituteBindings::class);

        Route::get('/members/{member}', function (Member $member) {

            return response()->json(['id' => $member->id], 200);

        })->middleware(SubstituteBindings::class);

        Route::get('/users/by-email/{user:email}', function (User $user) {

            return response()->json([
                'id' => $user->id,
                'message' => 'resource found'
            ], 200);

        })->middleware(SubstituteBindings::class);
    }


    /**
     * @test
     */
    #[Test]
    public function the_test_route_return_200_if_model_resource_found_using_default_key_binding()
    {

        $user = User::create([
            'email'    => uniqid() . '@localhost.com',
            'password' => bcrypt('password'),
        ]);

        $this->get("/users/{$user->id}")->assertStatus(200);
    }


    /**
     * @test
     */
    #[Test]
    public function the_test_route_return_404_if_model_resource_not_found_using_default_key_binding()
    {

        $user = User::create([
            'email'    => uniqid() . '@localhost.com',
            'password' => bcrypt('password'),
        ]);

        $this->get("/users/10001")->assertStatus(404);
    }


    /**
     * @test
     */
    #[Test]
    public function it_find_model_resource_on_keybind()
    {

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
    #[Test]
    public function it_can_not_find_model_resource_on_keybind_if_no_match_found()
    {

        $user1 = User::create([
            'email'    => uniqid() . '@localhost.com',
            'password' => bcrypt('password'),
        ]);

        $this->get("/users/someemail@email.com")->assertStatus(404);
        $this->get("/users/someusername")->assertStatus(404);
    }


    /**
     * @test
     */
    #[Test]
    public function it_only_use_the_explicit_field_when_route_defines_custom_binding_key()
    {

        $user = User::create([
            'email'    => uniqid() . '@localhost.com',
            'username' => uniqid() . '_username',
            'password' => bcrypt('password'),
        ]);

        $this->get("/users/by-email/{$user->email}")->assertStatus(200)->assertJson(['id' => $user->id]);
        $this->get("/users/by-email/{$user->username}")->assertStatus(404);
    }


    /**
     * @test
     */
    #[Test]
    public function it_find_soft_deleted_model_resource_on_keybind_for_with_trashed_routes()
    {

        $user = User::create([
            'email'    => uniqid() . '@localhost.com',
            'username' => uniqid() . '_username',
            'password' => bcrypt('password'),
        ]);

        $user->delete();

        $this->get("/users/{$user->email}")->assertStatus(404);
        $this->get("/trashed-users/{$user->id}")->assertStatus(200)->assertJson(['id' => $user->id]);
        $this->get("/trashed-users/{$user->email}")->assertStatus(200)->assertJson(['id' => $user->id]);
        $this->get("/trashed-users/{$user->username}")->assertStatus(200)->assertJson(['id' => $user->id]);
        $this->get("/trashed-users/someusername")->assertStatus(404);

        // an explicit binding field only uses that field
        $this->get("/trashed-users/by-id/{$user->id}")->assertStatus(200)->assertJson(['id' => $user->id]);
        $this->get("/trashed-users/by-id/{$user->email}")->assertStatus(404);
    }


    /**
     * @test
     */
    #[Test]
    public function it_find_scoped_child_model_resource_on_keybind()
    {

        $user1 = User::create(['email' => uniqid() . '@localhost.com', 'password' => bcrypt('password')]);
        $user2 = User::create(['email' => uniqid() . '@localhost.com', 'password' => bcrypt('password')]);

        $profile1 = $user1->profiles()->create(['employee_number' => 'EMP-1', 'first_name' => 'First', 'last_name' => 'Last']);
        $profile2 = $user2->profiles()->create(['employee_number' => 'EMP-2', 'first_name' => 'First', 'last_name' => 'Last']);

        $this->get("/users/{$user1->id}/profiles/{$profile1->id}")->assertStatus(200)->assertJson(['id' => $profile1->id]);
        $this->get("/users/{$user1->email}/profiles/EMP-1")->assertStatus(200)->assertJson(['id' => $profile1->id]);

        // the multi key lookup must stay scoped to the parent
        $this->get("/users/{$user1->id}/profiles/EMP-2")->assertStatus(404);
        $this->get("/users/{$user1->id}/profiles/{$profile2->id}")->assertStatus(404);
    }


    /**
     * @test
     */
    #[Test]
    public function the_route_key_name_is_id_by_default()
    {

        $this->assertSame('id', (new User())->getRouteKeyName());
        $this->assertSame('id', (new Profile())->getRouteKeyName());
    }


    /**
     * @test
     */
    #[Test]
    public function it_can_opt_in_to_use_the_model_route_key_name()
    {

        $account = Account::create(['code' => 'ACC-1', 'email' => 'account@localhost.com']);

        $this->assertSame('code', $account->getRouteKeyName());

        $this->get("/accounts/ACC-1")->assertStatus(200)->assertJson(['code' => 'ACC-1']);
        $this->get("/accounts/account@localhost.com")->assertStatus(200)->assertJson(['code' => 'ACC-1']);
        $this->get("/accounts/ACC-2")->assertStatus(404);
    }


    /**
     * @test
     */
    #[Test]
    public function it_checks_the_binding_key_columns_on_the_model_connection()
    {

        // the members table only exists on the model's (non default) connection
        Schema::connection('secondary')->create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->timestamps();
        });

        $member = Member::create(['email' => 'member@localhost.com']);

        $this->get("/members/{$member->id}")->assertStatus(200)->assertJson(['id' => $member->id]);
        $this->get("/members/member@localhost.com")->assertStatus(200)->assertJson(['id' => $member->id]);
    }

}
