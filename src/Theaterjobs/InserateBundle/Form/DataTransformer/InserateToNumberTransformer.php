<?php

namespace Theaterjobs\InserateBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Theaterjobs\InserateBundle\Entity\Inserate;

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
 */
class InserateToNumberTransformer implements DataTransformerInterface {

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om) {
        $this->om = $om;
    }

    /**
     * Transforms an object (inserate) to a string (id).
     *
     * @param  Inserate|null $inserate
     * @return string
     */
    public function transform($inserate) {
        if (null === $inserate) {
            return "";
        }

        return $inserate->getId();
    }

    /**
     * Transforms a string (id) to an object (inserate).
     *
     * @param  string $id
     *
     * @return Inserate|null
     *
     * @throws TransformationFailedException if object (inserate) is not found.
     */
    public function reverseTransform($id) {
        if (!$id) {
            return null;
        }

        $inserate = $this->om
                ->getRepository('TheaterjobsInserateBundle:Inserate')
                ->findOneBy(array('id' => $id))
        ;

        if (null === $inserate) {
            throw new TransformationFailedException(sprintf(
                    'An inserate with id "%s" does not exist!', $id
            ));
        }

        return $inserate;
    }

}
