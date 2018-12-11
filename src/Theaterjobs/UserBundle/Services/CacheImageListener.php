<?php

namespace Theaterjobs\UserBundle\Services;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Imanee\Imanee;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Theaterjobs\InserateBundle\Entity\LogoPossessor;
use Theaterjobs\InserateBundle\Entity\MediaImage as InserateImage;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class CacheImageListener
{
    protected $cacheManager;
    protected $helper;
    protected $path;

    public function retinaCreate($entity)
    {
        //section add the retina img
        $ext = $entity->getuploadFile()->getExtension();
        $contentType = "Content-type: " . $entity->getuploadFile()->getMimeType();

        $path = $this->helper->asset($entity, 'uploadFile');
        $absolutePath = __DIR__ . '/../../../../web' . $path;

        $imanee = new Imanee($absolutePath);
        $Path2X = str_replace("." . $ext, "@2x." . $ext, $absolutePath);
        list($width, $height) = getimagesize($absolutePath);
        header($contentType);
        $imanee->resize(2 * $width, 2 * $height)->write($Path2X);
        // end of section

    }

    public function __construct(CacheManager $cacheManager, UploaderHelper $helper)
    {
        $this->cacheManager = $cacheManager;
        $this->helper = $helper;
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entity = $event->getObject();
        if ($entity instanceof LogoPossessor || $entity instanceof MediaImage || $entity instanceof InserateImage || $entity instanceof News) {
            if ($event->hasChangedField('path')) {
                
                // we temporary path only for LogoPossessor
                if (!$this->path && $entity instanceof LogoPossessor) {
                    //on logo remove get the tempPath
                    $this->path = $entity->getTemp();
                }
                // clear cache
                $this->cacheManager->remove($this->path);
                //removing section of retina img
                $ext2XTemp = pathinfo($this->path, PATHINFO_EXTENSION);
                $Path2XTemp = str_replace("." . $ext2XTemp, "@2x." . $ext2XTemp, __DIR__ . '/../../../../web' . $this->path);
                if (file_exists($Path2XTemp)) {
                    // removing old retina img
                    unlink($Path2XTemp);
                }
                // end of section
            }
        }

        if ($entity instanceof Profile) {
            if ($event->hasChangedField('isPublished')) {
                $mediaImages = $entity->getMediaImage();

                foreach ($mediaImages as $mediaImage) {
                    $path = $this->helper->asset($mediaImage, 'uploadFile');
                    $this->cacheManager->remove($path);
                }

                $mediaImagesAudio = $entity->getMediaAudio();
                foreach ($mediaImagesAudio as $item) {
                    $path = $this->helper->asset($item, 'uploadFileImage');
                    $this->cacheManager->remove($path);
                }
            }
        }

    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof LogoPossessor || $entity instanceof MediaImage || $entity instanceof InserateImage || $entity instanceof News) {
            if ($entity->getuploadFile()) {
                //create retina img
                $this->retinaCreate($entity);
            }
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof LogoPossessor || $entity instanceof MediaImage || $entity instanceof InserateImage || $entity instanceof News) {
                // finding the path
                $this->path = $this->helper->asset($entity, 'uploadFile');

            }
        }
    }


    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof LogoPossessor || $entity instanceof MediaImage || $entity instanceof InserateImage || $entity instanceof News) {
            if ($entity->getuploadFile()) {
                //create retina img
                $this->retinaCreate($entity);
            }
        }
    }

    // when delete entity so remove all img cache related
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof LogoPossessor || $entity instanceof MediaImage || $entity instanceof InserateImage || $entity instanceof News) {
            $path = $this->helper->asset($entity, 'uploadFile');
            $this->cacheManager->remove($path);
            //removing section of retina img
            $path = $this->helper->asset($entity, 'uploadFile');
            $ext2XTemp = pathinfo($path, PATHINFO_EXTENSION);
            $Path2XTemp = str_replace("." . $ext2XTemp, "@2x." . $ext2XTemp, __DIR__ . '/../../../../web' . $path);
            if (file_exists($Path2XTemp)) {
                // removing old retina img
                unlink($Path2XTemp);
            }
            // end of section
        }
    }
}