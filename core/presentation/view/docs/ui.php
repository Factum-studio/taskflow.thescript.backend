<?php
use yii\helpers\Html;
?>

<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags() ?>
        <title>Документация TASKFLOW</title>
        <?php $this->head() ?>
        <link rel="stylesheet" href="/css/docs.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body>
    <?php $this->beginBody() ?>

    <button class="sidebar-toggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="docs-wrapper">
        <aside class="docs-sidebar">
            <div class="logo">
                <svg viewBox="0 0 174 32" fill="none">
                    <path d="M0 24V18.6719H18.6562L21.3281 16L18.6562 13.3438H0V5.34375L5.32812 0H26.6562V5.34375H8L5.32812 8H21.3281L26.6562 13.3438V18.6719L21.3281 24H0ZM34.6719 24L29.3438 18.6719V13.3438L34.6719 8H56V13.3438H37.3438L34.6719 16L37.3438 18.6719H56V24H34.6719ZM58.6875 24V13.3438L64.0156 8H85.3438V13.3438H66.6875L64.0156 16V24H58.6875ZM88.0312 24V18.6719H98.6875V13.3438H88.0312V8H104.031V18.6719H114.688V24H88.0312ZM98.6875 5.34375V0H104.031V5.34375H98.6875ZM136.031 18.6719L138.703 16L136.031 13.3438H125.375L122.703 16V18.6719H136.031ZM117.375 32V13.3438L122.703 8H138.703L144.031 13.3438V18.6719L138.703 24H122.703V32H117.375ZM162.719 24L157.375 18.6719V13.3438H146.719V8H157.375V0H162.719V8H173.375V13.3438H162.719V16L165.375 18.6719H173.375V24H162.719Z" fill="url(#paint0_linear_18_616)"/>
                    <defs>
                        <linearGradient id="paint0_linear_18_616" x1="0" y1="12" x2="177" y2="12" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#6EE7B7"/>
                            <stop offset="1" stop-color="#6BA7FF"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <div class="doc-toc">
                <?= $menu ?>
            </div>
        </aside>

        <main class="docs-content">
            <div class="docs-content-inner">
                <?= $content ?>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>
        (function() {
            function init() {
                // Подсветка синтаксиса
                if (typeof hljs !== 'undefined') {
                    hljs.highlightAll();
                }

                var toggleBtn = document.querySelector('.sidebar-toggle');
                var sidebar = document.querySelector('.docs-sidebar');

                if (toggleBtn && sidebar) {
                    // Функция обновления положения кнопки
                    function updateButtonPosition() {
                        if (window.innerWidth <= 1024) {
                            if (sidebar.classList.contains('open')) {
                                toggleBtn.style.transform = 'translateX(200px)';
                            } else {
                                toggleBtn.style.transform = 'translateX(0)';
                            }
                        } else {
                            toggleBtn.style.transform = '';
                        }
                    }

                    // Обработчик клика
                    toggleBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        sidebar.classList.toggle('open');
                        updateButtonPosition();
                    });

                    // При изменении размера окна сбрасываем открытое состояние и позицию
                    window.addEventListener('resize', function() {
                        if (window.innerWidth > 1024) {
                            sidebar.classList.remove('open');
                            toggleBtn.style.transform = '';
                        } else {
                            updateButtonPosition();
                        }
                    });

                    // Закрытие сайдбара при клике вне его на мобильных
                    document.addEventListener('click', function(e) {
                        if (window.innerWidth <= 1024) {
                            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                                sidebar.classList.remove('open');
                                updateButtonPosition();
                            }
                        }
                    });

                    // Начальная установка позиции
                    updateButtonPosition();
                }

                // Кнопки копирования
                var pres = document.querySelectorAll('pre');
                pres.forEach(function(block) {
                    var btn = document.createElement('button');
                    btn.innerHTML = '<i class="fas fa-copy"></i>';
                    btn.className = 'copy-btn';
                    btn.title = 'Copy code';
                    btn.onclick = function() {
                        var code = block.querySelector('code') ? block.querySelector('code').innerText : block.innerText;
                        navigator.clipboard.writeText(code).then(function() {
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            setTimeout(function() { btn.innerHTML = '<i class="fas fa-copy"></i>'; }, 2000);
                        });
                    };
                    block.style.position = 'relative';
                    block.appendChild(btn);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>