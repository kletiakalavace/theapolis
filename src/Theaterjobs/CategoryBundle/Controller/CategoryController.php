<?php

namespace Theaterjobs\CategoryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Theaterjobs\CategoryBundle\Entity\Category;
use Symfony\Component\HttpFoundation\JsonResponse;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\UserBundle\Event\UserActivityEvent;

/**
 * Category controller.
 *
 * @Route("/")
 */
class CategoryController extends BaseController
{

}
