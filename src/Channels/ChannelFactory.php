<?php

namespace Railroad\Railnotifications\Channels;

use Illuminate\Foundation\Application;

class ChannelFactory
{
    private $application;

    protected $map = [];

    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->map = config('railnotifications.channels');
    }

    /**
     * @param $name
     * @return ChannelInterface
     */
    public function make($name): ChannelInterface
    {
        $class = $this->map[$name];

        // All channels will be singletons
        $this->application->singleton(
            $name,
            function (Application $app) use ($class) {
                return $app->make($class);
            }
        );

        return $this->application->make($class);
    }
}