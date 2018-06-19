<?php

namespace Theaterjobs\MembershipBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('theaterjobs_membership');

        $rootNode
            ->children()
            ->scalarNode('default_tax_rate_country')->defaultValue('DE')->end()
            ->scalarNode('expires_in')->defaultValue('P14D')->end()
            ->scalarNode('profile_class')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('email_from')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('sepaapi_username')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('sepaapi_code')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('sepa_iban')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('sepa_bic')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('sepa_name')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('sepa_creditor_id')->isRequired()->cannotBeEmpty()->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

}
