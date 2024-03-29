<?php
/*
 * Copyright © 2020. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

namespace mPhpMaster\LaravelClassToFunction\Classes;

use Illuminate\Support\Traits\Tappable;
use mPhpMaster\LaravelClassToFunction\Traits\TMacroable;

/**
 * Class ClassToFunction
 *
 * @package mPhpMaster\LaravelClassToFunction\Classes
 */
class ClassToFunction
{
    use Tappable,
        TMacroable;

    /**
     * @var array
     */
    private static $store = [];
    /**
     * @var string
     */
    private static $maker = "";
    /**
     * @var string
     */
    private static $declaration = '
        function %s() {
            return call_user_func_array(
                %s::get(__FUNCTION__),
                func_get_args()
            );
        }
    ';

    public function __construct()
    {
        $path =
            \Illuminate\Support\Env::get(
                'COMPOSER_VENDOR_DIR',
                app()->basePath('vendor/composer/autoload_classmap.php')
            );
        if( ($_f = new \Illuminate\Filesystem\Filesystem)->exists($path) ) {
            collect($_f->getRequire($path))->filter(function ($path, $class) {
                try {
                    $is_class = str_before(base_path($path), "/") !== 'vendor' && class_exists($class) && is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class);
                    if( !$is_class ) {
                        return false;
                    }
                } catch( \Exception $exception ) {
                    return false;
                }

                try {
                    $_class = app($class);
                    $is_class = is_object($_class) ? getModelAbstractClass($_class) : false;

                    if( $is_class ) {
                        if( function_exists($_class_name = basenameOf($class)) ) {
                            $_class_name = "{$_class_name}_";
                        }
                        \mPhpMaster\LaravelClassToFunction\Classes\ClassToFunction::add($_class_name,
                            function (...$arguments) use ($class) {
                                return func_num_args() ? $class::find(...$arguments) : $class::all();
                            }
                        );
                        return true;
                    }
                    return false;
                } catch( \Exception $exception ) {

                }
                return false;
            });

            eval(\mPhpMaster\LaravelClassToFunction\Classes\ClassToFunction::make());
        }
    }

    /**
     * @param $name
     * @param $func
     */
    public static function add($name, $func)
    {
        // prepares a new function for make()
        $name = self::safeName($name);
        self::$store[ $name ] = $func;
        self::$maker .= sprintf(self::$declaration, $name, __CLASS__);
    }

    /**
     * @param $name
     *
     * @return bool|string
     */
    private static function safeName($name)
    {
        // extra safety against bad function names
        $name = preg_replace('/[^a-zA-Z0-9_]/', "", $name);
        $name = substr($name, 0, 64);
        return $name;
    }

    /**
     * @return string
     */
    public static function make()
    {
        // returns a string with all declarations
        return self::$maker;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public static function get($name)
    {
        // returns a stored callable
        return self::$store[ $name ];
    }
}
