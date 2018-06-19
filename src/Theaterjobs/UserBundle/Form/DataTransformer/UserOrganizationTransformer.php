<?php

namespace Theaterjobs\UserBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Entity\UserOrganization;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Inserate Form DataTransformer
 *
 * @category DataTransformer
 * @package  Theaterjobs\InserateBundle\Form\DataTransformer
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\Service("theaterjobs_user.form.user_organization_transformer")
 */
class UserOrganizationTransformer implements DataTransformerInterface
{

    /**
     * @DI\Inject("fos_elastica.manager")
     */
    public $fos_elastica_manager;

    /** @DI\Inject("fos_elastica.finder.theaterjobs.profile") */
    public $profileFinder;

    /**
     * Transforms an object (inserate) to a string (id).
     *
     * @param  User|null $user
     * @return string
     */
    public function transform($user)
    {
        if (null === $user) {
            return "";
        }

        return $user->getEmail();
    }

    /**
     * Transforms a string (id) to an object (inserate).
     *
     * @param  string $email
     *
     * @return UserOrganization|null
     *
     * @throws TransformationFailedException if object (inserate) is not found.
     */
    public function reverseTransform($email)
    {
        if (!$email) {
            return null;
        }
        $query = $this->fos_elastica_manager->getRepository(Profile::class)->getUserByEmail($email);
        $results = $this->profileFinder->find($query);
        if ($results) {
            return $results[0]->getUser();
        }
        return null;
    }

}
