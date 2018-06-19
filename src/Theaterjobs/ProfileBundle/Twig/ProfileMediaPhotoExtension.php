<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 24/03/2018
 * Time: 21:28
 */

namespace Theaterjobs\ProfileBundle\Twig;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Theaterjobs\ProfileBundle\Entity\MediaAudio;
use Theaterjobs\ProfileBundle\Entity\MediaImage;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ProfileMediaPhotoExtension
 * @package Theaterjobs\ProfileBundle\Twig
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class ProfileMediaPhotoExtension extends \Twig_Extension
{
    const DEFAULT_IMAGES = [
        'mediaImage' => 'bundles/theaterjobsmain/images/default-image.png',
        'mediaAudioImage' => 'bundles/theaterjobsmain/images/default-audio.png'
    ];
    /**
     * @var UploaderHelper $helper
     */
    protected $helper;

    /**
     * @var AssetsHelper $assets
     */
    protected $assets;

    /**
     * @var CacheManager $cacheManager
     */
    protected $cacheManager;

    /**
     * @var Router $router
     */
    protected $router;

    /**
     * @var ContainerInterface $container
     */
    protected $container;


    /**
     * ProfileMediaPhotoExtension constructor.
     * @param ContainerInterface $container
     * @param Router $router
     */
    public function __construct(ContainerInterface $container, Router $router)
    {
        $this->container = $container;
        $this->helper = $container->get('vich_uploader.templating.helper.uploader_helper');
        $this->assets = $container->get('templating.helper.assets');
        $this->cacheManager = $container->get('liip_imagine.cache.manager');
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('profile_media_photo', [$this, 'profileMediaPhoto'])
        ];
    }

    /**
     * preview the photo of the given media
     * @param MediaAudio|MediaImage $media
     * @param string $uploadableField
     * @param string $liipImageFilter
     * @return mixed
     * @internal param $filter
     */
    public function profileMediaPhoto($media, $uploadableField = 'uploadFile', $liipImageFilter = 'preview')
    {
        // media image route
        $route = 'tj_image_file';
        $defaultImage = self::DEFAULT_IMAGES['mediaImage'];

        // if the instance is media audio change route and default image
        if ($media instanceof MediaAudio) {
            $route = 'tj_audio_image_file';
            $defaultImage = self::DEFAULT_IMAGES['mediaAudioImage'];
        }

        // set the default profile photo
        $sourcePath = $this->assets->getUrl($defaultImage);
        $profile = $media->getProfile();

        // check if the profile is public
        if ($profile->getIsPublished()) {
            // get the relative path for the file from the vich service
            $fileRelativePath = $this->helper->asset($media, $uploadableField);
            // construct the full path of the file
            $fullPath = $this->container->get('kernel')->getWebDir() . $fileRelativePath;
            // check if the file exits to skip exceptions
            if (!empty(trim($fileRelativePath)) && file_exists($fullPath)) {
                $sourcePath = $this->cacheManager
                    ->getBrowserPath(
                        $fileRelativePath,
                        $liipImageFilter
                    );
            }
        } else {
            // serve the file from  php in order to check for right access
            $sourcePath = $this->router->generate($route, ['id' => $media->getId()]);
        }

        return new \Twig_Markup(
            $sourcePath,
            'utf8'
        );
    }
}