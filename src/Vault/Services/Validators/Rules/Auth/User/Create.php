<?php namespace Rappasoft\Vault\Services\Validators\Rules\Auth\User;

use Rappasoft\Vault\Services\Validators\Validator as Validator;

class Create extends Validator {

	public static $rules = [
		'name'			=>  'required',
		'email'					=>	'required|email|unique:users',
		'password'				=>	'required|alpha_num|min:6|confirmed',
		'password_confirmation'	=>	'required|alpha_num|min:6',
	];

}