<?php
/**
 * @var string $jsonUrl  URL до swagger.json (передаётся из контроллера)
 */
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMC4yMTggMy4yMTYwNEMxMC4yNjgzIDMuMDI3NzIgMTAuMjQzMiAyLjgyNzI0IDEwLjE0ODIgMi42NTcwN0MxMC4wNTMyIDIuNDg2ODkgOS44OTU2NCAyLjM2MDM5IDkuNzA4OTYgMi4zMDQzNkM5LjU2MzI0IDIuMjYwNjMgOS40MDg3IDIuMjYyNTEgOS4yNjU2MSAyLjMwODEyQzkuMjI1MzkgMi4zMjA5NCA5LjE4NjA3IDIuMzM3MjEgOS4xNDgxIDIuMzU2OUM4Ljk3NTA4IDIuNDQ2NjIgOC44NDM3NyAyLjYwMDE4IDguNzgyMDEgMi43ODUwNEw1Ljc4MjAxIDEyLjc4NUM1LjczMzE1IDEyLjk3MyA1Ljc1OTA3IDEzLjE3MjUgNS44NTQzMSAxMy4zNDE3QzUuOTQ5NTYgMTMuNTEwOSA2LjEwNjcyIDEzLjYzNjYgNi4yOTI3MyAxMy42OTIzQzYuNDc4NzQgMTMuNzQ4IDYuNjc5MSAxMy43Mjk0IDYuODUxNjYgMTMuNjQwM0M3LjAyNDIzIDEzLjU1MTMgNy4xNTU1NSAxMy4zOTg5IDcuMjE4MDEgMTMuMjE1TDguMjUwMDEgOS43NzUzOEwxMC4wOTYxIDMuNjIyMjhMMTAuMjE4IDMuMjE2MDRaTTQuNTMwMDEgNC45NzAwNEM0LjY3MDQ2IDUuMTEwNjYgNC43NDkzNSA1LjMwMTI5IDQuNzQ5MzUgNS41MDAwNEM0Ljc0OTM1IDUuNjk4NzkgNC42NzA0NiA1Ljg4OTQxIDQuNTMwMDEgNi4wMzAwNEwyLjU2MDAxIDguMDAwMDRMNC41MzAwMSA5Ljk3MDA0QzQuNjYyNDkgMTAuMTEyMiA0LjczNDYxIDEwLjMwMDMgNC43MzExOSAxMC40OTQ2QzQuNzI3NzYgMTAuNjg4OSA0LjY0OTA1IDEwLjg3NDIgNC41MTE2MyAxMS4wMTE3QzQuMzc0MjIgMTEuMTQ5MSA0LjE4ODg0IDExLjIyNzggMy45OTQ1MyAxMS4yMzEyQzMuODAwMjMgMTEuMjM0NiAzLjYxMjE5IDExLjE2MjUgMy40NzAwMSAxMS4wM0wwLjk3MDAxMSA4LjUzMDA0QzAuODI5NTYxIDguMzg5NDEgMC43NTA2NzEgOC4xOTg3OSAwLjc1MDY3MSA4LjAwMDA0QzAuNzUwNjcxIDcuODAxMjkgMC44Mjk1NjEgNy42MTA2NiAwLjk3MDAxMSA3LjQ3MDA0TDMuNDcwMDEgNC45NzAwNEMzLjYxMDY0IDQuODI5NTkgMy44MDEyNiA0Ljc1MDcgNC4wMDAwMSA0Ljc1MDdDNC4xOTg3NiA0Ljc1MDcgNC4zODkzOSA0LjgyOTU5IDQuNTMwMDEgNC45NzAwNFoiIGZpbGw9IiNFNkU5RUUiLz4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMC4yMTggMy4yMTU5OEMxMC4yNjgyIDMuMDI3NjYgMTAuMjQzMiAyLjgyNzE4IDEwLjE0ODIgMi42NTcwMUMxMC4wNTMyIDIuNDg2ODMgOS44OTU2MyAyLjM2MDMzIDkuNzA4OTYgMi4zMDQzQzkuNTYzMjQgMi4yNjA1NyA5LjQwODcgMi4yNjI0NSA5LjI2NTYgMi4zMDgwNkM5Ljk5OTEzIDIuMDA2NzUgMTAuMTY5NiAyLjAwNTkzIDExLjExODEgMi4wMDEzNUMxMS4xMzc1IDIuMDAxMjUgMTEuMTU3MyAyLjAwMTE2IDExLjE3NzQgMi4wMDEwNkMxMi4yMjg4IDEuOTk1ODUgMTMuMjY2NiAyLjIzODE0IDE0LjIwNyAyLjcwODMzQzE0LjY5MyAyLjk1MDMzIDE1IDMuNDQ4MzMgMTUgMy45OTEzM1YxMi45MjkzQzE1IDEzLjU3OTMgMTQuNDc0IDE0LjEwNDMgMTMuODI1IDE0LjEwNDNIMTMuNzg1QzEzLjU5OCAxNC4xMDQzIDEzLjQxNSAxNC4wNTQzIDEzLjI1NiAxMy45NTgzQzEyLjU2NDMgMTMuNTQ0IDExLjc3OTQgMTMuMzEwNiAxMC45NzM3IDEzLjI3OTdDMTAuMTY4IDEzLjI0ODcgOS4zNjc0NCAxMy40MjEzIDguNjQ2MDEgMTMuNzgxM0w4LjQ0NyAxMy44ODEzQzguMTUzMTUgMTQuMDI4OCA3LjgyODc4IDE0LjEwNTIgNy41MDAwMSAxNC4xMDQzSDcuMzgzMDFDNy4xMzA5MSAxNC4xMDUzIDYuNTkzNzUgMTMuODgxMyA2LjI5MjcyIDEzLjY5MjJDNi40Nzg3MyAxMy43NDc5IDYuNjc5MDkgMTMuNzI5MyA2Ljg1MTY2IDEzLjY0MDNDNy4wMjQyMyAxMy41NTEzIDcuMTU1NTUgMTMuMzk4OCA3LjIxODAxIDEzLjIxNUw4LjI1MDAxIDkuNzc1MzJWMTIuMzA5M0M5LjA3OTIzIDExLjk0NCA5Ljk3NzUgMTEuNzYyOSAxMC44ODM1IDExLjc3NjNDMTEuNzg5NSAxMS43ODk4IDEyLjY4MiAxMS45OTg1IDEzLjUgMTIuMzg4M1Y0LjAzMTMzQzEyLjczNzYgMy42NTc4MSAxMS44OTUzIDMuNDc2NjQgMTEuMDQ2NyAzLjUwMzY2QzEwLjcyNTggMy41MTM4OCAxMC40MDc2IDMuNTUzNzcgMTAuMDk2MSAzLjYyMjIyTDEwLjIxOCAzLjIxNTk4WiIgZmlsbD0iI0U2RTlFRSIvPgo8L3N2Zz4=" />
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
    <svg viewBox="0 0 1080 1080" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M687.798 654.642L639.122 588.017L568.663 491.584L702.216 491.422L772.092 587.866L822.236 657.05L701.773 818.489L573.61 818.64V817.96L652.655 704.916L687.798 654.642Z" fill="var(--primary)"/>
        <path d="M358.679 426.308L406.49 491.756L476.939 588.2L336.604 588.352L266.717 491.929L216.67 422.863L337.09 261.5L472.835 261.328V263.023L392.159 378.41L358.679 426.308Z" fill="var(--primary)"/>
        <path d="M558.446 371.596C556.202 374.367 553.4 376.634 550.222 378.251C547.044 379.868 543.561 380.798 540 380.981H488.862V362.621H540C546.134 362.621 546.167 353.074 540 353.117C528.39 352.102 504.122 356.897 495.601 346.313C482.339 333.914 489.899 309.938 507.697 307.001L525.701 325.08H511.974C505.85 325.08 505.807 334.638 511.974 334.584H544.374C549.395 335.264 554.065 337.535 557.701 341.064C558.144 341.485 558.49 341.863 558.781 342.144C559.688 343.369 560.486 344.669 561.168 346.032C561.809 347.32 562.347 348.656 562.777 350.028C563.069 351.184 565.834 362.124 558.446 371.596ZM563.76 305.489V323.849H534.816L516.456 305.489H563.76Z" fill="var(--primary)"/>
        <path d="M610.049 357.761H623.765L623.441 359.057C622.57 362.936 620.876 366.582 618.473 369.749C616.029 372.823 612.978 375.36 609.509 377.201C608.881 377.532 608.231 377.82 607.565 378.065C604.212 379.482 600.61 380.217 596.97 380.225C593.421 380.236 589.905 379.546 586.624 378.193C583.343 376.84 580.362 374.852 577.853 372.342C575.343 369.833 573.355 366.852 572.002 363.571C570.649 360.29 569.959 356.774 569.97 353.225C569.989 350.175 570.5 347.148 571.482 344.261L572.022 342.641L583.254 353.873V354.305C583.478 357.525 584.859 360.555 587.141 362.838C589.423 365.12 592.454 366.501 595.674 366.725H597.618C599.029 366.619 600.414 366.291 601.722 365.753C602.172 365.619 602.606 365.438 603.018 365.213C604.338 364.547 605.542 363.672 606.582 362.621C607.851 361.361 608.847 359.854 609.509 358.193L610.049 357.761ZM584.129 348.149L574.301 338.321L574.841 337.565C576.965 334.514 579.695 331.934 582.862 329.987C586.03 328.04 589.565 326.769 593.246 326.251C596.927 325.734 600.676 325.983 604.257 326.982C607.838 327.981 611.174 329.709 614.056 332.057C618.796 335.808 622.111 341.067 623.452 346.961L623.776 348.257H610.06L609.844 347.609C609.178 345.952 608.186 344.447 606.928 343.181C605.382 341.646 603.496 340.499 601.423 339.833C599.35 339.167 597.148 339.001 594.998 339.349C592.849 339.696 590.812 340.548 589.054 341.833C587.296 343.119 585.868 344.802 584.885 346.745L584.129 348.149Z" fill="var(--primary)"/>
        <path d="M688.014 379.901H667.926L667.602 379.577L647.514 359.64L634.554 346.68V327.564H669.6C670.581 327.565 671.559 327.674 672.516 327.888C675.465 328.434 678.177 329.866 680.292 331.992C682.253 333.956 683.635 336.422 684.288 339.12C684.591 340.283 684.736 341.482 684.72 342.684C684.738 343.226 684.702 343.769 684.612 344.304C684.417 348.452 682.685 352.38 679.752 355.32C676.829 358.264 672.883 359.966 668.736 360.072H667.872L688.014 379.901ZM634.77 353.16L647.73 366.12V380.484H634.77V353.16ZM670.853 345.87C671.416 345.088 671.677 344.129 671.587 343.17C671.567 342.694 671.487 342.222 671.35 341.766C671.299 341.565 671.238 341.367 671.166 341.172H648.486L654.642 347.328H667.062C667.767 347.393 668.478 347.29 669.137 347.03C669.795 346.769 670.383 346.357 670.853 345.827V345.87Z" fill="var(--primary)"/>
        <path d="M711.558 327.413V346.853L698.598 333.893V327.413H711.558ZM698.598 339.725L711.558 352.685V379.685H698.598V339.725Z" fill="var(--primary)"/>
        <path d="M764.91 339.401C765.213 340.564 765.358 341.763 765.342 342.965C765.376 346.377 764.232 349.695 762.102 352.361C761.736 352.819 761.339 353.252 760.914 353.657C759.506 355.093 757.826 356.234 755.971 357.013C754.117 357.792 752.125 358.193 750.114 358.193H733.914L722.52 346.745V327.629H750.168C753.581 327.648 756.89 328.806 759.57 330.919C762.25 333.032 764.149 335.979 764.964 339.293L764.91 339.401ZM722.52 352.469L735.48 365.429V379.577H722.52V352.469ZM752.112 342.857C752.117 342.53 752.081 342.203 752.004 341.885C751.782 341.114 751.374 340.409 750.816 339.833C750.408 339.417 749.921 339.088 749.382 338.865C748.844 338.642 748.266 338.531 747.684 338.537H735.48V340.265L742.608 347.393H747.684C748.266 347.399 748.844 347.288 749.382 347.065C749.921 346.842 750.408 346.513 750.816 346.097L751.14 345.773C751.729 344.914 752.048 343.898 752.058 342.857H752.112Z" fill="var(--primary)"/>
        <path d="M828.414 327.413V340.373H808.758V359.64L795.798 346.68V340.416H776.142V327.456L828.414 327.413ZM795.798 352.577L808.758 365.537V379.685H795.798V352.577Z" fill="var(--primary)"/>
        <path d="M515.938 417.56L525.247 432.972L507.017 462.953H488.311L515.938 417.56ZM568.08 463.039H549.396L545.54 456.656L518.951 412.56L528.25 397.246L568.08 463.039Z" fill="var(--primary)"/>
        <path d="M614.52 443.88V462.759H603.234V459.756C599.015 462.218 594.117 463.26 589.261 462.73C584.406 462.199 579.849 460.124 576.261 456.809C572.673 453.495 570.244 449.116 569.331 444.317C568.418 439.519 569.07 434.554 571.19 430.153L571.752 429.073L580.489 437.811L580.392 438.275C580.32 438.837 580.292 439.404 580.306 439.971C580.326 443.018 581.549 445.933 583.71 448.082C585.871 450.23 588.793 451.437 591.84 451.44C594.15 451.463 596.412 450.777 598.32 449.475L594.205 443.88H614.52ZM581.99 434.16L573.912 426.071L574.474 425.412C576.585 422.88 579.227 420.844 582.214 419.448C585.2 418.052 588.457 417.33 591.754 417.334C596.381 417.349 600.897 418.756 604.714 421.373C609.471 424.613 612.801 429.559 614.012 435.186L614.293 436.32H602.64L602.456 435.759C601.904 434.38 601.073 433.13 600.016 432.087C597.845 429.922 594.905 428.707 591.84 428.707C588.775 428.707 585.835 429.922 583.664 432.087C583.281 432.506 582.92 432.946 582.584 433.404L581.99 434.16Z" fill="var(--primary)"/>
        <path d="M637.524 445.759L623.808 432V417.377H661.77V428.652H636.304L642.222 434.57H661.856V445.846L637.524 445.759ZM661.856 451.678V462.953H623.894V437.206L635.18 448.481V451.721L661.856 451.678Z" fill="var(--primary)"/>
        <path d="M713.167 416.156V462.953H702.551L702.076 462.478L689.677 450.079L682.441 442.843L671.166 431.568V417.377H672.948L673.229 417.658L701.892 446.321V427.432L702.173 427.151L713.167 416.156ZM671.252 436.828L682.56 448.103V462.953H671.252V436.828Z" fill="var(--primary)"/>
        <path d="M757.231 443.88H769.165L768.884 444.96C768.128 448.334 766.655 451.505 764.564 454.259C762.442 456.935 759.786 459.142 756.767 460.739C756.224 461.028 755.661 461.277 755.082 461.484C752.167 462.718 749.035 463.357 745.87 463.363C742.782 463.373 739.723 462.772 736.869 461.596C734.014 460.419 731.421 458.689 729.237 456.505C727.054 454.322 725.324 451.729 724.147 448.874C722.97 446.02 722.37 442.961 722.38 439.873C722.393 437.22 722.838 434.586 723.697 432.076L724.162 430.661L733.882 440.435V440.813C734.08 443.612 735.282 446.245 737.266 448.229C739.25 450.213 741.883 451.414 744.682 451.613H746.366C747.596 451.52 748.803 451.232 749.941 450.76C750.317 450.644 750.679 450.488 751.021 450.295C752.171 449.718 753.218 448.955 754.121 448.038C755.224 446.942 756.089 445.629 756.659 444.182L757.231 443.88ZM734.681 435.51L726.127 426.956L726.592 426.308C728.776 423.202 731.675 420.666 735.046 418.916C738.416 417.166 742.158 416.253 745.956 416.254C751.331 416.262 756.54 418.119 760.709 421.513C764.831 424.776 767.716 429.348 768.884 434.473L769.165 435.553H757.231L757.037 434.992C756.467 433.547 755.606 432.235 754.51 431.136C753.167 429.798 751.527 428.798 749.724 428.217C747.92 427.635 746.004 427.49 744.133 427.793C742.263 428.095 740.49 428.837 738.962 429.957C737.433 431.077 736.192 432.543 735.34 434.236L734.681 435.51Z" fill="var(--primary)"/>
        <path d="M831.362 417.377L806.274 442.465V453.6L804.676 452.099L794.956 442.379L769.867 417.291H785.84L786.121 417.571L800.69 432.141L815.54 417.291L831.362 417.377ZM795.096 447.617L806.371 458.892V462.931H795.096V447.617Z" fill="var(--primary)"/>
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