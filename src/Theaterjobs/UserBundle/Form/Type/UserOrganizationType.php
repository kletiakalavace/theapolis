<?php

namespace Theaterjobs\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * UserOrganization Form Type
 *
 * @category Form
 * @package  Theaterjobs\UserBundle\Form
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\FormType
 */
class UserOrganizationType extends AbstractType
{
    /** @DI\Inject("theaterjobs_user.form.user_organization_transformer") */
    public  $userTranformer;

    /** @DI\Inject("translator") */
    public $trans;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('user', 'text', [
            'label' => 'organization.edit.label.addMember',
            'required' => true,
            'mapped' => true,
            'attr' => [
                'class' => 'user-input',
                'placeholder' => false,
            ],
            'constraints' => [
                new Assert\Callback([$this, 'validateUserField'])
            ]
        ]);
        $builder->get('user')->addModelTransformer($this->userTranformer);
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function validateUserField($value, ExecutionContextInterface $context)
    {
        if (!$value) {
            $context->buildViolation($this->trans->trans('user.user_organization_type.user.doesnt.exists'))->addViolation();
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms',
            'data_class' => 'Theaterjobs\UserBundle\Entity\UserOrganization',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'theaterjobs_userbundle_userorganization';
    }

}
