<?php

namespace App\Form;

use App\Entity\ChildPresence;
use App\Entity\Semainier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChildPresenceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('day')
            ->add('present')
            ->add('arrival_time')
            ->add('departure_time')
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
