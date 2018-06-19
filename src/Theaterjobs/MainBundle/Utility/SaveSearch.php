<?php

namespace Theaterjobs\MainBundle\Utility;

use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MainBundle\Entity\SaveSearch as SaveSearchEntity;

/**
 * Class SaveSearch
 * @package Theaterjobs\MainBundle\Utility
 * @DI\Service("theaterjobs.main_bundle.save_search")
 */
class SaveSearch
{

    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $em;

    /** @DI\Inject("%job_status%") */
    public $job_status;

    /** @DI\Inject("%organization_status%") */
    public $orga_status;

    /**
     * @param SaveSearchEntity $saveSearch
     * @param array url json Params
     * @return  array
     */
    public function getParamsArr(SaveSearchEntity $saveSearch)
    {
        $params = $saveSearch->getParams();
        foreach ($params as $key => $param) {
            // Get tag names
            $tagName = $this->getTagName($saveSearch, $key, $param);
            if ($tagName) {
                $params[$key] = $tagName;
                // If there is no tag, then don't display it
            } else
                unset($params[$key]);
        }
        return $params;
    }


    /**
     * © JAMNANA
     * Get tag name for a search param
     * @param SaveSearchEntity $search param key
     * @param string $name param key
     * @param string | array $value param value
     * @return string
     */
    private function getTagName($search, $name, $value)
    {
        $getBy = is_array($value) ? 'getByIds' : 'getById';

        switch ($name){
            case 'subcategories' :
                return $this->$getBy('TheaterjobsCategoryBundle:Category', 'getTitle', $value);

            case 'category' :
                return $this->getBySlug('TheaterjobsCategoryBundle:Category', 'getTitle', $value);

            case 'status' :
                return $this->getStatusByTag($search, is_array($value) ? $value : [$value]);

            case 'organizationSection' :
                return $this->$getBy('TheaterjobsInserateBundle:OrganizationSection', 'getName', $value);

            case 'organization' :
                return $this->$getBy('TheaterjobsInserateBundle:Organization', 'getName', $value);

            case 'organizationKind' :
                return $this->$getBy('TheaterjobsInserateBundle:OrganizationKind', 'getName', $value);

            case 'gratification' :
                return $this->$getBy('TheaterjobsInserateBundle:Gratification', 'getName', $value);

            case 'tags' :
                return implode(', ', explode(',', $value));

            default:
                return is_array($value) ?  implode(', ', $value) : $value;
        }
    }


    /**
     * Get values imploded by coma
     * @param $entity
     * @param $getByField
     * @param $id
     * @return string
     */
    private function getById($entity, $getByField, $id)
    {
        $entity = $this->em->getRepository($entity)->findOneById($id);
        return $entity ? $entity->$getByField() : null;
    }

    /**
     * Get values imploded by coma
     * @param $entity
     * @param $getByField
     * @param $ids
     * @return string
     */
    private function getByIds($entity, $getByField, $ids)
    {
        return implode(', ', array_reduce($this->em->getRepository($entity)->findById($ids), function ($acc, $item) use ($getByField) {
            $acc[] =  $item->$getByField();
            return $acc;
        }, []));
    }

    /**
     * Get values imploded by coma
     * @param $entity
     * @param $getByField
     * @param $slug
     * @return string
     */
    private function getBySlug($entity, $getByField, $slug)
    {
        $entity = $this->em->getRepository($entity)->findOneBySlug($slug);
        return $entity ? $entity->$getByField() : null;
    }

    /**
     * Get job|organization status based on search type and their ids
     * @param SaveSearchEntity $search
     * @param $value
     * @return string
     */
    private function getStatusByTag($search, $value)
    {
        $statuses = $search->isJobEntity() ? $this->job_status : $this->orga_status;
        return implode(', ', array_reduce($value, function ($acc, $item) use ($statuses) {
            $acc[] = $statuses[$item];
            return $acc;
        }, []));
    }


    /**
     * Remove from params white listed parameters from search entity
     * @param array $params
     * @return string
     */
    public function removeWhiteListed($params)
    {
        // Field names of params
        $fields = array_keys($params);
        // Fields that aren't on whitelist
        $unique = array_diff($fields, SaveSearchEntity::WHITE_LIST);
        $data = [];
        foreach ($unique as $item) {
            $data[$item] = $params[$item];
        }
        // Bug on save searches areas comes on each request until we fix that we remove area manually if the location is not present
        // @TODO remove this when area is fixed
        if (isset($data['area']) && !isset($data['location'])) {
            unset($data['area']);
        }
        return json_encode($data);
    }
}