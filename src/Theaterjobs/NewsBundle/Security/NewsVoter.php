<?php

namespace Theaterjobs\NewsBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\NewsBundle\Entity\News;
use Theaterjobs\UserBundle\Entity\User;

/**
 * InsearteVoter
 *
 * @category Abstract Voter
 * @package  Theaterjobs\InserateBundle\Security
 * @author   Jurgen Rexhmati
 * @DI\Service("theaterjobs_news.news_voter", public=false)
 * @DI\Tag("security.voter")
 */
class NewsVoter extends Voter
{

    const CREATE = 'create_news';
    const EDIT = 'edit_news';
    const VIEW = 'view_news';
    const DELETE = 'delete_news';

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
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CREATE]);
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
        /** @var News $news */
        $news = $subject;

        $user = $token->getUser();

        switch ($attribute) {
            case self::VIEW:
                ;
                return $this->canView($news, $user);
            case self::EDIT:
                ;
                return $this->canEdit($user);
            case self::CREATE:
                ;
                return $this->canCreate($user);
            case self::DELETE:
                return $this->canDelete($user);
        }

        return false;
    }

    private function canView(News $news, $user)
    {
        return $news->getPublished() || $user instanceof  User && $user->hasRole(User::ROLE_ADMIN);
    }

    private function canEdit($user)
    {
        return $user instanceof User && $user->hasRole(User::ROLE_ADMIN);
    }

    private function canCreate($user)
    {
        return $user instanceof User && $user->hasRole(User::ROLE_ADMIN);
    }

    private function canDelete($user)
    {
        return $user instanceof User && $user->hasRole(User::ROLE_ADMIN);
    }
}
