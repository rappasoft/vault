<?php namespace Rappasoft\Vault\Blade;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\View\Compilers\BladeCompiler as Compiler;

class BladeExtender
{
	public static function attach(Application $app)
	{
		$blade = $app['view']->getEngineResolver()->resolve('blade')->getCompiler();
		$class = new static;
		foreach (get_class_methods($class) as $method) {
			if ($method == 'attach') continue;

			$blade->extend(function ($value) use ($app, $class, $blade, $method) {
				return $class->$method($value);
			});
		}
	}

	public function openRole($value)
	{
		$matcher = '/@role\([\'"]([\w\d]*)[\'"]\)/';
		return preg_replace($matcher, '<?php if (Vault::hasRole(\'$1\')): ?> ', $value);
	}

	public function closeRole($value)
	{
		return preg_replace("/@endrole/", '<?php endif; ?>', $value);
	}

	public function openPermission($value)
	{
		$matcher = '/@permission\([\'"]([\w\d]*)[\'"]\)/';
		return preg_replace($matcher, '<?php if (Vault::can(\'$1\')): ?> ', $value);
	}

	public function closePermission($value)
	{
		$matcher = "/@endpermission/";
		return preg_replace($matcher, '<?php endif; ?>', $value);
	}
}
