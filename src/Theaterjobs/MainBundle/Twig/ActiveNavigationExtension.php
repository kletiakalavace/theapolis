<?php

namespace Theaterjobs\MainBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ActiveNavigationExtension
 * @package Theaterjobs\MainBundle\Twig
 */
class ActiveNavigationExtension extends \Twig_Extension
{

    /** @var RequestStack $request */
    private $request;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param RequestStack $request
     */
    public function setRequest( RequestStack $request )
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('active_nav', array($this, 'activeNavigation')),
        );
    }

    /**
     * Returns 1 if $path matches with request navigation
     * Returns false if $path doesn't match
     * Returns 2 if $path matches with profile and request navigation profile slug is the same as $path
     *
     * @param $path
     * @return integer | boolean
     */
    public function activeNavigation($path)
    {
        $uri = $this->request->getMasterRequest()->getPathInfo();
        $reqNav = explode('/', $uri);
        $nav = explode('/', $path);

        if (count($reqNav) > 2 && count($nav) > 2 && $reqNav[2] === $nav[2]) {
            if ($reqNav[2] === 'people') {
                if ($this->tokenStorage->getToken()) {
                    $token = $this->tokenStorage->getToken();
                    if ($token) {
                        $user = $token->getUser();
                        if ($user instanceOf User) {
                            $linkSlug = $reqNav[count($reqNav) - 1];
                            $userSlug = $user->getProfile()->getSlug();
                            //His profile
                            return $userSlug === $linkSlug ? 2 : 1;
                        }
                    }
                }
                //Profile navigation match
                return 1;
                }
            //Basic navigation match
            return 1;
        }

        //Support accountSettings route
        if (count($reqNav) > 3 && $reqNav[2] == 'account' && $reqNav[3] == 'settings') {
            return 2;
        }
        return false;
    }
}