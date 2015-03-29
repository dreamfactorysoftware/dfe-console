<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\BaseFabricDeploymentModel;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\DateTime;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Sql;

/**
 * This is the model for table "route_hash_t"
 *
 * @property integer $id
 * @property integer $type_nbr
 * @property string  $hash_text
 * @property string  $actual_path_text
 * @property string  $expire_date
 * @property string  $create_date
 * @property string  $lmod_date
 */
class RouteHash extends BaseFabricDeploymentModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return RouteHash the static model class
	 */
	public static function model( $className = __CLASS__ )
	{
		return parent::model( $className );
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'route_hash_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'hash_text, actual_path_text', 'required' ),
			array( 'type_nbr', 'numerical', 'integerOnly' => true ),
			array( 'hash_text, actual_path_text', 'length', 'max' => 1024 ),
			array( 'id, type_nbr, hash_text, actual_path_text, expire_date, create_date, lmod_date', 'safe', 'on' => 'search' ),
		);
	}

	/**
	 * @param string $path
	 * @param int    $keepDays The number of days to keep the hash alive. Set $keepDays to 0 to not expire the link.
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public static function hashFileForDownload( $path, $keepDays = 30 )
	{
		$_hash = sha1( md5( $path ) . microtime( true ) . getmypid() );

		$_model = new self();
		$_model->hash_text = $_hash;
		$_model->actual_path_text = $path;

		if ( 0 != $keepDays )
		{
			$_model->expire_date = date( 'c', time() + ( $keepDays * 86400 ) );
		}

		$_model->save();

		return $_hash;
	}

	/**
	 * @param string $hash
	 *
	 * @return bool|string
	 */
	public static function getFileFromHash( $hash )
	{
		Log::debug( 'Download hash lookup: ' . $hash );

		/** @var $_model RouteHash */
		$_model = RouteHash::model()->findByAttributes( array( 'hash_text' => $hash ) );

		if ( empty( $_model ) )
		{
			Log::debug( 'Download hash lookup: ' . $hash );

			return false;
		}

		$_path = $_model->actual_path_text;
		unset( $_model );

		return $_path;
	}

	/**
	 * @return bool True if I did anything, false otherwise.
	 */
	public static function expireFiles()
	{
		/** @var $_models RouteHash[] */
		$_models = self::model()->findAll( 'expire_date < CURRENT_DATE' );

		if ( empty( $_models ) )
		{
			return false;
		}

		foreach ( $_models as $_model )
		{
			if ( file_exists( $_model->actual_path_text ) )
			{
				unlink( $_model->actual_path_text );
			}

			$_model->delete();
			unset( $_model );
		}

		unset( $_models );

		return true;
	}
}