<?php namespace Rappasoft\Vault\Services\Validators\Rules\Auth\Permission;

use Rappasoft\Vault\Services\Validators\Validator as Validator;

class Create extends Validator {

	public static $rules = [
		'name'			=>  'required',
		'display_name'	=>	'required',
	];

}