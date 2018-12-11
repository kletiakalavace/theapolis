<?php

namespace Theaterjobs\InserateBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Theaterjobs\InserateBundle\Entity\Gratification;

/**
 * Job Form Type
 *
 * @category Form
 * @package  Theaterjobs\InserateBundle\Form
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\FormType
 */
class JobType extends InserateType
{

    /** @DI\Inject("base_mailer") */
    public $baseMailer;

    /** @DI\Inject("security.authorization_checker") */
    public $security;

    /** @DI\Inject("translator") */
    public $trans;

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var EntityManager
     */
    public $em;

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('categories', 'theaterjobs_category_category', array(
                'choice_list' => $options['category_choice_list'],
                'attr' => [
                    'multiple' => false
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ))
            ->add('gratification', GratificationType::class, array(
                'required' => false,
                'attr' => (array('hidden' => true)),
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    $qb = $er->createQueryBuilder('g');
                    return $qb;
                },
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Callback([$this, 'checkEduTypeAccess'])
                ]
            ))
            ->add('hideOrganizationLogo', 'checkbox', array(
                'value' => 1,
                'required' => false,
                'label' => 'form.label.job.hideOrganizationLogo',
            )
        )
            ->add('contact', 'textarea', array(
                    'required' => true,
                    'label' => 'work.new.label.contact',
                    'attr' => array('rows' => 13,
                    ),
                )
            )
            ->add('description', 'textarea', array(
                    'required' => true,
                    'label' => 'work.new.label.description',
                    'attr' => array('rows' => 13,
                    ),
                )
            )
            ->add('email', 'email', array(
                    'required' => false,
                    'label' => 'work.new.label.email',
                    'constraints' => [
                        new Assert\Callback([$this, 'checkEmail'])
                    ]
                )
            )
            ->add('fromAge', 'number', array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'form.placeholder.job.fromAge',
                )))
            ->add('uploadFile', 'vich_file', array(
                'label' => 'news.edit.label.image',
                'required' => false,
                'attr' => ['class' => 'uploadAudioImage', 'accept' => 'image/*'],
                'allow_delete' => false, // not mandatory, default is true
                'download_link' => false,
            ))
            ->add('uploadFileCover', 'vich_file', array(
                'label' => 'news.edit.label.image',
                'required' => false,
                'attr' => ['class' => 'uploadCoverImage', 'accept' => 'image/*'],
                'allow_delete' => false, // not mandatory, default is true
                'download_link' => false,
            ))
            ->add('copyrightText', 'text', array(
                'label' => false,
                'attr' => array('class' => 'year startDate',
                    'placeholder' => 'job.ad.label.coverImage.copyright'
                ),
                'required' => false
            ))
            ->add('toAge', 'number', array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'form.placeholder.job.toAge',
                )))
            ->add('otherApplicationWay', 'checkbox', array(
                'label' => false,
                'required' => false
            ))
            ->add('createMode', 'text', array(
                'label' => false,
                'required' => false
            ))->add('optedForDel', HiddenType::class, array(
                'data' => '[false,false]',
                'mapped'=>false
            ));

    }

    /**
     * (non-PHPdoc)
     * @param OptionsResolver $resolver
     *
     * @see \Symfony\Component\Form\AbstractType::setDefaultOptions()
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array('translation_domain' => 'forms'));
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'tj_inserate_form_job';
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function checkEmail($value, ExecutionContextInterface $context)
    {
        if ($value !== null && $this->baseMailer->checkFalseEmail($value)) {
            $context->buildViolation($this->trans->trans("inserate.form.email.unknown.provider"))->addViolation();
        }
    }

    /**
     * @param Gratification $gratification
     * @param ExecutionContextInterface $context
     */
    public function checkEduTypeAccess($gratification, ExecutionContextInterface $context)
    {
        // In case a non member user is adding an edu type
        $isMember = $this->security->isGranted('ROLE_MEMBER');
        if (!$isMember && $gratification && $gratification->isEduType()) {
            $error = $this->trans->trans('job.edit.please.become.member');
            $context->buildViolation($error)->addViolation();
        }
    }
}
