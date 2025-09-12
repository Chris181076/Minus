<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Child;
use App\Form\ChildForm;

class ChildFormTest extends KernelTestCase
{
    public function testFormSubmit()
    {
        self::bootKernel();
        $container = static::getContainer();

        $factory = $container->get('form.factory');
        
        // Récupérer l'EntityManager pour obtenir un Group réel
        $entityManager = $container->get('doctrine.orm.entity_manager');
        
        // Récupérer le premier groupe disponible
        $group = $entityManager->getRepository(\App\Entity\Group::class)->find(1);
        
        // Si aucun groupe n'existe, en créer un pour le test
        if (!$group) {
            $group = new \App\Entity\Group();
            $group->setName('Force Rouge');
            $group->setColorCode('#FF0000');
            $entityManager->persist($group);
            $entityManager->flush();
        }

        $child = new Child();
        
        // Solution 1: Initialiser created_at avant de créer le formulaire
        $child->setCreatedAt(new \DateTimeImmutable());
        
        $form = $factory->create(ChildForm::class, $child, ['is_admin' => true]);

        $formData = [
            'firstName' => 'Alice',
            'lastName' => 'Dupont',
            'birthDate' => '2015-06-01',
            'medicalNotes' => 'Asthmatique',
            'childGroup' => $group->getId(),   // Utiliser l'ID du group réel
            'created_at' => '2024-01-15',  // Ajout de la date de création
        ];

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        
        // Debug : afficher les erreurs si le formulaire n'est pas valide
        if (!$form->isValid()) {
            echo "Erreurs du formulaire :\n";
            foreach ($form->getErrors(true) as $error) {
                echo "- " . $error->getMessage() . "\n";
            }
        }
        
        // Tests supplémentaires recommandés
        $this->assertTrue($form->isValid());
        $this->assertEquals('Alice', $child->getFirstName());
        $this->assertEquals('Dupont', $child->getLastName());
        $this->assertEquals(new \DateTime('2015-06-01'), $child->getBirthDate());
        $this->assertEquals('Asthmatique', $child->getMedicalNotes());
    }
}