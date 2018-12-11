<?php

namespace Theaterjobs\InserateBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;
/**
 * Organization Form Type
 *
 * @category Form
 * @package  Theaterjobs\MainBundle\Form\Type
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\FormType
 */
class OrganizationDescriptionType extends AbstractType
{
    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var EntityManager
     */
    public $em;

    /**
     * (non-PHPdoc)
     * @param FormBuilderInterface $builder The form builder.
     * @param array $options An arrray with options.
     *
     * @see \Symfony\Component\Form\AbstractType::buildForm()
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', 'textarea', array(
                    'label' => 'form.label.organization.description',
                    'required' => false
                )
            );

    }

    /**
     * (non-PHPdoc)
     * @param OptionsResolver $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\InserateBundle\Entity\Organization',
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_form_organization_descr';
    }

}
