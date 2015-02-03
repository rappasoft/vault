<?php namespace Rappasoft\Vault\Http\Middleware;

use Closure;
use Rappasoft\Vault\Traits\VaultRoute;

/**
 * Class RouteNeedsRole
 * @package Rappasoft\Vault\Http\Middleware
 */
class RouteNeedsRoleOrPermission {

	use VaultRoute;

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$assets = $this->getAssets($request);

		if ($assets['needsAll']) {
			if (! \Vault::hasRoles($assets['roles'], true) || ! \Vault::canMultiple($assets['permissions'], true))
				return $this->getRedirectMethodAndGo($request);
		} else {
			if (! \Vault::hasRoles($assets['roles'], false) && ! \Vault::canMultiple($assets['permissions'], false))
				return $this->getRedirectMethodAndGo($request);
		}

		return $next($request);
	}

}
