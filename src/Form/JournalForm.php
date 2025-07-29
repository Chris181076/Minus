<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Journal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Entity\ChildPresence;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\JournalEntryForm;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class JournalForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['readonly' => true],
                'label' => false,
                'input' => 'datetime_immutable',
            ])
            
                 ->add('entries', CollectionType::class, [
            'entry_type' => JournalEntryForm::class, // Doit pointer vers le bon form
            'entry_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Journal::class,
        ]);
    }
}
