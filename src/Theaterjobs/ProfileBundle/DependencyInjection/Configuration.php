<?php

namespace Theaterjobs\ProfileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('theaterjobs_profile');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
                ->children()
                ->arrayNode('categories')
                ->children()
                ->arrayNode('root_slugs')
                ->children()
                ->variableNode('profile')->defaultNull()->end()
                ->variableNode('voice')->defaultNull()->end()
                ->variableNode('drive_licence')->defaultNull()->end()
                ->end()
                ->end()
                ->end()
                ->end()
                ->end();

        return $treeBuilder;
    }

}
