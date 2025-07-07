<?php

namespace App\Form;

use App\Entity\PlannedPresence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\ChildForm;


class PlannedPresenceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('week_day', TextType::class)
            ->add('arrival_time', TimeType::class, [
                'widget' => 'single_text', 
                'required' => false, 
                'empty_data' => null,
                'input' => 'datetime',])
            ->add('departure_time', TimeType::class, [
                'widget' => 'single_text', 
                'required' => false, 
                'empty_data' => null,
                'input' => 'datetime',]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlannedPresence::class,
        ]);
    }
}
