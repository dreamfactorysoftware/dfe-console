<?php namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Library\Utility\Json;
use Illuminate\Support\Collection;
use League\Flysystem\Filesystem;

class MetadataCollection extends Collection
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param Filesystem $filesystem
     *
     * @return mixed
     */
    public function load(Filesystem $filesystem)
    {
        $_file = $this->getMetadataFilename();

        if ($filesystem->has($_file)) {
            $_data = Json::decode($_json = $filesystem->get($_file));
            $this->items = is_array($_data) ? $_data : $this->getArrayableItems($_data);

            return $_data;
        }

        return $this->save($filesystem);
    }

    /**
     * @param Filesystem $filesystem
     * @param array      $values Optional values to merge with metadata for writing
     *
     * @return bool
     */
    public function save(Filesystem $filesystem, array $values = null)
    {
        $this->merge($values ?: []);

        return $filesystem->put($this->getMetadataFilename(), $this->toJson());
    }

    /**
     * @return string
     */
    protected function getMetadataFilename();
}