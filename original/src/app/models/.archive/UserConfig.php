<?php
/**
 * This is the model class for table "user_config_t".
 *
 * @property integer   $id
 * @property integer   $user_id
 * @property integer   $active_ind
 * @property string    $active_date
 * @property string    $config_text
 * @property string    $expire_date
 * @property string    $create_date
 * @property string    $lmod_date
 *
 * The followings are the available model relations:
 * @property \User     $user
 */
class UserConfig extends \DreamFactory\Yii\Models\BaseModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ServiceConfig the static model class
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
		return 'user_config_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'user_id', 'required' ),
			array( 'user_id, active_ind', 'numerical', 'integerOnly' => true ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'user' => array( static::BELONGS_TO, 'User', 'user_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'          => 'ID',
			'user_id'     => 'User ID',
			'active_ind'  => 'Active',
			'active_date' => 'Active Date',
			'config_text' => 'Configuration',
			'expire_date' => 'Expiration Date',
			'create_date' => 'Create Date',
			'lmod_date'   => 'Modified Date',
		);
	}

	/**
	 * Decode the config into an array
	 *
	 * @param CEvent $event
	 */
	public function onAfterFind( $event )
	{
		parent::onAfterFind( $event );

		if ( null !== $this->config_text )
		{
			if ( \DreamFactory\Yii\Utility\Pii::serialized( $this->config_text ) )
			{
				$this->config_text = unserialize( $this->config_text );
			}
		}
		else
		{
			$this->config_text = array();
		}
	}

	/**
	 * Encode the config
	 *
	 * @param CModelEvent $event
	 */
	public function onBeforeSave( $event )
	{
		parent::onBeforeSave( $event );

		if ( is_object( $this->config_text ) || is_array( $this->config_text ) )
		{
			$this->config_text = \DreamFactory\Yii\Utility\Pii::serialize( $this->config_text );
		}
		else
		{
			$this->config_text = null;
		}
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria;

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'user_id', $this->user_id );
		$_criteria->compare( 'active_ind', $this->active_ind );
		$_criteria->compare( 'active_date', $this->active_date, true );
		$_criteria->compare( 'config_text', $this->config_text, true );
		$_criteria->compare( 'expire_date', $this->expire_date, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider(
			$this,
			array(
				'criteria' => $_criteria,
			)
		);
	}
}