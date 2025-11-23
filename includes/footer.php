<?php
declare(strict_types=1);
/** @var FooterData $data */

use App\View\FooterData;

?>
<footer>
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($data->site_title) ?>. <?= $data->translator->translate($data->lang_code, 'copyright') ?></p>
    <p><?= $data->translator->translate($data->lang_code, 'made_with') ?></p>
</footer>
