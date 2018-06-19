<?php

namespace Theaterjobs\ProfileBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TheaterjobsProfileExtension extends Extension
{

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/'));
        $loader->load('services.yml');

        $container->setParameter(
            'theaterjobs_profile.category.profile.root_slug', $config['categories']['root_slugs']['profile']
        );
        $container->setParameter(
            'theaterjobs_profile.category.voice.root_slug', $config['categories']['root_slugs']['voice']
        );
        $container->setParameter(
            'theaterjobs_profile.drive.licence.root_slug', $config['categories']['root_slugs']['drive_licence']
        );
    }

}
