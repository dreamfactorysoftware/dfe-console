<?php
/**
 * BlobStorage.php
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @package   cerberus
 * @filesource
 */
namespace Cerberus\Services\CouchDb;

use Cerberus\Documents\Blob;
use Doctrine\CouchDB\Attachment;
use Doctrine\CouchDB\HTTP\HTTPException;
use Doctrine\CouchDB\View\FolderDesignDocument;
use Doctrine\ODM\CouchDB\DocumentRepository;
use DreamFactory\Utility\Relax;
use Kisma\Core\Enums\HashType;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Services\SeedService;
use Kisma\Core\Utility\Convert;
use Kisma\Core\Utility\Hasher;
use Kisma\Core\Utility\Inflector;
use Kisma\Core\Utility\Log;

/**
 * Stores blobs in CouchDB
 */
class BlobStorage extends SeedService
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string Our document type
	 */
	const DocumentType = 'Cerberus\\Documents\\Blob';
	/**
	 * @var string
	 */
	const DefaultNamespace = 'com.dreamfactory.cerberus.blob';

	//*************************************************************************
	//* Private Members
	//*************************************************************************

	/**
	 * @var string Used to construct keys in the queue
	 */
	protected $_namespace = self::DefaultNamespace;
	/**
	 * @var \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected $_dm = null;
	/**
	 * @var string
	 */
	protected $_owner = null;

	//*************************************************************************
	//* Public Methods
	//*************************************************************************

	/**
	 * @param \Kisma\Core\Interfaces\ConsumerLike $consumer
	 * @param array                               $settings
	 *
	 * @throws \InvalidArgumentException
	 * @return \Cerberus\Services\CouchDb\BlobStorage
	 */
	public function __construct( ConsumerLike $consumer, $settings = array() )
	{
		//	Save settings before going deep...
		$this->_namespace = static::DefaultNamespace;
		$this->set( 'settings', $settings );

		parent::__construct( $consumer, $settings );

		if ( null === $this->_owner )
		{
			throw new \InvalidArgumentException( 'No "owner" specified. Please set during construction.' );
		}
	}

	/**
	 * Flush out the DM before we die
	 */
	public function __destruct()
	{
		if ( null !== $this->_dm )
		{
			$this->_dm->flush();
		}

		parent::__destruct();
	}

	/**
	 * @param string      $id          The blob key and/or file name
	 * @param string      $data        The blob data or file name
	 * @param bool|string $contentType The content type of the data
	 *
	 * @throws \Doctrine\CouchDB\HTTP\HTTPException|\Exception
	 */
	public function create( $id, $data = null, $contentType = false )
	{
		$id = trim( $id, ' /' );

		/** @var $_dm DocumentRepository */
		if ( null !== ( $_blob = $this->_findById( $id ) ) )
		{
			throw new HTTPException( 'Blob "' . $id . '" exists.', 400 );
		}

		$_blob = new Blob();
		$_blob->setId( $id );
		$_blob->setOwner( $this->_owner );
		$_blob->addAttachment( $data ? : $id, $contentType );

		$this->_dm->persist( $_blob );
		$this->_dm->flush();
	}

	/**
	 * @param string $id
	 *
	 * @return Attachment|Attachment[]
	 * @throws \Doctrine\CouchDB\HTTP\HTTPException|\Exception
	 */
	public function read( $id )
	{
		/** @var $_dm DocumentRepository */
		if ( null === ( $_blob = $this->_findById( $id, true ) ) )
		{
			throw new HTTPException( 'Blob "' . $id . '" not found.', 404 );
		}

		$_docs = $_blob->getAttachments();

		if ( !empty( $_docs ) )
		{
			/** @var $_docs Attachment[] */
			foreach ( $_docs as $_key => $_doc )
			{
				if ( $id == $_key )
				{
					return $_doc;
				}
			}

			return $_docs;
		}
	}

	/**
	 * @param string      $id          The blob ID
	 * @param string      $data        The blob data or file name
	 * @param bool|string $contentType The content type of the data
	 *
	 * @throws \Doctrine\CouchDB\HTTP\HTTPException
	 * @internal param string $key The blob key
	 */
	public function update( $id, $data, $contentType = false )
	{
		/** @var $_dm DocumentRepository */
		if ( null === ( $_blob = $this->_findById( $id ) ) )
		{
			throw new HTTPException( 'Blob "' . $id . '" not found.', 404 );
		}

		$_blob->addAttachment( $data, $contentType );
		$this->_dm->persist( $_blob );
		$this->_dm->flush();
	}

	/**
	 * @param string      $id      The blob ID
	 *
	 * @throws \Doctrine\CouchDB\HTTP\HTTPException
	 * @internal param string $key The blob key
	 */
	public function delete( $id )
	{
		/** @var $_dm DocumentRepository */
		if ( null === ( $_blob = $this->_findById( $id ) ) )
		{
			throw new HTTPException( 'Blob "' . $id . '" not found.', 404 );
		}

		$this->_dm->detach( $_blob );
		$this->_dm->flush();
	}

	/**
	 * @param string $viewPath
	 */
	public function refreshViews( $viewPath )
	{
		$this->_dm->getCouchDBClient()->createDesignDocument(
			'system',
			new FolderDesignDocument( $viewPath )
		);

		//	Flush baby!
		$this->_dm->flush();
	}

	//*************************************************************************
	//* Private Methods
	//*************************************************************************

	/**
	 * @param string $id
	 *
	 * @param bool   $encode
	 *
	 * @return Blob
	 */
	protected function _findById( $id, $encode = false )
	{
		return $this->_dm->find( static::DocumentType, $encode ? urlencode( $id ) : $id );
	}

	/**
	 * @param array $keyPairs
	 *
	 * @return Blob
	 */
	protected function _findByAttributes( array $keyPairs )
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->_dm->getRepository( static::DocumentType )->findOneBy( $keyPairs );
	}

	/**
	 * @param array $settings
	 */
	protected function _checkDatabase( $settings = array() )
	{
		if ( false === Relax::databaseExists( $_client = $this->_dm->getCouchDBClient() ) )
		{
			$this->refreshViews( $settings['view_path'] );
		}
	}

	/**
	 * @param \Doctrine\ODM\CouchDB\DocumentManager $dm
	 *
	 * @return BlobStorage
	 */
	public function setDm( $dm )
	{
		$this->_dm = $dm;

		return $this;
	}

	/**
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	public function getDm()
	{
		return $this->_dm;
	}

	/**
	 * @param string $namespace
	 *
	 * @return BlobStorage
	 */
	public function setNamespace( $namespace )
	{
		$this->_namespace = $namespace;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->_namespace;
	}

	/**
	 * @param string $owner
	 *
	 * @return BlobStorage
	 */
	public function setOwner( $owner )
	{
		$this->_owner = $owner;

		$_settings = $this->get( 'settings', array(), true );

		//	Create our bucket name
		$_settings['dbname'] = ( isset( $_settings['db_prefix'] ) ? trim( $_settings['db_prefix'], ' _' ) . '_' : null ) . $owner;

		// 	Set our document manager up...
		$this->_dm = Relax::documentManager( $_settings );

		//	Make sure our database and views are there
		$this->_checkDatabase( $_settings );

		//	Add a reference to the queue to the kisma global space
		\Kisma::set( 'app.blob_storage', $this );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOwner()
	{
		return $this->_owner;
	}
}
