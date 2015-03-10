<?php namespace DreamFactory\Enterprise\Common\Traits;

use DreamFactory\Enterprise\Common\Contracts\ManagerContract;

/**
 * A trait that adds object management to a class and implements the ObjectManagerContract
 */
trait ObjectManager
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array The things I'm managing
     */
    protected $_things = [];

    //********************************************************************************
    //* Methods
    //********************************************************************************

    /**
     * @param string $tag       The identifier of this thing
     * @param mixed  $thing     The thing to manage
     * @param bool   $overwrite If $tag already exists, and $overwrite is FALSE, an exception will be thrown.
     *
     * @return ManagerContract
     */
    public function manage( $tag, $thing, $overwrite = false )
    {
        if ( false === $overwrite && array_key_exists( $tag, $this->_things ) )
        {
            throw new \InvalidArgumentException( 'Item at "' . $tag . '" already exists. Overwrite not allowed.' );
        }

        $this->_things[$tag] = $thing;

        return $this;
    }

    /**
     * @param string $tag The tag to remove from the manager
     *
     * @return ManagerContract
     */
    public function unmanage( $tag )
    {
        if ( array_key_exists( $tag, $this->_things ) )
        {
            $this->_things[$tag] = null;
            unset( $this->_things[$tag] );
        }

        return $this;
    }

    /**
     * Returns the thing assigned to $tag.
     *
     * @param string $tag
     *
     * @return mixed
     * @throws \InvalidArgumentException when nothing is managed under $tag
     */
    public function resolve( $tag )
    {
        if ( isset( $this->_things[$tag] ) )
        {
            return $this->_things[$tag];
        }

        throw new \InvalidArgumentException( 'There is nothing assigned to "' . $tag . '".' );
    }

    /**
     * @return \IteratorIterator
     */
    public function getIterator()
    {
        return new \IteratorIterator( new \ArrayObject( $this->_things ) );
    }
}
