<?php

namespace Theaterjobs\InserateBundle\Entity;

use Doctrine\Common\Persistence\ObjectRepository;
use Theaterjobs\InserateBundle\Model\UserInterface;

/**
 * Repository for the Gratification.
 *
 * @category Repository
 * @package  Theaterjobs\InserateBundle\Entity
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface UserOrganizationRepositoryInterface extends ObjectRepository {

    public function getQueryBuilderByUser(UserInterface $user);
}
