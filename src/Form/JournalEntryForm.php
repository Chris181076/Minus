<?php

namespace App\Form;

use App\Entity\JournalEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class JournalEntryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heure', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Heure',
                'input' => 'datetime_immutable',
                
            ])
            ->add('action', ChoiceType::class, [
                'label' => 'Action',
                'choices'  => [
                    'Manger' => 'manger',
                    'Goûter' => 'gouter',
                    'Pipi' => 'pipi',
                    'Popo' => 'popo',
                    'Sieste' => 'sieste',
                    'Activité' => 'activité',
                    'couche propre' => 'couche ok',
                ],
                'placeholder' => 'Choisir...',
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'label' => 'Note',
            ]);
               
            }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JournalEntry::class,
        ]);
    }
}