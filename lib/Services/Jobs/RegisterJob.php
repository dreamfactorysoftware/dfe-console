<?php namespace DreamFactory\Enterprise\Services\Jobs;

use DreamFactory\Enterprise\Common\Jobs\BaseJob;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;

class RegisterJob extends BaseJob
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
    protected $ownerId;
    /**
     * @type int The type of owner
     */
    protected $ownerType = OwnerTypes::USER;
    /**
     * @type \stdClass
     */
    protected $ownerInfo;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new command instance.
     *
     * @param int         $ownerId   The id of the entity
     * @param int         $ownerType The type of owner (implied from entity type if null)
     * @param string|null $tag       Optional string to describe your job
     */
    public function __construct($ownerId, $ownerType, $tag = null)
    {
        parent::__construct($tag);

        //  Make sure we have a good types
        $this->ownerInfo = OwnerTypes::getOwner($ownerId, $ownerType);

        $this->ownerId = $ownerId;
        $this->ownerType = (is_numeric($ownerType) && OwnerTypes::contains($ownerType))
            ? $ownerType
            : OwnerTypes::defines($ownerType,
                true);
    }

    /**
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return int
     */
    public function getOwnerType()
    {
        return $this->ownerType;
    }

    /**
     * @return string
     */
    public function getOwnerTypeName()
    {
        return OwnerTypes::prettyNameOf($this->ownerType);
    }

    public function getOwnerInfo()
    {
        return $this->ownerInfo;
    }
}
