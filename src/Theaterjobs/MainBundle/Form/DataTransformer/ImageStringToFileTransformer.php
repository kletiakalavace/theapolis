<?php

namespace Theaterjobs\MainBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ImageString to file Datatransformer.
 *
 * @category DataTransformer
 * @package  Theaterjobs\MainBundle\Form\DataTransformer
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class ImageStringToFileTransformer implements DataTransformerInterface
{

    /**
     * Transforms an object (File) to a string (base64).
     *
     * @param  File|null $file
     * @return string
     */
    public function transform($file)
    {
        return '';
    }

    /**
     * Transforms a string (base64) to an object (File).
     * @param string $imageString
     * @return File|null
     * @throws TransformationFailedException if no object (File)
     */
    public function reverseTransform($imageString)
    {

        if (!$imageString) {
            return;
        }

        preg_match('/data:([^;]*);base64,(.*)/', $imageString, $matches);

        $mimeType = $matches[1];
        $imageData = base64_decode($matches[2]);
        $filePath = sys_get_temp_dir() . "/" . uniqid();
        file_put_contents($filePath, $imageData);

        // for filename we use the unique namer from vich_uploader it's defined in image_uploader.yml
        $file = new UploadedFile($filePath, '', $mimeType, null, null, true);

        if (null === $file) {
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!', $imageString
            ));
        }
        
        return $file;
    }
}
