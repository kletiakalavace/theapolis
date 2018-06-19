<?php

namespace Theaterjobs\MainBundle\Utility;

/**
 * The logger interface.
 *
 * @category Utility
 * @package  Theaterjobs\MainBundle\Utility
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
interface LoggerInterface
{
    /**
     * Get the id.
     */
    public function getId();

    /**
     * Get the class name.
     */
    public function getClassName();

    /**
     * Get the log messages.
     */
    public function getLogMessages();

    /**
     * Returns the Id of this alias.
     */
    public function __toString();
}
