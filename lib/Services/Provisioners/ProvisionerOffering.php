<?php namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Services\Contracts\Offering;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * A provisioner's offering
 */
class ProvisionerOffering implements Offering, Jsonable, Arrayable
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Maximum 64 character short name of offering
     */
    protected $_id;
    /**
     * @type string Maximum 64 character short name of offering
     */
    protected $_name;
    /**
     * @type string Maximum 1024 character description of offering
     */
    protected $_description;
    /**
     * @type array An array of key-value pairs ([:key=>[:option1=>:value1]...]) representing each choice in the offering
     */
    protected $_items = [];
    /**
     * @type string The suggested key to use as default when presenting
     */
    protected $_suggested = null;
    /**
     * @type array Any offering config info
     */
    protected $_config = [];
    /**
     * @type string The selected choice in $items
     */
    protected $_selection;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $id
     * @param array  $values The other values of the offering. These can be one of 'name', 'description', 'items',
     *                       'suggested', or 'config'
     */
    public function __construct($id, $values = [])
    {
        $this->_id = $id;

        foreach ($values as $_key => $_value) {
            if ($_key != 'id' && method_exists($this, 'set' . $_key)) {
                $this->{'set' . $_key}($_value);
            }
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param array $config
     *
     * @return ProvisionerOffering
     */
    public function setConfig($config)
    {
        $this->_config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param string $id
     *
     * @return ProvisionerOffering
     */
    public function setId($id)
    {
        $this->_id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     *
     * @return ProvisionerOffering
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param string $description
     *
     * @return ProvisionerOffering
     */
    public function setDescription($description)
    {
        $this->_description = $description;

        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param array $items
     *
     * @return ProvisionerOffering
     */
    public function setItems($items)
    {
        if (!is_array($items)) {
            $items = (array)$items;
        }

        $this->_items = $items;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuggested()
    {
        return $this->_suggested;
    }

    /**
     * @param string $suggested
     *
     * @return ProvisionerOffering
     */
    public function setSuggested($suggested)
    {
        $this->_suggested = $suggested;

        return $this;
    }

    /**
     * @return string
     */
    public function getSelection()
    {
        return $this->_selection;
    }

    /** @inheritdoc */
    public function toArray()
    {
        return [
            'id'          => $this->_id,
            'name'        => $this->_name,
            'description' => $this->_description,
            'items'       => $this->_items,
            'suggested'   => $this->_suggested,
            'selection'   => $this->_selection,
        ];
    }

    /** @inheritdoc */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
