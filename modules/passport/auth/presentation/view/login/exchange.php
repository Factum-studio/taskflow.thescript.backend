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
    .exchange {
        text-align: center;
        padding: 24px 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* ---------- орбита ---------- */
    .orbit {
        position: relative;
        width: 150px;
        height: 240px;
        margin: 0 auto 16px;
    }

    .orbit__track {
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        border: 1px dashed var(--background-blue-light, #c7d2fe);
        border-radius: 50%;
        opacity: .75;
    }

    .orbit__ring {
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        animation: orbit-spin 3.2s ease-in-out infinite;
        will-change: transform;
    }

    /* слоты — на противоположных сторонах окружности */
    .orbit__slot {
        position: absolute;
        top: 50%;
    }
    .orbit__slot--right { right: 0; transform: translate(50%, -50%); }
    .orbit__slot--left  { left:  0; transform: translate(-50%, -50%); }

    /* контр-вращение */
    .orbit__counter {
        animation: orbit-counter 3.2s ease-in-out infinite;
        will-change: transform;
    }

    /* ---------- чипы ---------- */
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 14px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 600;
        background: #fff;
        white-space: nowrap;
        box-shadow: 0 8px 24px rgba(30, 41, 59, .08);
        user-select: none;
    }

    .chip--code {
        color: #1e3a8a;
        border: 1px solid #c7d2fe;
    }
    .chip__braces {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 14px;
        color: #3b5bdb;
        letter-spacing: -.5px;
    }
    .chip--code:hover {
        background: var(--background-blue);
    }

    .chip--keys {
        color: #166534;
        border: 1px solid #86efac;
    }
    .chip__key {
        width: 22px;
        height: 22px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .chip--keys:hover {
        background: var(--background-green);
    }

    /* ---------- анимации ---------- */
    @keyframes orbit-spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
    @keyframes orbit-counter {
        from { transform: rotate(0deg); }
        to   { transform: rotate(-360deg); }
    }

    /* ---------- подпись ---------- */
    .exchange__caption {
        margin: 8px 0 0;
        font-size: 13px;
        color: #6b7280;
    }
    .exchange__caption code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 12px;
        color: #3b5bdb;
        background: #eef2ff;
        padding: 1px 6px;
        border-radius: 5px;
    }

    @media (prefers-reduced-motion: reduce) {
        .orbit__ring, .orbit__counter { animation: none; }
    }
</style>

<div class="exchange">
    <div class="orbit">
        <div class="orbit__track"></div>

        <div class="orbit__ring">
            <div class="orbit__slot orbit__slot--right">
                <div class="orbit__counter">
                    <div class="chip chip--code">
                        <span class="chip__braces">&lt;/&gt;</span>
                        <span>code</span>
                    </div>
                </div>
            </div>

            <div class="orbit__slot orbit__slot--left">
                <div class="orbit__counter">
                    <div class="chip chip--keys">
                        <svg class="chip__key" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="7.5" cy="15.5" r="4.5"></circle>
                            <path d="M10.7 12.3 21 2"></path>
                            <path d="M17 6l3 3"></path>
                            <path d="M14 9l3 3"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p class="exchange__caption">
        Обмениваем <code>code</code> на ключи доступа…
    </p>
</div>
<script>
    setTimeout(
        function () {
            window.location.replace(<?= Json::htmlEncode($targetUrl) ?>);
        }, (<?= $delay ?> * 1000)
    );
</script>