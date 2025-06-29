<?php

namespace App\Form;

use App\Entity\Allergy;
use App\Entity\Child;
use App\Entity\Group;
use App\Entity\Icon;
use App\Entity\SpecialDiet;
use App\Entity\User;
use App\Form\PlannedPresenceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

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
            ->add('allergies', EntityType::class, [
                'class' => Allergy::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
            ->add('specialDiets', EntityType::class, [
                'class' => SpecialDiet::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
           ->add('icon', EntityType::class, [
                'class' => Icon::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => false,
                'choice_attr' => function($icon) {
            return ['data-image' => $icon->getPath()];
            },
            'block_prefix' => 'icon',
            ])
            
          ->add('childGroup', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
            ])
            ->add('plannedPresences', CollectionType::class, [
                'entry_type' => PlannedPresenceForm::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
        }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Child::class,
        ]);
    }
}
