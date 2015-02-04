<?php namespace Rappasoft\Vault;

use Illuminate\Support\ServiceProvider;

/**
 * Class VaultServiceProvider
 * @package Rappasoft\Vault
 */
class VaultServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Package boot method
	 */
	public function boot() {
		$this->registerObservers();
		$this->registerCommands();
		$this->publishConfig();
		$this->registerViews();
		$this->publishMigration();
		$this->publishSeeder();
		$this->registerRoutes();
		$this->publishAssets();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerVault();
		$this->registerFacade();
		$this->registerBindings();
	}

	/**
	 * Register the application bindings.
	 *
	 * @return void
	 */
	private function registerVault()
	{
		$this->app->bind('vault', function($app) {
			return new Vault($app);
		});
	}

	/**
	 * Register the vault facade without the user having to add it to the app.php file.
	 *
	 * @return void
	 */
	public function registerFacade() {
		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Vault', 'Rappasoft\Vault\Facades\Vault');
		});
	}

	/**
	 * Register service provider bindings
	 */
	public function registerBindings() {
		$this->app->bindShared('Rappasoft\Vault\Repositories\User\UserRepositoryContract', function($app) {
			return new Repositories\User\EloquentUserRepository($app['config']['auth.model']);
		});

		$this->app->bind(
			'Rappasoft\Vault\Repositories\Role\RoleRepositoryContract',
			'Rappasoft\Vault\Repositories\Role\EloquentRoleRepository'
		);

		$this->app->bind(
			'Rappasoft\Vault\Repositories\Permission\PermissionRepositoryContract',
			'Rappasoft\Vault\Repositories\Permission\EloquentPermissionRepository'
		);
	}

	/**
	 * Publish the views to the application vendor/views directory
	 */
	public function registerViews() {
		//Load the views using vault::
		$this->loadViewsFrom(dirname(__FILE__).'/Views', 'vault');
	}

	/**
	 * Register any model observers
	 */
	public function registerObservers() {
		$user = $this->app['config']['auth.model'];
		$user = new $user;
		$user->observe(new Observers\UserObserver);
	}

	/**
	 * Register the publish views command
	 */
	public function registerCommands() {
		$this->app['command.vault.views'] = $this->app->share(function($app)
		{
			return new Commands\PublishViewsCommand($app);
		});

		$this->commands(
			'command.vault.views'
		);
	}

	/**
	 * Publish the config file to the application config directory
	 */
	public function publishConfig() {
		//Publish the configuration file to the config directory for use as Config::get('vault.setting')
		$this->publishes([
			dirname(__FILE__).'/../config/vault.php' => config_path('vault.php'),
		], 'config');
	}

	/**
	 * Publish the migration to the application migration folder
	 */
	public function publishMigration() {
		//TODO: Check to see if the migration exists in the migrations directory so it doesn't keep getting re-published every time the publish method is called

		//Publish the migration file
		$this->publishes([
			dirname(__FILE__).'/Templates/migrations.stub' => base_path('database/migrations/'.date('Y_m_d_His')."_vault_setup_tables.php"),
		], 'migration');
	}

	/**
	 * Publish the seed file to the application seeds folder
	 */
	public function publishSeeder() {
		//Publish the seeder file
		$this->publishes([
			dirname(__FILE__).'/Templates/seeds.stub' => base_path('database/seeds/VaultTableSeeder.php'),
		], 'seeder');

		//Append the seeder call to the master seeder file
		$this->appendSeederToMasterFile();
	}

	/**
	 * Register the routes file with the application
	 */
	public function registerRoutes() {
		//Load the routes file into the application
		if ($this->app['config']['vault.general.use_vault_routes'])
			include __DIR__.'/Http/routes.php';
	}

	/**
	 * Publish package assets to public directory
	 */
	public function publishAssets() {
		$this->publishes([
			dirname(__FILE__).'/../public/css' => base_path('public/css/vault'),
			dirname(__FILE__).'/../public/js' => base_path('public/js/vault'),
		], 'assets');
	}

	/**
	 * Get the services provided.
	 *
	 * @return string[]
	 */
	public function provides()
	{
		return array(
			'vault',
		);
	}

	/**
	 * Append the seeder class to the master seeder file
	 *
	 * @return bool
	 */
	protected function appendSeederToMasterFile() {
		$seeder = base_path("database/seeds/DatabaseSeeder.php");
		$addition = '$this->call("VaultTableSeeder");';

		//Not sure the best way to do this
		if(file_exists($seeder)) {
			$current_contents = file_get_contents($seeder);

			//See if the file doesnt already have it
			if(strpos($current_contents, $addition) === false) {
				$magic = '/((?:.|\s)*?\s*run\(\)\s*{)((?:.|\s)*)(}\s*})$/m';
				preg_match($magic, $current_contents, $matches);
				$new_content = $matches[1].$matches[2]."\n\t\t".$addition."\n\t".$matches[3];
				return file_put_contents($seeder, $new_content);
			}
			return false;
		}
		return false;
	}
}