<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\View\PageLicenseData;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Throwable;

class LicenseController extends AbstractController
{
    private const VALID_DOCS = ['overview', 'book', 'site', 'code', 'dmca'];

    /**
     * @throws Throwable
     */
    public function handle(string $page, string $lang): string
    {
        $doc = $_GET['doc'] ?? 'overview';

        if (!in_array($doc, self::VALID_DOCS, true)) {
            $doc = 'overview';
        }

        $markdownPath = PROJECT_ROOT . '/legal/' . $doc . '.md';

        if (!is_file($markdownPath) || !is_readable($markdownPath)) {
            throw new \RuntimeException("License document not found: {$doc}");
        }

        $markdown = (string) file_get_contents($markdownPath);
        $contentHtml = $this->renderMarkdown($markdown);

        $translator = $this->container->get('localized_content');
        $title = (string) $translator->translate($lang, 'page_license_doc_' . $doc, 'License');

        $data = new PageLicenseData(
            title: $title,
            contentHtml: $contentHtml,
            doc: $doc,
            lang_code: $lang
        );

        return $this->renderPage('page_license.php', $data, $page, $lang);
    }

    private function renderMarkdown(string $markdown): string
    {
        $config = [
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        $converter = new MarkdownConverter($environment);

        return (string) $converter->convert($markdown)->getContent();
    }
}