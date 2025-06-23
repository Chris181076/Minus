<?php
namespace App\Service;
use App\Service\WeekHelper;
use App\Entity\Semainier;

class WeekHelper
{
    public function getWeekStartAndEnd(?\DateTimeInterface $referenceDate = null): array
    {
        $referenceDate = $referenceDate ?? new \DateTimeImmutable();
        $start = $referenceDate->modify(('Monday' === $referenceDate->format('l')) ? 'this Monday' : 'last Monday')->setTime(0, 0);
        $end = $start->modify('+4 days')->setTime(23, 59, 59);
        return [$start, $end];
    }

    public function getWeekDays(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }
    public function getStartOfWeek(\DateTimeInterface $referenceDate = null): \DateTimeImmutable
    {
        $referenceDate = $referenceDate ?? new \DateTimeImmutable();
        $startOfWeek = $referenceDate->modify(('Monday' === $referenceDate->format('l')) ? 'this Monday' : 'last Monday');
        return $startOfWeek->setTime(0, 0, 0);
    }

    public function getEndOfWeek(\DateTimeInterface $referenceDate = null): \DateTimeImmutable
    {
        $startOfWeek = $this->getStartOfWeek($referenceDate);
        return $startOfWeek->modify('+4 days')->setTime(23, 59, 59);
    }
}