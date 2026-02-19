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
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@3/swagger-ui-bundle.js"></script>
<script>
    SwaggerUIBundle({
        url: "<?= $jsonUrl ?>",
        dom_id: "#swagger-ui"
    });
</script>
</body>
</html>