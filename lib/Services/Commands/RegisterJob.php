<?php namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\JobCommand;
use DreamFactory\Enterprise\Common\Enums\AppKeyEntities;
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
    protected $_entityType = AppKeyEntities::USER;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new command instance.
     *
     * @param int    $ownerId    The id of the entity
     * @param string $entityType The type of entity
     * @param int    $ownerType  The type of owner (implied from entity type if null)
     */
    public function __construct( $ownerId, $entityType = AppKeyEntities::USER, $ownerType = null )
    {
        $this->_ownerId = $ownerId;
        $entityType = strtoupper( trim( $entityType ) );
        $ownerType = $ownerType ? strtoupper( trim( $ownerType ) ) : null;

        //  Make sure we have a good types
        AppKeyEntities::defines( $entityType, true );

        if ( null === $ownerType )
        {
            $ownerType = AppKeyEntities::mapOwnerType( $entityType );
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
        $this->_entityType = $entityType;
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
