<?php
namespace DreamFactory\Enterprise\Common\Contacts;

interface AdaptableContract
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get( $name );

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function __set( $name, $value );

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __unset( $name );

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call( $method, $arguments );

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic( $method, $arguments );
}