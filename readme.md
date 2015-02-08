# Vault (Laravel 5 Package)

[![Project Status](http://stillmaintained.com/rappasoft/vault.png)](http://stillmaintained.com/rappasoft/vault)
[![Build Status](https://scrutinizer-ci.com/g/rappasoft/vault/badges/build.png?b=master)](https://scrutinizer-ci.com/g/rappasoft/vault/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rappasoft/vault/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rappasoft/vault/?branch=master)
[![Total Downloads](https://poser.pugx.org/rappasoft/vault/downloads.svg)](https://packagist.org/packages/rappasoft/vault) 
[![License](https://poser.pugx.org/rappasoft/vault/license.svg)](https://packagist.org/packages/rappasoft/vault)

Vault is a simple yet powerful access control system for the new Laravel 5 Framework. It comes with a backend user interface to manage users, roles, and permissions as well as the relationships between them.

**Examples:**
[Vault User Index](http://i.imgur.com/yZ80ySY.png)
[Vault Create Role](http://i.imgur.com/R4yE7nF.png)
[Vault Edit User](http://i.imgur.com/5ZIRcGV.png)
[Vault Role Index](http://i.imgur.com/zmfGeEr.png)

## Documentation

* [Setup](#setup)
    * [Publishing Views](#publish)
    * [UserHasRole trait](#userhasrole)
    * [Dummy Data](#seeding)
    * [Route Middleware](#middleware)
* [Configuration] (#configuration)
    * [Config File](#config_file)
    * [Status Property](#status_property)
    * [Route Middleware](#route_middleware)
        * [Parameters](#route_middleware_params)
        * [Creating Middleware](#creating_middleware)
        * [VaultRoute trait](#vault_route_trait)
    * [Blade Extensions](#blade_extensions)

## Prerequisites

- This package assumes you have an installation of Laravel 5 using the pre-packaged authentication library and functionality. For a brand new project, I recommend using my [Laravel 5 Boilerplate Package](https://github.com/rappasoft/laravel-5-boilerplate) and requiring this library.
- User model must have soft deletes enabled.

<a name="setup"/>
## Setup

In the `require` key of `composer.json` file add the following

    "rappasoft/vault": "dev-master"
    
Run the Composer update command

    $ composer update

In your `config/app.php` add the following to your `$providers` and `$aliases` array

```php
'providers' => [

    'App\Providers\EventServiceProvider',
    'App\Providers\RouteServiceProvider',
    ...
    'Rappasoft\Vault\VaultServiceProvider',
    'Illuminate\Html\HtmlServiceProvider',

],
```

```php
'aliases' => [

    'App'       => 'Illuminate\Support\Facades\App',
    ...
    'Form'		=> 'Illuminate\Html\FormFacade', 
    'HTML'		=> 'Illuminate\Html\HtmlFacade'
   
],
```

**The Vault Facade is loaded by the service provider by default.**

<a name="publish"/>
Run the `vendor:publish` command

    $ php artisan vendor:publish

This will publish the following files to your application:

- app/config/vault.php config file
- Vault Migration File
- Vault Seed File (Will add the seed call to the end of your DatabaseSeeder.php class)
- public/js/vault/*
- public/css/vault/*

You can also publish individual assets by tag if need be:

    $ php artisan vendor:publish --provider="Rappasoft\Vault\VaultServiceProvider" --tag="config"
    $ php artisan vendor:publish --provider="Rappasoft\Vault\VaultServiceProvider" --tag="migration"
    $ php artisan vendor:publish --provider="Rappasoft\Vault\VaultServiceProvider" --tag="seeder"
    $ php artisan vendor:publish --provider="Rappasoft\Vault\VaultServiceProvider" --tag="assets"
    
**You can also publish views, see configuration below.**

Run the `dumpautoload` command

    $ composer dumpautoload -o

Run the `migration` command

    $ php artisan migrate
    
<a name="userhasrole"/>
Add the `UserHasRole` trait to your User model:

```php
<?php namespace App;

...
use Illuminate\Database\Eloquent\SoftDeletes;
use Rappasoft\Vault\Traits\UserHasRole;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes, UserHasRole;
}
```

<a name="seeding"/>
Run the `seed` command

    $ php artisan db:seed --class="VaultTableSeeder"

<a name="middleware"/>
Add the `route middleware` to your app/Http/Kernel.php file:

```php
protected $routeMiddleware = [
    'auth' => 'App\Http\Middleware\Authenticate',
    'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
    'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
    ...
    'vault.routeNeedsRole' => 'Rappasoft\Vault\Http\Middleware\RouteNeedsRole',
    'vault.routeNeedsPermission' => 'Rappasoft\Vault\Http\Middleware\RouteNeedsPermission',
    'vault.routeNeedsRoleOrPermission' => 'Rappasoft\Vault\Http\Middleware\RouteNeedsRoleOrPermission',
];
```

###That's it! You should now be able to navigate to http://localhost/access/users to see the users index.
    
<a name="configuration"/>
## Configuration

<a name="config_file"/>
###Configuration File

```php
/*
* The company name used in the footer of the vault views.
*/
vault.general.company_name
/*
* Whether or not to load the vault views when the application loads.
* Useful if you want to copy the vault routes into your own routes file to modify.
*/
vault.general.use_vault_routes

/*
* The namespaced route to the vault role
*/
vault.role
/*
* The namespaced route to the vault permission
*/
vault.permission

/*
* Used by Vault to save roles to the database.
*/
vault.roles_table
/*
* Used by Vault to save permissions to the database.
*/
vault.permissions_table
/*
* Used by Vault to save relationship between permissions and roles to the database.
*/
vault.permission_role_table
/*
 * Used by Vault to save relationship between permissions and users to the database.
 * This table is only for permissions that belong directly to a specific user and not a role
 */
vault.permission_user_table
/*
* Used by Vault to save assigned roles to the database.
*/
vault.assigned_roles_table

/*
* Amount of users to show per page for pagination on users.index
*/
vault.users.default_per_page
/*
* The rules to validate the users password by when creating a new user
*/
vault.users.password_validation

/*
* Whether a role must contain a permission or can be used standalone (perhaps as a label)
*/
vault.roles.role_must_contain_permission
/*
 * Whether or not the administrator role must possess every permission
 * Works in unison with permissions.permission_must_contain_role
 */
vault.roles.administrator_forced

/*
 * Whether a permission must contain a role or can be used standalone
 * Works in unison with roles.administrator_forced
 * If a permission doesn't contain a role it can be assigned directly to a user
 */
vault.permissions.permission_must_contain_role
```

By default the package works without publishing its views. But if you wanted to publish the vault views to your application to take full control, run the vault:views command:

    $ php artisan vault:views
    
If you do not want vault to use its default routes file you can duplicate it and set the `vault.general.use_vault_routes` configuration to false and it will not load by default.
    
<a name="status_property"/>
### Utilizing the `status` property

If would would like to enable enabled/disabled users you can simply do a check wherever you are logging in your user:

```php
if ($user->status == 0)
    return Redirect::back()->withMessage("Your account is currently disabled");
```

<a name="route_middleware"/>
## Applying the Route Middleware

Laravel 5 is trying to steer away from the filters.php file and more towards using middleware. Here is an example right from the vault routes file of a group of routes that requires the Administrator role:

```php
Route::group([
	'middleware' => 'vault.routeNeedsRole',
	'role' => ['Administrator'],
	'redirect' => '/',
	'with' => ['error', 'You do not have access to do that.']
], function()
{
    Route::group(['prefix' => 'access'], function ()
    	{
    		/*User Management*/
    		Route::resource('users', '\Rappasoft\Vault\Http\Controllers\UserController', ['except' => ['show']]);
    	});
});
```

The above code checks to see if the currently authenticated user has the role `Administrator`, if not redirects to `/` with a session variable that has a key of `message` and value of `You do not have access to do that.`

The following middleware ships with the vault package:

- vault.routeNeedsRole
- vault.routeNeedsPermission
- vault.routeNeedsRoleOrPermission

<a name="route_middleware_params"/>
## Route Parameters

- `middleware` => The middleware name, you can change them in your app/Http/Kernel.php file.
- `role` => A string of one role or an array of roles by name.
- `permission` => A string of one permission or an array of permissions by name.
- `needsAll` => A boolean, false by default, that states whether or not all of the specified roles/permissions are required to authenticate.
- `with` => Sends a session flash on failure. Array with 2 items, first is session key, second is value.
- `redirect` => Redirect to a url if authentication fails.
- `redirectRoute` => Redirect to a route if authentication fails.
- `redirectAction` => Redirect to an action if authentication fails.

**If no redirect is specified a `response('Unauthorized', 401);` will be thrown.**

<a name="creating_middleware"/>
## Create Your Own Middleware

If you would like to create your own middleware, the following methods are available.

```php
/**
	 * Checks if the user has a Role by its name.
	 * @param string $name
	 * @return bool
*/
Vault::hasRole($role);

/**
	 * Checks to see if the user has an array of roles, and whether or not all must return true to authenticate
	 * @param array $roles
	 * @param boolean $needsAll
	 * @return bool
*/
Vault::hasRoles($roles, $needsAll);

/**
	 * Check if user has a permission by its name.
	 * @param string $permission.
	 * @return bool
*/
Vault::can($permission);

/**
	 * Check an array of permissions and whether or not all are required to continue
	 * @param array $permissions
	 * @param boolean $needsAll
	 * @return bool
*/
Vault::canMultiple($permissions, $needsAll);
```
**Vault::** by default uses the currently authenticated user. You can also do:

```php
$user->hasRole($role);
$user->hasRoles($roles, $needsAll);
$user->can($permission);
$user->canMultiple($permissions, $needsAll);
```

<a name="vault_route_trait"/>
### VaultRoute trait

If you would like to take advantage of the methods used by Vault's route handler, you can `use` it:

    `use Rappasoft\Vault\Traits\VaultRoute`
    
Which will give you methods in your middleware to grab route assets. You can then add methods to your middleware to grab assets that vault doesn't grab by default and take advantage of them.

<a name="blade_extensions"/>
## Blade Extensions

Vault comes with @blade extensions to help you show and hide data by role or permission without clogging up your code with unwanted if statements:

```php
@role('User')
    This content will only show if the authenticated user has the `User` role.
@endrole

@permission('can_view_this_content')
    This content will only show if the authenticated user is somehow associated with the `can_view_this_content` permission.
@endpermission
```

**Currently each call only supports one role or permission, however they can be nested.**

If you want to show or hide a specific section you can do so in your layout files the same way:

```php
@role('User')
    @section('special_content')
@endrole

@permission('can_view_this_content')
    @section('special_content')
@endpermission
```

## License

Vault is free software distributed under the terms of the MIT license.

## Additional information

Any issues, please [report here](https://github.com/rappasoft/vault/issues).
