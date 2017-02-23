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
        $className = $this->map[$name];
        $class = $this->application->make($className);

        // All channels will be singletons
        $this->application->singleton(
            $className,
            function ($app) use ($class) {
                return $class;
            }
        );

        return $class;
    }
}