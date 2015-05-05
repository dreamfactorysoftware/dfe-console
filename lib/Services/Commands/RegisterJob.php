<?php namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\JobCommand;
use DreamFactory\Enterprise\Common\Enums\AppKeyClasses;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Library\Fabric\Database\Enums\OwnerTypes;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RegisterJob extends JobCommand
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'register';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The owner of the entity
     */
    protected $_ownerId;
    /**
     * @type int The type of owner
     */
    protected $_ownerType = OwnerTypes::USER;
    /**
     * @type string
     */
    protected $_entityType = AppKeyClasses::USER;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new command instance.
     *
     * @param int    $ownerId   The id of the entity
     * @param string $keyClass  The type of entity
     * @param int    $ownerType The type of owner (implied from entity type if null)
     */
    public function __construct( $ownerId, $keyClass = AppKeyClasses::USER, $ownerType = null )
    {
        $this->_ownerId = $ownerId;
        $keyClass = strtoupper( trim( $keyClass ) );
        $ownerType = $ownerType ? strtoupper( trim( $ownerType ) ) : null;

        //  Make sure we have a good types
        AppKeyClasses::defines( $keyClass, true );

        if ( null === $ownerType )
        {
            $ownerType = AppKeyClasses::mapOwnerType( $keyClass );
        }
        else
        {
            $ownerType = OwnerTypes::defines( $ownerType, true );
        }

        try
        {
            $_owner = $this->_locateOwner( $ownerId, $ownerType );
        }
        catch ( ModelNotFoundException $_ex )
        {
            throw new \InvalidArgumentException( 'The $ownerId "' . $ownerId . '" could not be located.' );
        }

        $this->_ownerId = $_owner->id;
        $this->_ownerType = $ownerType;
        $this->_entityType = $keyClass;
    }

    /**
     * @return string
     */
    public function getOwnerId()
    {
        return $this->_ownerId;
    }

    /**
     * @return int
     */
    public function getOwnerType()
    {
        return $this->_ownerType;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->_entityType;
    }

    /**
     * @return string The handler class for this job if different from "[class-name]Handler"
     */
    public function getHandler()
    {
        return 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\RegisterHandler';
    }
}
