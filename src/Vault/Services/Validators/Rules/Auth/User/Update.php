<?php namespace Rappasoft\Vault\Services\Validators\Rules\Auth\User;

use Rappasoft\Vault\Services\Validators\Validator as Validator;

class Update extends Validator {

	public static $rules = [
		'email'			=>	'required|email',
		'name'			=>  'required',
	];

}