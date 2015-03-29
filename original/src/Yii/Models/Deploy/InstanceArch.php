<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Yii\Models\Auth\User;

/**
 * This is the model for table "instance_arch_t"
 *
 * @property integer            $id
 * @property integer            $user_id
 * @property integer            $vendor_id
 * @property integer            $vendor_image_id
 * @property integer            $vendor_credentials_id
 * @property integer            $guest_location_nbr
 * @property string             $instance_id_text
 * @property string             $db_name_text
 * @property string             $db_user_text
 * @property string             $db_password_text
 * @property string             $storage_id_text
 * @property integer            $flavor_nbr
 * @property string             $base_image_text
 * @property string             $instance_name_text
 * @property string             $region_text
 * @property string             $availability_zone_text
 * @property string             $security_group_text
 * @property string             $ssh_key_text
 * @property integer            $root_device_type_nbr
 * @property string             $public_host_text
 * @property string             $public_ip_text
 * @property string             $private_host_text
 * @property string             $private_ip_text
 * @property string             $request_id_text
 * @property string             $request_date
 * @property integer            $deprovision_ind
 * @property integer            $provision_ind
 * @property integer            $trial_instance_ind
 * @property integer            $state_nbr
 * @property integer            $vendor_state_nbr
 * @property string             $vendor_state_text
 * @property integer            $environment_id
 * @property integer            $activate_ind
 * @property string             $start_date
 * @property string             $end_date
 * @property string             $terminate_date
 * @property string             $create_date
 * @property string             $lmod_date
 *
 * Relations:
 * @property Environment        $environment
 * @property VendorCredentials  $vendorCredentials
 * @property Vendor             $vendor
 * @property VendorImage        $vendorImage
 * @property User               $user
 */
class InstanceArch extends Instance
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return \Cerberus\Yii\Models\Deploy\Instance
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
        return 'instance_arch_t';
    }
}