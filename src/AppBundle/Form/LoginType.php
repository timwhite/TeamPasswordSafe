<?php

namespace AppBundle\Form;

use AppBundle\Repository\GroupsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;

class LoginType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var GroupsRepository $group_repo */
        $group_repo = $options['groups_repository'];

        $builder
            ->add('group', EntityType::class, [
                'class' => 'AppBundle\Entity\Groups',
                'choices' => $group_repo->getByUser($options['current_user'])
            ])
            ->add('name')
            ->add('url')
            ->add('username')
            ->add('plainPassword', TextType::class, [
                'mapped' => false,
                'attr' => [
                    'maxlength' => 1024,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Length(['max' => 1024])
                ]
            ])
            ->add('notes')
            ->add('save', SubmitType::class, array('label' => 'Update Login'))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Login'
        ));
        $resolver->setRequired([
            'current_user',
            'groups_repository'
        ]);
    }
}
