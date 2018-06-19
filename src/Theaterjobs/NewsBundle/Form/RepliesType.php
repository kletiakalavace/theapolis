<?php

namespace Theaterjobs\NewsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * News Form Type
 *
 * @DI\FormType
 */
class RepliesType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('comment',TextareaType::class, array(
                    'label' => false,
                    'attr' => array(
                        /*'placeholder' => 'news.show.placeholder.leaveComment',*/
                    )
                        )
                );
                /*->add('useForumAlias',CheckboxType::class,array(
                    'label' =>'form.label.comment_with_forumAlias'
                ));*/
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\NewsBundle\Entity\Replies'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'tj_news_form_replies';
    }

}
