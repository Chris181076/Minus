<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('phoneNumber')
            ->add('firstName')
            ->add('lastName')
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('sentMessages', EntityType::class, [
                'class' => Message::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('receivedMessages', EntityType::class, [
                'class' => Message::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('children', EntityType::class, [
                'class' => Child::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
