<?php
/*
 * Copyright © 2021. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

if ( !function_exists('getModelAbstractClass') ) {
    /**
     * @param object|string|null $test_class
     *
     * @return string|bool
     * @todo: class \Model
     */
    function getModelAbstractClass($test_class = null)
    {
        if ( $test_class ) {
            $test_class = is_object($test_class) ? $test_class : app(getRealClassName($test_class));

            $test_abstract_class = getModelAbstractClass();
            return $test_class instanceof $test_abstract_class;
        }

        return \Model::class;
    }
}

if ( !function_exists('getRealClassName') ) {
    /**
     * Returns the real class name.
     *
     * @param string|object $class <p> The tested class. This parameter may be omitted when inside a class. </p>
     *
     * @return string|false <p> The name of the class of which <i>`class`</i> is an instance.</p>
     * <p>
     *      Returns <i>`false`</i> if <i>`class`</i> is not an <i>`class`</i>.
     *      If <i>`class`</i> is omitted when inside a class, the name of that class is returned.
     * </p>
     */
    function getRealClassName($class)
    {
        if ( is_object($class) ) {
            $class = get_class($class);
        }
        throw_if(!class_exists($class), new Exception("Class `{$class}` not exists!"));

        try {
            $_class = eval("return new class extends {$class} { };");

        } catch (Exception $exception) {
            throw $exception;
        }

        if ( $_class && is_object($_class) ) {
            return get_parent_class($_class);
        }

        return false;
    }
}

if ( !function_exists('basenameOf') ) {
    /**
     * Returns basename of the given string after replace slashes and back slashes to DIRECTORY_SEPARATOR
     *
     * @param string $string
     *
     * @return string
     */
    function basenameOf(string $string)
    {
        $string = (string) str_ireplace('/', DIRECTORY_SEPARATOR,
            str_ireplace('\\', DIRECTORY_SEPARATOR, $string)
        );

        return basename($string);
    }
}