<?php
/**
 * FabricDocument.php
 * A base container for key => value store documents (i.e. CouchDB, Mongo, etc.). Does nothing, like the goggles.
 */
namespace Cerberus\Documents;

use Doctrine\ODM\CouchDB\Mapping\Annotations;

/**
 * @Document @InheritanceRoot @MappedSuperclass
 */
class FabricDocument
{
	//*************************************************************************
	//* Fields
	//*************************************************************************

	/** @Id */
	protected $id = null;
	/** @Version */
	protected $version = null;
	/**
	 * @Field(type="string")
	 */
	protected $created_at = null;
	/**
	 * @Field(type="string")
	 */
	protected $updated_at = null;

	//*************************************************************************
	//* Members
	//*************************************************************************

	/**
	 * @var string The storage date format
	 */
	protected $_dateFormat = 'c';

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Timestamps a field with the current date (or supplied date, in the current date format
	 *
	 * @param string $date
	 * @param string $field
	 *
	 * @throws \InvalidArgumentException
	 * @return string The stamped date
	 */
	public function timestamp( $date = null, $field = 'updated_at' )
	{
		if ( null !== $date )
		{
			//	Try to see if this date is cool...
			if ( false === strtotime( $date ) || ( is_numeric( $date ) && false === date( $this->_dateFormat, $date ) ) )
			{
				$date = null;
			}
			else
			{
				$date = date( $this->_dateFormat, is_numeric( $date ) ? $date : strtotime( $date ) );
			}
		}

		if ( property_exists( $this, $field ) )
		{
			return $this->{$field} = $date ? : date( $this->_dateFormat );
		}

		throw new \InvalidArgumentException( 'The field "' . $field . '" does not exist in this document.' );
	}

	/**
	 * Set defaults
	 */
	public function __construct()
	{
		$this->created_at = $this->timestamp();
	}

	/**
	 * @param string $created_at
	 *
	 * @return FabricDocument
	 */
	public function setCreatedAt( $created_at )
	{
		$this->timestamp( $created_at, 'created_at' );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @param string $id
	 *
	 * @return FabricDocument
	 */
	public function setId( $id )
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $updated_at
	 *
	 * @return FabricDocument
	 */
	public function setUpdatedAt( $updated_at = null )
	{
		$this->timestamp( $updated_at );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUpdatedAt()
	{
		return $this->updated_at;
	}

	/**
	 * @param string $version
	 *
	 * @return FabricDocument
	 */
	public function setVersion( $version )
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @param string $dateFormat
	 *
	 * @return FabricDocument
	 */
	public function setDateFormat( $dateFormat )
	{
		$this->_dateFormat = $dateFormat;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateFormat()
	{
		return $this->_dateFormat;
	}

}
