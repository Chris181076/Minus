<?php

namespace App\Entity;

use App\Repository\IconRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IconRepository::class)]
class Icon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
// src/Entity/Icon.php

public function getPath(): ?string
{
    if (!$this->path) {
        return null;
    }
    
    // Retourne le chemin absolu correct
    return '/icons/' . $this->path;
}

public function setPath(string $path): static
{
    // Nettoie le chemin pour ne garder que le nom du fichier
    $cleanPath = str_replace(['icons/', 'public/'], '', $path);
    $this->path = basename($cleanPath);
    
    return $this;
}
}
