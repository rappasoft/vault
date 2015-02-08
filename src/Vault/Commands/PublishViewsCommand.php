<?php namespace Rappasoft\Vault\Commands;

/**
 * Part of Laravel 5 Vault Package
 *
 * @package Vault
 * @version 1.0.0
 * @author Anthony Rappa
 * @license MIT License
 * @copyright (c) 2007-2015 Anthony Rappa, Rappasoft
 * @link http://www.rappasoft.com
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class PublishViewsCommand
 * @package Rappasoft\Vault\Commands
 */
class PublishViewsCommand extends Command {

	/**
	 * @var
	 */
	protected $app;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vault:views';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publishes the vault views to the resources folder.';

	/**
	 * @param $app
	 */
	public function __construct($app) {
		parent::__construct();
		$this->app = $app;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->line('');
		$this->info( "This will publish all of the vault views to the resources folder." );
		$this->line('');

		if ($this->confirm("Proceed? [Yes|no]"))
		{
			$this->line('');

			$this->info("Publishing Views...");
			if( $this->publishViews() )
			{
				$this->info("Views successfully published!");
			}
			else{
				$this->error(
					"There was a problem publishing the views."
				);
			}

			$this->line('');
		}
	}

	/**
	 * Publish the views
	 */
	protected function publishViews()
	{
		$views = dirname(__FILE__).'/../Views';
		$location = base_path('resources/views/vendor/vault');

		//Publishes the views to the resources path, since laravel checks for both, this lets the user alter them
		return File::copyDirectory($views, $location);
	}

}
