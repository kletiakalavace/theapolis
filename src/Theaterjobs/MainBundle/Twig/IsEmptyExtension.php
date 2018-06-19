<?php

namespace Theaterjobs\MainBundle\Twig;

use Theaterjobs\InserateBundle\Entity\AdminComments;
use Theaterjobs\InserateBundle\Model\OrganizationSearch;
use Theaterjobs\NewsBundle\Entity\Replies;
use Theaterjobs\NewsBundle\Model\NewsSearch;
use Theaterjobs\UserBundle\Entity\AdminUserComments;

/**
 * Check if an array is empty
 *
 * @category Twig
 * @package  Theaterjobs\MainBundle\Twig
 */
class IsEmptyExtension extends \Twig_Extension
{

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_empty', array($this, 'isEmptyFunction')),
            new \Twig_SimpleFunction('get_admin_comment', [$this, 'getAdminComment'])

        );
    }

    /**
     * Twig filter, returns true if an arr is empty
     *
     * @param \ArrayIterator $array
     *
     * @return bool
     */
    public function isEmptyFunction($array, $formData = null)
    {
        return $this->_isEmpty($array, $formData);
    }

    /**
     * Check if an form vars object with sub arr is empty
     *
     * @param \ArrayIterator $obj
     *
     * @return bool
     */
    private function _isEmpty($obj, $formData)
    {
        $whitelist = ['sortChoices', 'published', 'area', 'page'];
        foreach ($obj as $key => $val) {
            if (in_array($key, $whitelist)) {
                continue;
            }
            if (!empty($val->vars['value'])) {
                return false;
            };
        }
        if ($formData) {
            if (!($formData instanceof OrganizationSearch) && !($formData instanceof NewsSearch) && !empty($formData->getCategory())) {
                return false;
            } else if  (($formData instanceof NewsSearch) && !empty($formData->getCategories())) {
                return false;
            }

            if (!($formData instanceof OrganizationSearch) && !empty($formData->getOrganization())) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param AdminUserComments|Replies $entity
     * @return array
     */
    public function getAdminComment($entity)
    {
        $comment = [];

        if ($entity instanceof Replies) {
            $comment['profile'] = $entity->getProfile();
            $comment['description'] = $entity->getComment();
            $comment['publishedAt'] = $entity->getDate();
            return $comment;
        }
        if ($entity instanceof AdminUserComments) {
            $comment['profile'] = $entity->getAdmin()->getProfile();
            $comment['description'] = $entity->getDescription();
            $comment['publishedAt'] = $entity->getPublishedAt();
            return $comment;
        }
        if ($entity instanceof AdminComments) {
            $comment['profile'] = $entity->getUser()->getProfile();
            $comment['description'] = $entity->getDescription();
            $comment['publishedAt'] = $entity->getPublishedAt();
            return $comment;
        }
    }


    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'is_empty';
    }

}
