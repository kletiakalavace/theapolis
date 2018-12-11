<?php

namespace Theaterjobs\CategoryBundle\Form\Extension;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Creates a category choice list.
 *
 * @category Extension
 * @package  Theaterjobs\CategoryBundle\Form\Extension\ChoiceList
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\Service("theaterjobs_category.form.extension.choicelistfactory")
 */
class CategoryChoiceListFactory {

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;

    /**
     * @DI\InjectParams({
     *  "om" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     */
    public function __construct(ObjectManager $om) {
        $this->om = $om;
    }

    /**
     * Returns a choicelist for given Category Root Title.
     *
     * @param string $rootSlug
     * @return ObjectChoiceList
     */
    public function getChoiceList($rootSlug) {
        $choiceList = $this->om->getRepository(
                        'TheaterjobsCategoryBundle:Category'
                )->findChoiceListBySlug($rootSlug);
        return $this->createChoiceList($choiceList);
    }

    /**
     * Creates an ObjectChoiceList.
     *
     * @param array $choiceList
     * @return ObjectChoiceList
     */
    private function createChoiceList(array $choiceList) {
        return new ObjectChoiceList($choiceList, 'title', [], null, 'id');
    }

}
