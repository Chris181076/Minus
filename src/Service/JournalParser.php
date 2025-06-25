<?php
namespace App\Service;

class JournalParser
{
    public function parseActionsWithHours(string $actionsText, ?\DateTimeImmutable $startTime = null): array
    {
        if (!$startTime) {
        $startTime = new \DateTimeImmutable('now');
        }
        $lines = explode("\n", trim($actionsText));
        $result = [];

        $currentTime = $startTime ?? new \DateTimeImmutable('now');

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $result[] = [
                'heure' => $currentTime->format('H:i'),
                'action' => $line,
            ];

        }

        return $result;
    }
}