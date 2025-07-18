<?php

namespace App\Form;

use App\Entity\Allergy;
use App\Entity\Child;
use App\Entity\Group;
use App\Entity\Icon;
use App\Entity\SpecialDiet;
use App\Entity\User;
use App\Form\PlannedPresenceForm;
use App\Entity\PlannedPresence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\UserForm;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ChildForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom de famille'
            ])
            ->add('birthDate', null, [
                'widget' => 'single_text',
            ])
          
            ->add('medicalNotes');
            if($options['is_admin']){
            $builder->add('created_at', null, [
                'widget' => 'single_text',
            ]);
            }
        
        $builder
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
                'label' => "Choix de l'icône",
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'icon-widget'],
                'choice_attr' => function($icon) {
            return ['data-image' => $icon->getPath()];
            },
            'block_prefix' => 'icon',
            ]);
            if($options['is_admin']){
            $builder->add('childGroup', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
            ])
            
            ->add('plannedPresences', CollectionType::class, [
            'entry_type' => PlannedPresenceForm::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'label' => false,
            ]);
        }
           
        }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Child::class,
            'is_admin' => false,
        ]);
    }
}
