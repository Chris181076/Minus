<?php
// src/Twig/GlobalVariables.php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use App\Repository\GroupRepository;

class GlobalVariables extends AbstractExtension implements GlobalsInterface
{
    private GroupRepository $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function getGlobals(): array
    {
        return [
            'groups' => $this->groupRepository->findAll(),
        ];
    }
}

