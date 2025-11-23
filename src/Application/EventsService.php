<?php
declare(strict_types=1);

namespace App\Application;
// App/Application/EventsService.php
use App\Domain\EventsRepositoryInterface;

class EventsService {
    public function __construct(private readonly EventsRepositoryInterface $repo) {}
    public function getAll(string $lang): array { return $this->repo->findByLang($lang); }
}