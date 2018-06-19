<?php

namespace Theaterjobs\InserateBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\UserBundle\Entity\User;

/**
 * InsearteVoter
 *
 * @category Abstract Voter
 * @package  Theaterjobs\InserateBundle\Security
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_inserate.inserate_voter", public=false)
 * @DI\Tag("security.voter")
 */
class JobVoter extends Voter
{

    const CREATE = 'create_job';
    const EDIT = 'edit_job';
    const VIEW = 'view_job';
    const ACCESS = 'access_job';

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
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::ACCESS])) {
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
        /** @var Job $job */
        $job = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($user, $job);
            case self::EDIT:
                return $this->canEdit($job, $user);
            case self::CREATE:
                return $this->canCreate($user);
            case self::ACCESS:
                return $this->canAccess($job, $user);
        }

        return false;
    }

    private function canView(User $user, Job $job)
    {
        $isAdmin = $user->hasRole(User::ROLE_ADMIN);

        // Is Admin
        if ($isAdmin) return true;

        $organization = $job->getOrganization();
        $userOrga = $job->getUser();
        $isJobCreator = $userOrga && $user->getId() === $userOrga->getId();
        $isTeamMember = $organization && $organization->isTeamMember($user);
        // return false if published & !job_creator & !team_member & !member
        return !($job->isPublished() && !$isJobCreator && !$isTeamMember && !$user->hasRole(User::ROLE_MEMBER));
    }

    private function canEdit(Job $job, User $user)
    {
        $organization = $job->getOrganization();
        $userOrga = $job->getUser();

        $isAdmin = $user->hasRole(User::ROLE_ADMIN);
        $isJobCreator = $userOrga && $user->getId() === $userOrga->getId();
        $isTeamMember = $organization && $organization->isTeamMember($user);

        //According to new user stories for job and education offers, if job is in name of organization, it belongs to organization and not user who created it.
        if ($organization) {
            return $isAdmin || $isTeamMember;
        }
        return $isAdmin || $isJobCreator;
    }

    private function canCreate(User $user)
    {
        return $user->hasRole(User::ROLE_USER);
    }

    private function canAccess(Job $job, User $user)
    {
        $isAdmin = $user->hasRole(User::ROLE_ADMIN);

        // Is Admin
        if ($isAdmin) return true;

        // Not Admin and deleted job
        if ($job->isDeleted()) return false;

        $organization = $job->getOrganization();
        $userOrga = $job->getUser();
        $isJobCreator = $userOrga && $user->getId() === $userOrga->getId();
        $isTeamMember = $organization && $organization->isTeamMember($user);

        // Job not Published and not admin
        if (!$job->isPublished() && !$isJobCreator && !$isTeamMember) return false;

        return true;
    }
}
