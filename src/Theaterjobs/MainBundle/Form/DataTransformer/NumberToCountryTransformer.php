<?php

namespace Theaterjobs\MainBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Number to country Datatransformer.
 *
 * @category DataTransformer
 * @package  Theaterjobs\MainBundle\Form\DataTransformer
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class NumberToCountryTransformer implements DataTransformerInterface
{

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
     * (non-PHPdoc)
     * @param string $country
     *
     * @see \Symfony\Component\Form\DataTransformerInterface::transform()
     *
     * @return integer
     */
    public function transform($country) {
        if (null === $country) {
            return "";
        }

        return $country->getId();
    }

    /**
     * (non-PHPdoc)
     * @param integer $id
     *
     * @see \Symfony\Component\Form\DataTransformerInterface::reverseTransform()
     *
     * @return mixed string|null
     */
    public function reverseTransform($id) {
        if (!$id) {
            return null;
        }

        $country = $this->om
            ->getRepository('TheaterjobsMainBundle:Country')
            ->find($id);

        if (null === $country) {
            throw new TransformationFailedException(
            sprintf(
                'An issue with number "%s" does not exist!', $number
            )
            );
        }

        return $country;
    }

}
