<?php

namespace Theaterjobs\FileSystemBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\ProfileBundle\Entity\MediaAudio;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Theaterjobs\ProfileBundle\Entity\MediaPdf;

/**
 * Class FileSystemController
 * @package Theaterjobs\FileSystemBundle\Controller
 */
class FileSystemController extends BaseController
{
    const DEFAULT_IMAGES = [
        'mediaImage' => 'bundles/theaterjobsmain/images/default-image.png',
        'mediaAudioImage' => 'bundles/theaterjobsmain/images/default-audio.png'
    ];

    /**
     * @Route("/audio/{id}", name="tj_audio_file")
     * @Method("GET")
     * @param MediaAudio $mediaAudio
     * @return Response
     */
    public function mediaAudioFileAction(MediaAudio $mediaAudio)
    {
        return $this->getFileResponse($mediaAudio);
    }

    /**
     * @Route("/pdf/{id}", name="tj_pdf_file")
     * @Method("GET")
     * @param MediaPdf $mediaPdf
     * @return Response
     */
    public function mediaPdfFileAction(MediaPdf $mediaPdf)
    {
        return $this->getFileResponse($mediaPdf);
    }

    /**
     * @Route("/image/{id}", name="tj_image_file")
     * @Method("GET")
     * @param MediaImage $mediaImage
     * @return response
     */
    public function mediaImageFileAction(MediaImage $mediaImage)
    {
        $defaultPhoto = self::DEFAULT_IMAGES['mediaImage'];
        
        return $this->getImageResponse($mediaImage, $defaultPhoto);
    }

    /**
     * @Route("/audio-image/{id}", name="tj_audio_image_file")
     * @Method("GET")
     * @param MediaAudio $mediaAudio
     * @return response
     */
    public function mediaAudioImageFileAction(MediaAudio $mediaAudio)
    {
        return $this->getImageResponse($mediaAudio, self::DEFAULT_IMAGES['mediaAudioImage']);
    }


    /**
     * @param $media
     * @return Response
     */
    protected function getFileResponse($media)
    {
        // get the vich helper service
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $sourcePath = '';

        // get the relative path for the file from the vich service
        $fileRelativePath = $helper->asset($media, $media->getUploadableField());

        // construct the full path of the file
        $fullPath = $this->container->get('kernel')->getWebDir() . $fileRelativePath;

        // check if the file exits to skip exceptions
        if (!empty(trim($fileRelativePath)) && file_exists($fullPath)) {
            // check logged user access
            if ($this->checkFileAccess($media->getProfile())) {
                $sourcePath = $fullPath;
            }
        }

        return new BinaryFileResponse($sourcePath);
    }

    /**
     * @param $media
     * @param string $defaultPhoto
     * @return Response
     */
    protected function getImageResponse($media, $defaultPhoto)
    {
        // get the vich helper service
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        // set the default profile photo
        $sourcePath = $this->container->get('kernel')->getUploadDir() . $defaultPhoto;

        // get the relative path for the file from the vich service
        $fileRelativePath = $helper->asset($media, $media->getImageUploadableField());

        // construct the full path of the file
        $fullPath = $this->container->get('kernel')->getWebDir() . $fileRelativePath;

        // check if the file exits to skip exceptions
        if (!empty(trim($fileRelativePath)) && file_exists($fullPath)) {
            // check logged user access
            if ($this->checkFileAccess($media->getProfile())) {
                $sourcePath = $fullPath;
            }
        }

        return new BinaryFileResponse($sourcePath);
    }
}