<?php

namespace Theaterjobs\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The theaterjobs user bundle
 *
 * @category UserBundle
 * @package  Theaterjobs\UserBundle
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class TheaterjobsUserBundle extends Bundle
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::getParent()
     *
     * @return string
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
