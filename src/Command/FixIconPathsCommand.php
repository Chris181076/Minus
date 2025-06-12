<?php

namespace App\Command;

use App\Service\IconPathFixer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixIconPathsCommand extends Command
{
    protected static $defaultName = 'app:fix-icon-paths';
    
    private $pathFixer;

    public function __construct(IconPathFixer $pathFixer)
    {
        parent::__construct();
        $this->pathFixer = $pathFixer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Corrige les chemins des icônes dans la base de données')
            ->setHelp('Cette commande nettoie les chemins des icônes stockés en base de données');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fixedCount = $this->pathFixer->fixIconPaths();
        
        if ($fixedCount > 0) {
            $output->writeln(sprintf('Fixed %d icon paths!', $fixedCount));
        } else {
            $output->writeln('All icon paths are already correct!');
        }
        
        return Command::SUCCESS;
    }
}