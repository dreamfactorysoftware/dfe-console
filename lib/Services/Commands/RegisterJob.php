<?php namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\JobCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;

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
     * @type \stdClass
     */
    protected $_ownerInfo;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new command instance.
     *
     * @param int $ownerId   The id of the entity
     * @param int $ownerType The type of owner (implied from entity type if null)
     */
    public function __construct( $ownerId, $ownerType )
    {
        //  Make sure we have a good types
        $this->_ownerInfo = OwnerTypes::getOwner( $ownerId, $ownerType );

        $this->_ownerId = $this->_ownerInfo->id;
        $this->_ownerType = $this->_ownerInfo->type;
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
    public function getOwnerTypeName()
    {
        return OwnerTypes::prettyNameOf( $this->_ownerType );
    }

    public function getOwnerInfo()
    {
        return $this->_ownerInfo;
    }

    /**
     * @return string The handler class for this job if different from "[class-name]Handler"
     */
    public function getHandler()
    {
        return 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\RegisterHandler';
    }
}
