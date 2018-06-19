<?php

namespace Theaterjobs\VATBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * TheaterjobsVATBundle
 *
 * Based on https://github.com/ruudk/VATBundle
 *
 * @category Extension
 * @package  Theaterjobs\VATBundle
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class TheaterjobsVATBundle extends Bundle {

    public function build(ContainerBuilder $container) {
        parent::build($container);
    }

}
