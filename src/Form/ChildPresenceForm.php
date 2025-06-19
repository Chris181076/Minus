<?php

namespace App\Form;

use App\Entity\ChildPresence;
use App\Entity\Semainier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ChildPresenceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('day', DateTimeType::class, [
        'widget' => 'single_text',
    ])
        ->add('arrivalTime', DateTimeType::class, [
        'widget' => 'single_text',
        'required' => false,
    ])
        ->add('departureTime', DateTimeType::class, [
        'widget' => 'single_text',
        'required' => false,
    ])
            ->add('note')
            ->add('semainier', EntityType::class, [
                'class' => Semainier::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChildPresence::class,
        ]);
    }
}
