<?php

namespace Theaterjobs\InserateBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\InserateBundle\Entity\Organization;
use Theaterjobs\UserBundle\Entity\User;

/**
 * InsearteVoter
 *
 * @category Abstract Voter
 * @package  Theaterjobs\InserateBundle\Security
 * @author   Jurgen Rexhmati
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_inserate.organization_voter", public=false)
 * @DI\Tag("security.voter")
 */
class OrganizationVoter extends Voter
{

    const CREATE = 'create_organization';
    const EDIT = 'edit_organization';
    const VIEW = 'view_organization';
    const ACCESS = 'access_organization';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::ACCESS, self::CREATE))) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var Organization $organization */
        $organization = $subject;

        switch ($attribute) {
            case self::VIEW:
                ;
                return $this->canView($user);
            case self::EDIT:
                ;
                return $this->canEdit($user);
            case self::CREATE:
                ;
                return $this->canCreate($user);
            case self::ACCESS:
                return $this->canAccess($user);
        }

        return false;
    }

    private function canView(User $user)
    {
    }

    private function canEdit(User $user)
    {
    }

    private function canCreate(User $user)
    {
    }

    private function canAccess(User $user)
    {
    }
}
