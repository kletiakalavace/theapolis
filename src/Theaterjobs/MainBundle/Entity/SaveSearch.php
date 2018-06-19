<?php

namespace Theaterjobs\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\ProfileBundle\Entity\Profile;

/**
 * SaveSearch
 *
 * @ORM\Table(name="tj_save_searches")
 * @ORM\Entity(repositoryClass="Theaterjobs\MainBundle\Entity\SaveSearchRepository")
 */
class SaveSearch
{
    use TimestampableEntity;

    const ONCE_A_DAY = 1;
    const NEVER = 2;
    const LIMIT = 20;

    const VALID_ENTITIES = [
        'organization' => Organization::class,
        'profile' => Profile::class,
        'news' => News::class,
        'job' => Job::class
    ];

    const WHITE_LIST = [
        'createMode',
        'applied',
        'jobFavourites',
        'sortBy',
        'jobApplications',
        'newsFavourites',
        'sortChoices',
        'organizationFavourites',
        'myOrganizations',
        'isVisibleInList',
        'defaultStatus',
        'forUser',
        'savedSearch',
        'favorite',
        'jobApplications',
        'user',
        'savedSearch',
        'page',
        'categories',
        'userFavourites',

    ];

    const CATEGORY_SLUG = 'category';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Theaterjobs\ProfileBundle\Entity\Profile", inversedBy="searches")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    private $profile;

    /**
     * @var string
     *
     * @ORM\Column(name="params", type="json_array")
     */
    private $params;

    /**
     * @var integer
     *
     * @ORM\Column(name="notification", type="integer")
     */
    private $notification = self::ONCE_A_DAY;

    /**
     * @var integer
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    private $entity;

    /**
     * @var string
     *
     * @ORM\Column(name="route_name", type="string", length=255)
     */
    private $routeName;

    /**
     * @var string | null
     *
     * @ORM\Column(name="category_slug", type="string", length=255, nullable=true)
     */
    private $categorySlug = null;

    /**
     * Not mapped by doctrine only for internal usage
     * @var array
     */
    private $paramsArr;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return array | string
     * @param $json
     */
    public function getParams($json = true)
    {
        if ($json) {
            $paramsArr = json_decode($this->params, true);
            if ($this->categorySlug) {
                $paramsArr['category'] = $this->categorySlug;
            }
            return $paramsArr;
        }
        return $this->params;
    }

    /**
     * @param string $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return int
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param int $notification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return int
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param int $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return int
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param int $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * @return int
     */
    public function getCategorySlug()
    {
        return $this->categorySlug;
    }

    /**
     * @param int $categorySlug
     */
    public function setCategorySlug($categorySlug)
    {
        $this->categorySlug = $categorySlug;
    }

    /**
     * Get params field as array
     * @return array
     */
    public function getParamsArr()
    {
        return $this->paramsArr;
    }

    /**
     * @param $arr
     */
    public function setParamsArr($arr)
    {
        $this->paramsArr = $arr;
    }

    /**
     * Get job instead of Theaterjobs\..\Jobs
     * Backward Compatibility
     */
    public function getShortEntity()
    {
        return array_search($this->entity, self::VALID_ENTITIES);
    }

    /**
     * @return bool
     */
    public function isJobEntity()
    {
        return $this->entity === Job::class;
    }

    /**
     * @return bool
     */
    public function isOrganizationEntity()
    {
        return $this->entity === Organization::class;
    }

    /**
     * @return bool
     */
    public function isProfileEntity()
    {
        return $this->entity === Profile::class;
    }

    /**
     * @return bool
     */
    public function isNewsEntity()
    {
        return $this->entity === News::class;
    }

    /**
     * @return string
     */
    public function getPlainParams()
    {
        return $this->params;
    }
}

