<?php

namespace Theaterjobs\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CategoryType extends AbstractType
{
	private $em;

	public function __construct($em)
	{
		$this->em = $em;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$catId = $options['catId'];
    	$em = $this->em;

        $builder
            ->add('title')
            ->add('parent','entity',array(
                'class' => 'Theaterjobs\CategoryBundle\Entity\Category',
                'property' => 'title',
                'empty_value' => 'form.choice.category.root',
                'query_builder' => function($er) use ($catId) {
                    $qb = $er->createQueryBuilder('c')
                            ->leftJoin('c.parent','p')
                             ->where('c.id <> ' . $catId)
                             ->andwhere('c.lvl < 2')
                            ->andWhere('p.title <> :news')
                            ->andWhere('p.title<> :forum')
                            ->orWhere('c.parent IS NULL')
                            ->setParameter('news','categories of news')
                            ->setParameter('forum','categories of forum');
                    return $qb;         
                }
                ))
            ->add('description')
            ->add('requiresAge','checkbox',array(
                'required' => false
            ))
            ->add('isPerformanceCategory','checkbox');
        ;
    }

    private function getChoices($catId)
    {
    	 $em = $this->em;

		$qb = $em->getRepository('TheaterjobsCategoryBundle:Category')->createQueryBuilder('c')
        		 ->where('c.id <> ' . $catId)
        		 ->andwhere('c.lvl < 2')
                        ->andWhere('c.parent != :news')
                            ->setParameter('news','categories of news');
        	 	
        $result = $qb->getQuery()->getResult();	
       
			$f = array();

        foreach($result as $r)             	
        {
        	$title = $r->getTitle();
        	$r->setTitle($title);
        	
        }
      //  exit;
        return $result;

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Theaterjobs\CategoryBundle\Entity\Category',
            'catId' => 0
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'theaterjobs_categorybundle_category';
    }
}
