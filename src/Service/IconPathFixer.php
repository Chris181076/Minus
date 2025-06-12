<?php // Cette ligne DOIT être la toute première du fichier

namespace App\Service;

use App\Entity\Icon;
use Doctrine\ORM\EntityManagerInterface;

class IconPathFixer
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function fixIconPaths(): int
    {
        $icons = $this->entityManager->getRepository(Icon::class)->findAll();
        $fixedCount = 0;
        
        foreach ($icons as $icon) {
            $currentPath = $icon->getPath();
            $fileName = basename($currentPath);
            
            if ($currentPath !== $fileName) {
                $icon->setPath($fileName);
                $fixedCount++;
            }
        }
        
        if ($fixedCount > 0) {
            $this->entityManager->flush();
        }
        
        return $fixedCount;
    }
}