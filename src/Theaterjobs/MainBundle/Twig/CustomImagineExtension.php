<?php

/**
 * @author Ihoxha
 * @copyright 2018 Theapolis, Hamburg
 * @package Theaterjobs\MainBundle\Twig
 *
 * Created using PhpStorm at 19/05/2018 22:04
 */

namespace Theaterjobs\MainBundle\Twig;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class CustomImagineExtension extends \Twig_Extension
{
    /**
     * @var CacheManager $cacheManager
     */
    protected $cacheManager;

    /**
     * ImagineFilterExtension constructor.
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('custom_imagine_filter', [$this, 'customImagineFilter'])
        ];
    }


    /**
     * @param string $relativePath
     * @param string $liipImageFilter
     * @return \Twig_Markup
     */
    public function customImagineFilter($relativePath, $liipImageFilter = 'view')
    {
        $sourcePath = $this->cacheManager
            ->getBrowserPath(
                $relativePath,
                $liipImageFilter
            );

        return new \Twig_Markup(
            $sourcePath,
            'utf8'
        );

    }


}