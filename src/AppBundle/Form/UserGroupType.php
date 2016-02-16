<?php

namespace AppBundle\Form;

use Glifery\EntityHiddenTypeBundle\Form\Type\EntityHiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('group', EntityHiddenType::class, [
            'class' => 'AppBundle:Groups'
        ]);
        $builder->add('user', EntityHiddenType::class, [
            'class' => 'AppBundle:User'
        ]);
        $builder
            ->add('save', SubmitType::class, array('label' => 'Add User'));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\UserGroup'
        ));

    }
}
