<?php


namespace Kumar\Nexmo;


use Illuminate\Support\Facades\Facade;

class SMS extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'Kumar\\Nexmo\\NexmoClient'; }
} 