<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 08/04/2018
 * Time: 10:13
 */

namespace Theaterjobs\InserateBundle\Twig;

use Elastica\SearchableInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Theaterjobs\InserateBundle\Entity\Organization;

/**
 * Class OrganizationNameExtension
 * @package Theaterjobs\InserateBundle\Twig
 */
class OrganizationNameExtension extends \Twig_Extension
{
    /**
     * @var SearchableInterface $fosEsIndexOrganization
     */
    protected $fosEsIndexOrganization;

    /**
     * @var RepositoryManagerInterface $fosEsRepositoryManager
     */
    protected $fosEsRepositoryManager;

    /**
     * OrganizationNameExtension constructor.
     * @param SearchableInterface $fosEsIndexOrganization
     * @param RepositoryManagerInterface $fosEsRepositoryManager
     */
    public function __construct(SearchableInterface $fosEsIndexOrganization, RepositoryManagerInterface $fosEsRepositoryManager)
    {
        $this->fosEsIndexOrganization = $fosEsIndexOrganization;
        $this->fosEsRepositoryManager = $fosEsRepositoryManager;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('name', [$this, 'getOrganizationName'])
        ];
    }

    /**
     * @param string $slug
     * @return \Twig_Markup
     */
    public function getOrganizationName($slug)
    {
        $query = $this->fosEsRepositoryManager->getRepository(Organization::class)->getOrganizationBySlug($slug);
        $organization = $this->fosEsIndexOrganization->search($query)->current();
        $name = '';

        if ($organization) {
            $name = $organization->__get('name');
        }

        return new \Twig_Markup(
            $name,
            'utf8'
        );

    }

}