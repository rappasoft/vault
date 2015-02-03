<?php

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

		Route::get('users/deactivated', ['as' => 'access.users.deactivated', 'uses' => '\Rappasoft\Vault\Http\Controllers\UserController@deactivated']);
		Route::get('users/deleted', ['as' => 'access.users.deleted', 'uses' => '\Rappasoft\Vault\Http\Controllers\UserController@deleted']);

		Route::get('user/{id}/delete', ['as' => 'access.user.delete-permanently', 'uses' => '\Rappasoft\Vault\Http\Controllers\UserController@delete'])
			->where([
				'id' => '[0-9]+'
			]);
		Route::get('user/{id}/restore', ['as' => 'access.user.restore', 'uses' => '\Rappasoft\Vault\Http\Controllers\UserController@restore'])
			->where([
				'id' => '[0-9]+'
			]);

		Route::get('user/{id}/mark/{status}', ['as' => 'access.user.mark', 'uses' => '\Rappasoft\Vault\Http\Controllers\UserController@mark'])
			->where([
				'id'     => '[0-9]+',
				'status' => '[0,1]'
			]);

		Route::get('user/{id}/password/change', ['as' => 'access.user.change-password', 'uses' => '\Rappasoft\Vault\Http\Controllers\UserController@changePassword'])
			->where([
				'id' => '[0-9]+'
			]);
		Route::post('user/{id}/password/change', ['as' => 'access.user.change-password', 'uses' => '\Rappasoft\Vault\Http\Controllers\UserController@updatePassword'])
			->where([
				'id' => '[0-9]+'
			]);

		/* Roles Management */
		Route::resource('roles', '\Rappasoft\Vault\Http\Controllers\RoleController', ['except' => ['show']]);

		/* Permission Management */
		Route::group(['prefix' => 'roles'], function ()
		{
			Route::resource('permissions', '\Rappasoft\Vault\Http\Controllers\PermissionController', ['except' => ['show']]);
		});
	});
});
