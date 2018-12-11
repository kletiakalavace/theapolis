<?php

namespace Theaterjobs\UserBundle\Services;

use Elastica\Result;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;


class UploadDirectoryNamer implements DirectoryNamerInterface
{
    public function directoryName($object, PropertyMapping $mapping)
    {
        if ($object instanceof Result)
            $object = $object->getSource();
        //check if the $object is an array to get the subdir (elastricSearch return object as array)
        if (is_array($object)) {
            $dir = $object['subdir'];
        } else {
            $dir = $object->getSubdir();
        }

        return $dir;
    }

}