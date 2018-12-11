<?php

namespace Theaterjobs\CategoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Category Form Type
 *
 * @category Form
 * @package  Theaterjobs\CategoryBundle\Form\Type
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @DI\FormType
 */
class CategoryType extends AbstractType
{

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\AbstractType::getParent()
     *
     * @return string
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->isRequired('choice_list');
        $resolver->setAllowedTypes([
            'choice_list' => 'Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList',
        ]);

        $resolver->setDefaults([
            'translation_domain' => 'forms',
            //'choice_list' => $this->categoryChoiceList,
            'multiple' => true,
            'group_by' => 'parent.title',
            'property' => 'title',
            //'translation_property' => 'title',
            'class' => 'Theaterjobs\CategoryBundle\Entity\Category',
            'required' => true,
            'label' => 'work.new.label.category',
            'empty_value' => 'form.category.choice.empty_value',
            'attr' => [
                'class' => 'tag-name-input disabled-titlejob placeholder-entercharact'
            ]
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'theaterjobs_category_category';
    }

    /**
     * @return string
     * If you want to create types that are compatible with Symfony 2.3 up to 2.8
     * and don't trigger deprecation errors, implement *both* 'getName()' and 'getBlockPrefix()'
     */
    public function getName()
    {
        return 'theaterjobs_category_category';
    }

}
