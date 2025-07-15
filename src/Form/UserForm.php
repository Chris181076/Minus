<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email'
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone'
            ])
            ->add('children', EntityType::class, [
            'class' => Child::class,
            'choice_label' => function (Child $child) {
            return $child->getFirstName() . ' ' . $child->getLastName();
            },   
            'multiple' => true,          
            'expanded' => false,         // true = checkbox, false = select multiple
            'required' => false,
            ])
    
            ->add('relationship', TextType::class, [
                'label' => 'Lien avec l’enfant',
                'required' => false
            ])
            ->add('roles', ChoiceType::class, [
            'choices' => [
            'Parent' => 'ROLE_PARENT',
            'Admin' => 'ROLE_ADMIN',
            'Éducateur' => 'ROLE_EDUCATEUR',
            ],
            'multiple' => true,       
            'expanded' => true,      // false = menu déroulant, true = cases à cocher
            'label' => 'Rôles',
    ]);
        ;
    }
}