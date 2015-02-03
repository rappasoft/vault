<?php

/*
 * Laravel 5 Vault Package
 * Author: Anthony Rappa
 * E-mail: rappa819@gmail.com
 * Website: http://www.rappasoft.com
 */

return array(

	/*
	 * General/misc config options
	 */
	'general' => [
		'company_name' => 'Rappasoft', //Used in footer
		'use_vault_routes' => true, //Whether or not to load the vault routes file by default
	],

	/*
	|--------------------------------------------------------------------------
	| Vault Role Model
	|--------------------------------------------------------------------------
	|
	| This is the Role model used by Vault to create correct relations.  Update
	| the role if it is in a different namespace.
	|
	*/
	'role' => 'Rappasoft\Vault\VaultRole',

	/*
	|--------------------------------------------------------------------------
	| Vault Roles Table
	|--------------------------------------------------------------------------
	|
	| This is the Roles table used by Vault to save roles to the database.
	|
	*/
	'roles_table' => 'roles',

	/*
	|--------------------------------------------------------------------------
	| Vault Permission Model
	|--------------------------------------------------------------------------
	|
	| This is the Permission model used by Vault to create correct relations.  Update
	| the permission if it is in a different namespace.
	|
	*/
	'permission' => 'Rappasoft\Vault\VaultPermission',

	/*
	|--------------------------------------------------------------------------
	| Vault Permissions Table
	|--------------------------------------------------------------------------
	|
	| This is the Permissions table used by Vault to save permissions to the database.
	|
	*/
	'permissions_table' => 'permissions',

	/*
	|--------------------------------------------------------------------------
	| Vault permission_role Table
	|--------------------------------------------------------------------------
	|
	| This is the permission_role table used by Vault to save relationship between permissions and roles to the database.
	|
	*/
	'permission_role_table' => 'permission_role',

	/*
	|--------------------------------------------------------------------------
	| Vault assigned_roles Table
	|--------------------------------------------------------------------------
	|
	| This is the assigned_roles table used by Vault to save assigned roles to the database.
	|
	*/
	'assigned_roles_table' => 'assigned_roles',

	/*
	 * Configurations for the user views
	 */
	'users' => [
		'default_per_page' => 25,
		'password_validation' => 'required|alpha_num|min:6', // "confirmed" is applied by default
	],

	/*
	 * Configuration for roles
	 */
	'roles' => [
		'role_must_contain_permission' => true, //Whether a role must contain a permission or can be used standalone
	],

);