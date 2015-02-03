<?php namespace Rappasoft\Vault\Repositories\User;

use Exception;
use Illuminate\Support\Facades\Auth;
use Rappasoft\Vault\Services\Validators\Rules\Auth\User\Create as RegisterUser;
use Rappasoft\Vault\Services\Validators\Rules\Auth\User\Update as UpdateUser;
use Rappasoft\Vault\Services\Validators\Rules\Auth\User\ChangePassword as ChangePassword;
use Rappasoft\Vault\Exceptions\EntityNotValidException;
use Rappasoft\Vault\Exceptions\UserNeedsRolesException;

/**
 * Class EloquentUserRepository
 * @package Rappasoft\Vault\Repositories\User
 */
class EloquentUserRepository implements UserRepositoryContract {

	/**
	 * @var model
	 * References the User namespace
	 */
	protected $model;

	/**
	 * @param $model
	 */
	public function __construct($model) {
		$class = '\\'.ltrim($model, '\\');
		$this->model = new $class;
	}

	/**
	 * @param $id
	 * @param bool $withRoles
	 * @return mixed
	 * @throws Exception
	 */
	public function findOrThrowException($id, $withRoles = false) {
		if ($withRoles)
			$user = $this->model->with('roles')->withTrashed()->find($id);
		else
			$user = $this->model->withTrashed()->find($id);

		if (! is_null($user)) return $user;

		throw new Exception('That user does not exist.');
	}

	/**
	 * @param $per_page
	 * @param string $order_by
	 * @param string $sort
	 * @param int $status
	 * @return mixed
	 */
	public function getUsersPaginated($per_page, $status = 1, $order_by = 'id', $sort = 'asc') {
		return $this->model->where('status', $status)->orderBy($order_by, $sort)->paginate($per_page);
	}

	/**
	 * @param $per_page
	 * @return \Illuminate\Pagination\Paginator
	 */
	public function getDeletedUsersPaginated($per_page) {
		return $this->model->onlyTrashed()->paginate($per_page);
	}

	/**
	 * @param string $order_by
	 * @param string $sort
	 * @return mixed
	 */
	public function getAllUsers($order_by = 'id', $sort = 'asc') {
		return $this->model->orderBy($order_by, $sort)->get();
	}

	/**
	 * @param $input
	 * @param $roles
	 * @return bool
	 * @throws EntityNotValidException
	 * @throws UserNeedsRolesException
	 * @throws Exception
	 */
	public function create($input, $roles) {
		//Validate user
		$this->registerUser($input);

		$user = new $this->model;
		$user->name = $input['name'];
		$user->email = $input['email'];
		$user->password = $input['password'];
		$user->status = isset($input['status']) ? 1 : 0;

		if ($user->save()) {
			//User Created, Update Roles

			//Validate that there's at least one role chosen, placing this here so
			//at lease the user can be updated first, if this fails the roles will be
			//kept the same as before the user was updated
			if (count($roles['assignees_roles']) == 0) {
				//Deactivate user
				$user->status = 0;
				$user->save();

				$exception = new UserNeedsRolesException();
				$exception->setValidationErrors('You must choose at lease one role. User has been created but deactivated.');

				//Grab the user id in the controller
				$exception->setUserID($user->id);
				throw $exception;
			}

			//Attach new roles
			foreach ($roles['assignees_roles'] as $role) {
				$user->attachRole($role);
			}

			return true;
		}

		throw new Exception('There was a problem creating this user. Please try again.');
	}

	/**
	 * @param $id
	 * @param $input
	 * @param $roles
	 * @return bool
	 * @throws EntityNotValidException
	 * @throws Exception
	 */
	public function update($id, $input, $roles) {
		$user = $this->findOrThrowException($id);

		//Figure out if email is not the same
		if ($user->email != $input['email']) {
			//Check to see if email exists
			if ($this->model->where('email', '=', $input['email'])->first())
				throw new Exception('That email address belongs to a different user.');
		}

		$this->validateUser($input);

		if ($user->update($input)) {
			//For whatever reason this just wont work in the above call, so a second is needed for now
			$user->status = isset($input['status']) ? 1 : 0;
			$user->save();

			//User Updated, Update Permissions

			//Validate that there's at least one role chosen, placing this here so
			//at lease the user can be updated first, if this fails the roles will be
			//kept the same as before the user was updated

			if (count($roles['assignees_roles']) == 0)
				throw new Exception('You must choose at least one role.');

			//Flush permissions out, then add array of new ones
			foreach ($user->roles as $role) {
				$user->detachRole($role->id);
			}

			//Attach new roles
			foreach ($roles['assignees_roles'] as $role) {
				$user->attachRole($role);
			}

			return true;
		}

		throw new Exception('There was a problem updating this user. Please try again.');
	}

	/**
	 * @param $id
	 * @param $input
	 * @return bool
	 * @throws EntityNotValidException
	 * @throws Exception
	 */
	public function updatePassword($id, $input) {
		$user = $this->findOrThrowException($id);

		//Validate password
		$changePassword = new ChangePassword();
		$changePassword->init(); //Initializes the rules into the array from the config file
		if(! $changePassword->passes($input)) {
			$exception = new EntityNotValidException();
			$exception->setValidationErrors($changePassword->errors);
			throw $exception;
		}

		//Passwords are hashed using UserObserver
		$user->password = $input['password'];
		if ($user->save())
			return true;

		throw new Exception('There was a problem changing this users password. Please try again.');
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws Exception
	 */
	public function destroy($id) {
		if (Auth::id() == $id)
			throw new Exception("You can not delete yourself.");

		$user = $this->findOrThrowException($id);
		if ($user->delete())
			return true;

		throw new Exception("There was a problem deleting this user. Please try again.");
	}

	/**
	 * @param $id
	 * @return boolean|null
	 * @throws Exception
	 */
	public function delete($id) {
		$user = $this->findOrThrowException($id, true);

		//Detach all roles
		foreach ($user->roles as $role) {
			$user->detachRole($role->id);
		}

		try {
			$user->forceDelete();
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws Exception
	 */
	public function restore($id) {
		$user = $this->findOrThrowException($id);

		if ($user->restore())
			return true;

		throw new Exception("There was a problem restoring this user. Please try again.");
	}

	/*
	 * Mark the user
	 */
	/**
	 * @param $id
	 * @param $status
	 * @return bool
	 * @throws Exception
	 */
	public function mark($id, $status) {
		if (Auth::id() == $id && $status == 0)
			throw new Exception("You can not deactivate yourself.");

		$user = $this->findOrThrowException($id);
		$user->status = $status;

		if ($user->save())
			return true;

		throw new Exception("There was a problem updating this user. Please try again.");
	}

	/**
	 * @param array $userDetails
	 * @return bool
	 * @throws EntityNotValidException
	 */
	private function validateUser(array $userDetails = array()) {
		$updateUser = new UpdateUser();

		if(! $updateUser->passes($userDetails)) {
			$exception = new EntityNotValidException();
			$exception->setValidationErrors($updateUser->errors);
			throw $exception;
		}

		return true;
	}

	/**
	 * @param array $userDetails
	 * @return bool
	 * @throws EntityNotValidException
	 */
	private function registerUser(array $userDetails = array()) {
		$registerUser = new RegisterUser();

		if(! $registerUser->passes($userDetails)) {
			$exception = new EntityNotValidException();
			$exception->setValidationErrors($registerUser->errors);
			throw $exception;
		}

		return true;
	}
}