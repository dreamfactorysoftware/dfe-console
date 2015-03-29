<?php
/**
 * Blob.php
 */
namespace Cerberus\Documents;

use Doctrine\CouchDB\Attachment;
use Doctrine\ODM\CouchDB\Mapping\Annotations;
use DreamFactory\Documents\DreamDocument;

/**
 * @Document @Index
 */
class Blob extends DreamDocument
{
	//*************************************************************************
	//* Fields
	//*************************************************************************

	/** @Id(strategy="ASSIGNED") */
	protected $id = null;
	/**
	 * @Field(type="string") @Index
	 * @var string
	 */
	protected $owner;
	/**
	 * @Attachments
	 */
	protected $attachments;

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Adds an attachment to this document
	 *
	 * @param string $data
	 * @param bool   $contentType
	 */
	public function addAttachment( $data, $contentType = false )
	{
		$_resource = $data;

		if ( is_file( $data ) || file_exists( $data ) )
		{
			if ( false === $contentType )
			{
				$contentType = $this->_getMimeType( $data );
			}

			$_resource = fopen( $data, 'r' );
		}

		$this->attachments[$this->id] = Attachment::createFromBinaryData( $_resource, $contentType );

		if ( \is_resource( $_resource ) )
		{
			fclose( $_resource );
		}
	}

	/**
	 * Removes an attachment from this document
	 *
	 * @return bool
	 */
	public function removeAttachment()
	{
		if ( isset( $this->attachments[$this->id] ) )
		{
			unset( $this->attachments[$this->id] );

			return true;
		}

		return false;
	}

	/**
	 * @param string $file
	 * @param bool   $stripCharset
	 *
	 * @return string
	 */
	protected function _getMimeType( $file, $stripCharset = true )
	{
		$_finfo = finfo_open( FILEINFO_MIME );
		$_mimeType = finfo_file( $_finfo, $file );
		finfo_close( $_finfo );

		if ( true === $stripCharset && false !== ( $_pos = strpos( $_mimeType, ';' ) ) )
		{
			$_mimeType = substr( $_mimeType, 0, $_pos );
		}

		return $_mimeType;
	}

	/**
	 * @return array
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	/**
	 * @param string $owner
	 *
	 * @return Blob
	 */
	public function setOwner( $owner )
	{
		$this->owner = $owner;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOwner()
	{
		return $this->owner;
	}

}
