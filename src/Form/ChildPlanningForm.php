<?php

namespace App\Form;

use App\Entity\Child;
use App\Form\PlannedPresenceForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChildPlanningForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plannedPresences', CollectionType::class, [
            'entry_type' => PlannedPresenceForm::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Child::class, // ← c'est ici que le lien se fait avec ton contrôleur
        ]);
    }
}
