<?php namespace Kumar\NexmoLaravel;

use Illuminate\Support\ServiceProvider;

class NexmoServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
        $this->app->bindShared('Kumar\\Nexmo\\NexmoClient',function($app){

            $config = $app['config']['nexmo'] ?: $app['config']['nexmo::config'];

            $nexmoMessage =  new NexmoClient($config['key'],$config['secret']);

            $nexmoMessage->setFrom($config['from']);

            return $nexmoMessage;
        });

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
