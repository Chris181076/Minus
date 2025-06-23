<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Journal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ChildPresence;

class JournalForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
                'attr' => ['readonly' => true],
            ])
            ->add('meal')
            ->add('nap')
            ->add('diaper_time')
            ->add('diaper_type')
            ->add('activity')
            ->add('note')
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Journal::class,
        ]);
    }
}
