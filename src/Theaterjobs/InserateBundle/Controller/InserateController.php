<?php

namespace Theaterjobs\InserateBundle\Controller;

use Theaterjobs\MainBundle\Controller\BaseController;

/**
 * The Inserate Controller.
 *
 * @category Controller
 * @package  Theaterjobs\InserateBundle\Controller
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class InserateController extends BaseController {

    /**
     * Creates an array with an User and a choicelist for inserates.
     *
     * @param $rootCategory
     * @return array
     */
    protected function getInserateOptions($rootCategory)
    {
        return [
            'user' => $this->getUser(),
            'category_choice_list' => $this->getCategoryChoiceList($rootCategory),
            'is_admin' => $this->isGranted('ROLE_ADMIN')
        ];
    }

    /**
     * Returns an ObjectChoiceList for given root title.
     *
     * @param string $rootTitle
     * @return \Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList
     */
    protected function getCategoryChoiceList($rootTitle)
    {
        $factory = $this->get("theaterjobs_category.form.extension.choicelistfactory");
        return $factory->getChoiceList($rootTitle);
    }

}
