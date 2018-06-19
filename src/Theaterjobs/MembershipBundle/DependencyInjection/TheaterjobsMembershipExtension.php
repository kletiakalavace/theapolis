<?php

namespace Theaterjobs\MembershipBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TheaterjobsMembershipExtension extends Extension
{

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container) {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->setParams($container, $config);
    }

    /**
     * Set container params from config
     *
     * @param ContainerBuilder $container
     * @param $config
     */
    private function setParams($container, $config)
    {
        $container->setParameter(
            'theaterjobs_membership.default_tax_rate_country', $config['default_tax_rate_country']
        );

        $container->setParameter(
            'theaterjobs_membership.expires_in', $config['expires_in']
        );

        $container->setParameter(
            'theaterjobs_membership.profile_class', $config['profile_class']
        );

        $container->setParameter(
            'theaterjobs_membership.email_from', $config['email_from']
        );

        $container->setParameter(
            'theaterjobs_membership.sepaapi_username', $config['sepaapi_username']
        );
        $container->setParameter(
            'theaterjobs_membership.sepaapi_code', $config['sepaapi_code']
        );

        $container->setParameter(
            'theaterjobs_membership.sepa_iban', $config['sepa_iban']
        );
        $container->setParameter(
            'theaterjobs_membership.sepa_bic', $config['sepa_bic']
        );
        $container->setParameter(
            'theaterjobs_membership.sepa_name', $config['sepa_name']
        );
        $container->setParameter(
            'theaterjobs_membership.sepa_creditor_id', $config['sepa_creditor_id']
        );
    }

}
