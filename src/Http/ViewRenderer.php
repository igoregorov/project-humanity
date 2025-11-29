<?php
// src/Http/ViewRenderer.php
declare(strict_types=1);

namespace App\Http;

use App\View\TemplateDataInterface;

class ViewRenderer
{
private string $includesPath;
private string $layoutFile;

public function __construct(string $includesPath = null, string $layoutFile = 'layout.php')
{
if ($includesPath === null) {
$includesPath = dirname(__DIR__, 2) . '/includes/';
}
$this->includesPath = rtrim($includesPath, '/\\') . '/';

$this->layoutFile = $this->includesPath . $layoutFile;

if (!file_exists($this->layoutFile)) {
error_log("DEBUG: Looking for layout file at: " . $this->layoutFile);
throw new \InvalidArgumentException("Layout file not found: {$this->layoutFile}");
}
}

/**
* Рендерит шаблон и оборачивает его в layout.
*/
public function render(string $templateName, TemplateDataInterface $data, array $layoutData = []): string
{
$contentHtml = $this->renderTemplate($templateName, $data);

// Рендерим layout, передав ему $contentHtml и $layoutData
// Используем extract для передачи переменных в layout
$contentHtml_for_layout = $contentHtml; // чтобы не конфликтовало с $contentHtml внутри ob
$layoutData['contentHtml'] = $contentHtml_for_layout;

ob_start();
try {
// Передаем данные в layout через extract
extract($layoutData, EXTR_OVERWRITE);
include $this->layoutFile;
} catch (\Throwable $e) {
ob_end_clean();
throw $e;
}
return ob_get_clean();
}

/**
* Рендерит *только* шаблон, без layout.
* Используется для рендеринга компонентов внутри layout.
*/
public function renderWithoutLayout(string $templateName, TemplateDataInterface $data): string
{
return $this->renderTemplate($templateName, $data);
}

/**
* Внутренний метод для рендеринга шаблона.
*/
private function renderTemplate(string $templateName, TemplateDataInterface $data): string
{
$templateFile = $this->includesPath . $templateName;

$realTemplateFile = realpath($templateFile);
$realBasePath = realpath($this->includesPath);

if (!$realTemplateFile || !$realBasePath || !str_starts_with($realTemplateFile, $realBasePath)) {
throw new \InvalidArgumentException("Invalid template path: $templateFile");
}

ob_start();
try {
// Передаем данные в шаблон через переменную $data
include $realTemplateFile;
} catch (\Throwable $e) {
ob_end_clean();
throw $e;
}
return ob_get_clean();
}
}