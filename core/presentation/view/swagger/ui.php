<?php
/**
 * @var string $jsonUrl  URL до swagger.json (передаётся из контроллера)
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>CRM API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3/swagger-ui.css">
    <link rel="stylesheet" type="text/css" href="/css/swagger.css">
    <style>
        /* Прелоадер */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.04);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .preloader.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .preloader-content {
            text-align: center;
        }

        .preloader-video {
            width: 400px;
            max-width: 80vw;
            border-radius: 16px;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.3);
        }

        .preloader-text {
            margin-top: 24px;
            font-family: monospace;
            font-size: 18px;
            color: #6BA7FF;
            letter-spacing: 2px;
            user-select: none;
        }

        .preloader-text::after {
            content: '';
            display: inline-block;
            width: 4px;
            height: 18px;
            background: #6BA7FF;
            margin-left: 8px;
            animation: blink 1s step-end infinite;
            vertical-align: middle;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

        /* Основной контент изначально скрыт */
        #swagger-ui {
            opacity: 0;
            transition: opacity 0.3s ease-in;
        }

        #swagger-ui.visible {
            opacity: 1;
        }
        #unmuteBtn {
            user-select: none;
        }
    </style>
</head>
<body>
<div class="logo">
    <svg viewBox="0 0 174 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 24V18.6719H18.6562L21.3281 16L18.6562 13.3438H0V5.34375L5.32812 0H26.6562V5.34375H8L5.32812 8H21.3281L26.6562 13.3438V18.6719L21.3281 24H0ZM34.6719 24L29.3438 18.6719V13.3438L34.6719 8H56V13.3438H37.3438L34.6719 16L37.3438 18.6719H56V24H34.6719ZM58.6875 24V13.3438L64.0156 8H85.3438V13.3438H66.6875L64.0156 16V24H58.6875ZM88.0312 24V18.6719H98.6875V13.3438H88.0312V8H104.031V18.6719H114.688V24H88.0312ZM98.6875 5.34375V0H104.031V5.34375H98.6875ZM136.031 18.6719L138.703 16L136.031 13.3438H125.375L122.703 16V18.6719H136.031ZM117.375 32V13.3438L122.703 8H138.703L144.031 13.3438V18.6719L138.703 24H122.703V32H117.375ZM162.719 24L157.375 18.6719V13.3438H146.719V8H157.375V0H162.719V8H173.375V13.3438H162.719V16L165.375 18.6719H173.375V24H162.719Z" fill="url(#paint0_linear_18_616)"/>
        <defs>
            <linearGradient id="paint0_linear_18_616" x1="0" y1="12" x2="177" y2="12" gradientUnits="userSpaceOnUse">
                <stop stop-color="#6EE7B7"/>
                <stop offset="1" stop-color="#6BA7FF"/>
            </linearGradient>
        </defs>
    </svg>
</div>
<!-- Прелоадер -->
<div id="preloader" class="preloader">
    <div class="preloader-content">
        <video
                id="kitikVideo"
                class="preloader-video"
                autoplay
                muted
                playsinline
                preload="auto"
        >
            <source src="/assets/kitik.mp4" type="video/mp4">
            Ваш браузер не поддерживает видео.
        </video>
        <div class="preloader-text">Документация уже едет...</div>
        <button id="unmuteBtn" style="
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin-top: 16px;
            padding: 8px 16px;
            background: #6BA7FF;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            font-family: monospace;
            display: none;
        ">
            🔊 Включить звук
        </button>
    </div>
</div>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@3/swagger-ui-bundle.js"></script>
<script>
    (function() {
        const preloader = document.getElementById('preloader');
        const swaggerContainer = document.getElementById('swagger-ui');
        const video = document.getElementById('kitikVideo');
        const unmuteBtn = document.getElementById('unmuteBtn');
        const preloaderContent = document.querySelector('.preloader-content');

        let videoPlayed = false;
        let swaggerLoaded = false;
        let userInteracted = false;

        // Создаём элемент подсказки, если его ещё нет
        let hintElement = document.querySelector('.preloader-hint');
        if (!hintElement && preloaderContent) {
            hintElement = document.createElement('div');
            hintElement.className = 'preloader-hint';
            hintElement.innerHTML = '✨ Нажмите в любом месте (кроме видео), чтобы пропустить ✨';
            hintElement.style.cssText = `
            margin-top: 16px;
            font-size: 14px;
            color: rgba(255,255,255,0.9);
            background: rgba(0,0,0,0.6);
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            cursor: pointer;
            opacity: 1;
            transition: opacity 0.3s;
            pointer-events: none;
        `;
            preloaderContent.appendChild(hintElement);
        }

        // Функция принудительного пропуска прелоадера
        function forceSkipPreloader() {
            if (videoPlayed && swaggerLoaded) return;

            if (!videoPlayed && video) {
                video.pause();
                videoPlayed = true;
                if (typeof video.dispatchEvent === 'function') {
                    video.dispatchEvent(new Event('ended'));
                }
            }
            hidePreloaderAndShowSwagger();
        }

        // Обработчик клика на прелоадере – пропускаем, если клик не по видео и не по кнопке
        if (preloader) {
            preloader.addEventListener('click', function(event) {
                if (video && video.contains(event.target)) return;
                if (unmuteBtn && unmuteBtn.contains(event.target)) return;
                if (hintElement && hintElement.contains(event.target)) return;
                forceSkipPreloader();
            });
        }

        // Показываем кнопку включения звука через 0.1 секунды
        setTimeout(() => {
            if (video && video.muted && !userInteracted) {
                unmuteBtn.style.display = 'block';
            }
        }, 100);

        // Включаем звук по клику
        if (unmuteBtn) {
            unmuteBtn.addEventListener('click', () => {
                if (video) {
                    video.muted = false;
                    unmuteBtn.style.display = 'none';
                    userInteracted = true;
                }
            });
        }

        // Функция для скрытия прелоадера
        function hidePreloaderAndShowSwagger() {
            if (!videoPlayed || !swaggerLoaded) return;

            if (hintElement) hintElement.style.opacity = '0';

            preloader.classList.add('fade-out');
            swaggerContainer.classList.add('visible');

            setTimeout(() => {
                if (preloader.parentNode) preloader.parentNode.removeChild(preloader);
            }, 500);

            if (video) video.pause();
        }

        // Слушаем окончание видео
        if (video) {
            video.addEventListener('ended', () => {
                videoPlayed = true;
                hidePreloaderAndShowSwagger();
            });

            video.addEventListener('error', () => {
                console.warn('Video failed to load, loading Swagger anyway');
                videoPlayed = true;
                hidePreloaderAndShowSwagger();
            });

            setTimeout(() => {
                if (!videoPlayed && video.ended || (video.currentTime > 0 && video.currentTime >= video.duration - 0.1)) {
                    videoPlayed = true;
                    hidePreloaderAndShowSwagger();
                }
            }, (video.duration * 1000) + 500);
        } else {
            videoPlayed = true;
        }

        // Загружаем Swagger UI
        SwaggerUIBundle({
            url: "<?= $jsonUrl ?>",
            dom_id: "#swagger-ui",
            onComplete: () => {
                swaggerLoaded = true;
                hidePreloaderAndShowSwagger();
            }
        });

        setTimeout(() => {
            if (!swaggerLoaded) {
                console.warn('Swagger loading timeout, showing anyway');
                swaggerLoaded = true;
                hidePreloaderAndShowSwagger();
            }
        }, 10000);
    })();
</script>
</body>
</html>