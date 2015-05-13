<?php namespace DreamFactory\Enterprise\Services\Contracts;

/**
 * The contract for a single provisioner offering
 */
interface Offering
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return array
     */
    public function getItems();

    /**
     * @return string
     */
    public function getSuggested();

}