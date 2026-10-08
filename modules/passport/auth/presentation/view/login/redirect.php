<?php

use yii\helpers\Html;
use yii\helpers\Json;

/** @var string $targetUrl */
/** @var int $delay */

$this->registerMetaTag([
    'http-equiv' => 'refresh',
    'content'    => $delay . ';url=' . Html::encode($targetUrl),
], 'refresh');
?>
<style>
    .spinner {
        width: 36px; height: 36px; margin: 0 auto;
        border: 3px solid var(--background-blue-light);
        border-top-color: var(--background-blue);
        border-radius: 50%;
        animation: spin 0.9s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
<div style="text-align:center;padding:24px 0;">
    <div class="spinner"></div>
    <p style="margin-top:20px;font-size:13px;color:#6b7280;">
        Если ничего не произошло —
        <a href="<?= Html::encode($targetUrl) ?>">перейти вручную</a>.
    </p>
</div>
<script>
    setTimeout(
        function () {
            window.location.replace(<?= Json::htmlEncode($targetUrl) ?>);
        }, (<?= $delay ?> * 1000)
    );
</script>