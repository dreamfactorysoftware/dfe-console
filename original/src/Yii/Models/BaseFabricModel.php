<?php
namespace Cerberus\Yii\Models;

use Cerberus\Enums\DSP;
use DreamFactory\Yii\Models\BaseModel;
use Kisma\Core\Utility\Log;

/**
 * BaseFabricModel
 * The base class for all fabric models.
 */
abstract class BaseFabricModel extends BaseModel
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const FABRIC_STORAGE_KEY = '%%STORAGE_KEY%%';

	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @return bool
	 */
	protected function beforeValidate()
	{
		if ( $this->hasAttribute( 'storage_id_text' ) && empty( $this->storage_id_text ) )
		{
			$this->storage_id_text = $this->_guid( __NAMESPACE__ );
		}

		return parent::beforeValidate();
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->getAttributes( true );
	}

	/**
	 * @return bool|string
	 */
	public function getPrivatePath()
	{
		if ( $this->hasAttribute( 'storage_id_text' ) )
		{
			$_path = str_ireplace( static::FABRIC_STORAGE_KEY, $this->storage_id_text, DSP::FABRIC_INSTANCE_PRIVATE_PATH );

			if ( is_dir( $_path ) )
			{
				return $_path;
			}

			Log::debug( 'Making private path: ' . $_path );

			if ( false !== @mkdir( $_path, 0777, true ) )
			{
				return $_path;
			}
		}

		return false;
	}

	/**
	 * @throws \RuntimeException
	 * @return string
	 */
	public function getSnapshotPath()
	{
		if ( false === ( $_path = $this->getPrivatePath() ) )
		{
			throw new \RuntimeException( 'Unable to determine private path of ' . __CLASS__ );
		}

		return $_path . DSP::HOSTED_SNAPSHOT_PATH;
	}

	/**
	 * @param string $namespace
	 *
	 * @return string
	 */
	protected function _guid( $namespace = null )
	{
		static $_guid = null;

		$_uuid = uniqid( null, true );

		$_data = $namespace . microtime( true );

		foreach ( $_SERVER as $_key => $_value )
		{
			$_data .= is_array( $_value ) ? serialize( $_value ) : $_value;
		}

		$_hash = strtoupper( hash( 'ripemd128', $_uuid . $_guid . md5( $_data ) ) );

		return $_guid
			=
			substr( $_hash, 0, 8 ) .
			'-' .
			substr( $_hash, 8, 4 ) .
			'-' .
			substr( $_hash, 12, 4 ) .
			'-' .
			substr( $_hash, 16, 4 ) .
			'-' .
			substr( $_hash, 20, 12 );
	}

	/**
	 * Returns a generic type suitable for type-casting
	 *
	 * @param \CDbColumnSchema $column
	 *
	 * @return string
	 */
	public function determineGenericType( $column )
	{
		$_simpleType = strstr( $column->dbType, '(', true );
		$_simpleType = strtolower( $_simpleType ? : $column->dbType );

		switch ( $_simpleType )
		{
			case 'bool':
				return 'boolean';

			case 'double':
			case 'float':
			case 'numeric':
				return 'float';

			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
			case 'bigint':
			case 'integer':
				if ( $column->size == 1 )
				{
					return 'boolean';
				}

				return 'integer';

			case 'binary':
			case 'varbinary':
			case 'blob':
			case 'mediumblob':
			case 'largeblob':
				return 'binary';

			case 'datetimeoffset':
			case 'timestamp':
			case 'datetime':
			case 'datetime2':
				return 'datetime';

			//	String types
			default:
			case 'string':
			case 'char':
			case 'text':
			case 'mediumtext':
			case 'longtext':
			case 'varchar':
			case 'nchar':
			case 'nvarchar':
				return 'string';
		}
	}
}
