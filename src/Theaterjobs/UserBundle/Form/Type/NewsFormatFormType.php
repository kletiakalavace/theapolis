<?php

namespace Theaterjobs\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * NewsFormat Form Type
 *
 * @category Form
 * @package  Theaterjobs\ProfileBundle\Form
 * @author   Jana Kaszas <jana@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 *
 * @DI\FormType
 */
class NewsFormatFormType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('news_format', 'choice', array(
            'choice_list' => new ChoiceList(array(
                'html' => 'HTML',
                'mobile' => 'MOBILE',
                'text' => 'TEXT')
            )
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'cascade_validation' => false,
                )
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     *
     * @return string
     */
    public function getName() {
        return 'tj_user_form_news_format';
    }

}
