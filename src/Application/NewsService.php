<?php
declare(strict_types=1);

namespace App\Application;
// App/Application/NewsService.php
use App\Domain\NewsRepositoryInterface;

class NewsService {
    public function __construct(private readonly NewsRepositoryInterface $repo) {}
    public function getAll(string $lang): array { return $this->repo->findByLang($lang); }
}