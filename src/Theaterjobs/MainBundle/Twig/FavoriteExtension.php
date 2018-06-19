<?php

namespace Theaterjobs\MainBundle\Twig;

use Doctrine\Common\Collections\Collection;
use Elastica\Result;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * FavoriteExtension ElasticSearch Results
 *
 * @author Igli Hoxha <igliihoxha@gmail.com>
 */
class FavoriteExtension extends \Twig_Extension
{
    /**
     * @var AssetsHelper $assets
     */
    protected $assets;

    /**
     * @var UrlGeneratorInterface $generator
     */
    protected $generator;

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('favorite', array($this, 'favoriteFilter')),
        );
    }

    /**
     * Constructs a new instance of FavoriteExtension.
     *
     * @param ContainerInterface $container
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(ContainerInterface $container , UrlGeneratorInterface $generator)
    {
        $this->assets = $container->get('templating.helper.assets');
        $this->generator = $generator;
    }

    /**
     * @param Result $result
     * @param Collection|null $arrayCollection
     * @return bool
     */
    public function favoriteFilter(Result $result,Collection $arrayCollection)
    {
        $gifUrl = $this->assets->getUrl('bundles/theaterjobsmain/img/icon-sprite.svg#icon-star');
        $star = "<svg class='icon-svg icon-svg-success' width='30' height='30'><use xlink:href='$gifUrl'></use></svg>";

        foreach ($arrayCollection as $value) {
            echo $result->getId() == $value->getId() ? $star : '';
        }

    }
}