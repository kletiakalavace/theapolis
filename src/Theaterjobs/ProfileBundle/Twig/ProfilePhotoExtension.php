<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 22/03/2018
 * Time: 21:42
 */

namespace Theaterjobs\ProfileBundle\Twig;

use Elastica\Result;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;


/**
 * Class ProfilePhotoExtension
 * @package Theaterjobs\ProfileBundle\Twig
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class ProfilePhotoExtension extends \Twig_Extension
{
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
     * @var ContainerInterface $container
     */
    protected $container;


    /**
     * ProfilePhotoExtension constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->helper = $container->get('vich_uploader.templating.helper.uploader_helper');
        $this->assets = $container->get('templating.helper.assets');
        $this->cacheManager = $container->get('liip_imagine.cache.manager');
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('profile_photo', [$this, 'profilePhoto'])
        ];
    }

    /**
     * preview the profile photo of the given profile
     * @param  $profile
     * @param $uploadableField
     * @param $liipImageFilter
     * @return mixed
     * @internal param $filter
     */
    public function profilePhoto($profile, $uploadableField = 'uploadFile', $liipImageFilter = 'profile_photo')
    {

        // set the default profile photo
        $sourcePath = $this->assets->getUrl('bundles/theaterjobsmain/images/profile-placeholder.svg');
        $profilePhotoMediaId = null;
        $profilePhotoMedia = null;

        // check profile type to use the right way to get the media image
        switch ($profile) {
            case $profile instanceof Profile:
                $profilePhotoMedia = $profile->getProfilePhoto();
                break;
            case $profile instanceof Result:
                $profilePhotoMedias = $profile->mediaImage;

                if ($profilePhotoMedias) {
                    $profilePhotoMedia = $this->getProfilePhoto($profilePhotoMedias);
                }
                break;

            default:
                $profilePhotoMedias = $profile['mediaImage'];
                if ($profilePhotoMedias) {
                    $profilePhotoMedia = $this->getProfilePhoto($profilePhotoMedias);
                }
        }


        if ($profilePhotoMedia) {
            // get the relative path for the file from the vich service (we specify even the entity type to make sure we get the right path)
            $fileRelativePath = $this->helper->asset($profilePhotoMedia, $uploadableField, 'Theaterjobs\\ProfileBundle\\Entity\\MediaImage');
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
        }

        return new \Twig_Markup(
            $sourcePath,
            'utf8'
        );
    }

    /**
     * @param array $profilePhotoMedias
     * @return mixed
     * get profile photo from  Media Images type array
     */
    protected function getProfilePhoto(array $profilePhotoMedias)
    {
        return current(array_filter($profilePhotoMedias, function ($media) {
            return $media['isProfilePhoto'] == true;
        }));
    }
}