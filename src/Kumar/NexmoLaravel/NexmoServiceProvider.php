<?php namespace Kumar\NexmoLaravel;

use Illuminate\Support\ServiceProvider;
use Kumar\Nexmo\NexmoProvider;

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
        $this->app->bindShared('Kumar\\Nexmo\\NexmoProvider',function($app){

            $config = $app['config']['nexmo'] ?: $app['config']['nexmo::config'];

            $nexmoProvider =  new NexmoProvider($config['key'],$config['secret']);

            $nexmoProvider->setFrom($config['from']);

            $nexmoProvider->setShortCode($config['shortcode']);

            $nexmoProvider->setLogger($app['log'])->setQueue($app['queue'])->setEventDispatcher($app['events']);

            return $nexmoProvider;
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
