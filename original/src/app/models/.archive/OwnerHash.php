<?php
/**
 * This is the model class for table "owner_hash_t".
 *
 * @property integer $id
 * @property integer $owner_id
 * @property integer $owner_type_nbr
 * @property string  $hash_text
 * @property string  $create_date
 * @property string  $lmod_date
 */
class OwnerHash_OLD extends \DreamFactory\Yii\Models\BaseModel implements \DreamFactory\Interfaces\OwnerTypes
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return OwnerHash the static model class
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
		return 'owner_hash_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'owner_id, owner_type_nbr, hash_text, create_date, lmod_date', 'required' ),
			array( 'owner_id, owner_type_nbr', 'numerical', 'integerOnly' => true ),
			array( 'hash_text', 'length', 'max' => 128 ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'             => 'ID',
			'owner_id'       => 'Owner',
			'owner_type_nbr' => 'Owner Type Nbr',
			'hash_text'      => 'Hash Text',
			'create_date'    => 'Create Date',
			'lmod_date'      => 'Lmod Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return \CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria;

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'owner_id', $this->owner_id );
		$_criteria->compare( 'owner_type_nbr', $this->owner_type_nbr );
		$_criteria->compare( 'hash_text', $this->hash_text, true );
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