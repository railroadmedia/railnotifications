<?php

namespace Railroad\Railnotifications\Decorators;

class Decorator
{
    /**
     * @param $data
     * @return mixed|Collection
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function decorate($data)
    {
        foreach (config('railnotifications.decorators',[]) as $decoratorClassName) {

            /**
             * @var $decorator DecoratorInterface
             */
            $decorator = app()->make($decoratorClassName);

            if (empty($data)) {
                return $data;
            }

            $data = $decorator->decorate($data);
        }

        return $data;
    }
}
