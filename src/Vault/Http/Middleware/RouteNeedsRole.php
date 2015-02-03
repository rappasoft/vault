<?php namespace Rappasoft\Vault\Http\Middleware;

use Closure;
use Rappasoft\Vault\Traits\VaultRoute;

/**
 * Class RouteNeedsRole
 * @package Rappasoft\Vault\Http\Middleware
 */
class RouteNeedsRole {

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

		if (! \Vault::hasRoles($assets['roles'], $assets['needsAll']))
			return $this->getRedirectMethodAndGo($request);

		return $next($request);
	}

}
