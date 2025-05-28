<?php

namespace App\Form;

use App\Entity\Allergy;
use App\Entity\Child;
use App\Entity\Group;
use App\Entity\Icon;
use App\Entity\SpecialDiet;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChildForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('birthDate', null, [
                'widget' => 'single_text',
            ])
            ->add('medicalNotes')
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('allergies', EntityType::class, [
                'class' => Allergy::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('specialDiets', EntityType::class, [
                'class' => SpecialDiet::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('Icons', EntityType::class, [
                'class' => Icon::class,
                'choice_label' => 'id',
            ])
            ->add('childGroup', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Child::class,
        ]);
    }
}
