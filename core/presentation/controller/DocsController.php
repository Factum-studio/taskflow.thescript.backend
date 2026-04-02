<?php

namespace core\presentation\controller;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\FileHelper;
use yii\web\Response;
use yii\helpers\HtmlPurifier;
use cebe\markdown\GithubMarkdown;

class DocsController extends Controller
{
    private $currentPage;

    /**
     * @throws NotFoundHttpException
     */
    public function actionUi($page = 'index')
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        $this->layout = false;

        $page = trim($page, '/');

        if ($page === '' || $page === 'index') {
            $filePath = Yii::getAlias('@app/README.md');
            $this->currentPage = '__readme__';
        } else {
            $filePath = Yii::getAlias("@app/docs/{$page}.md");
            $this->currentPage = $page;
        }

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException("Page '{$page}' not found");
        }

        $markdown = file_get_contents($filePath);

        // Исправление относительных ссылок на абсолютные с удалением .md
        $markdown = preg_replace_callback('/\[([^\]]+)\]\((?:\.\/)?docs\/([^)]+)\)/', function($matches) {
            $path = preg_replace('/\.md(#.*)?$/', '$1', $matches[2]);
            $url = '/docs/' . $path;
            return "[{$matches[1]}]({$url})";
        }, $markdown);

        $parser = new GithubMarkdown();
        $parser->enableNewlines = true;
        $content = $parser->parse($markdown);

        // Добавляем id заголовкам
        $content = preg_replace_callback('/<h([1-6])>(.*?)<\/h\1>/', function ($m) {
            $id = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', strip_tags($m[2]))));
            return "<h{$m[1]} id=\"{$id}\">{$m[2]}</h{$m[1]}>";
        }, $content);

        $content = HtmlPurifier::process($content);

        return $this->render('@core/presentation/view/docs/ui', [
            'content' => $content,
            'menu' => $this->generateMenu(),
        ]);
    }

    private function generateMenu()
    {
        $docsPath = Yii::getAlias("@app/docs");
        $files = FileHelper::findFiles($docsPath, ['only' => ['*.md']]);

        // Собираем элементы
        $items = [];

        // Overview (README)
        $items['__overview'] = [
            'url' => '/docs',
            'label' => 'Overview',
            'active' => ($this->currentPage === '__readme__'),
            'depth' => 0,
            'dir' => '',
        ];

        foreach ($files as $file) {
            $relativePath = str_replace($docsPath . DIRECTORY_SEPARATOR, '', $file);
            $page = str_replace('.md', '', $relativePath);
            $page = str_replace('\\', '/', $page);
            $depth = substr_count($page, '/');
            $dir = $depth > 0 ? dirname($page) : '';
            $label = $this->extractTitle($file) ?: basename($page);
            $items[$page] = [
                'url' => "/docs/{$page}",
                'label' => $label,
                'active' => ($page === $this->currentPage),
                'depth' => $depth,
                'dir' => $dir,
            ];
        }

        // Разделяем корневые и папки
        $rootItems = [];
        $dirItems = [];
        foreach ($items as $key => $item) {
            if ($item['depth'] == 0) {
                $rootItems[] = $item;
            } else {
                $dirItems[$item['dir']][] = $item;
            }
        }

        // Кастомный порядок корневых страниц (укажите нужный)
        $rootOrder = ['architect', 'contribute', 'opportunities', 'uml'];
        usort($rootItems, function($a, $b) use ($rootOrder) {
            $aKey = str_replace('/docs/', '', $a['url']);
            $bKey = str_replace('/docs/', '', $b['url']);
            $posA = array_search($aKey, $rootOrder);
            $posB = array_search($bKey, $rootOrder);
            if ($posA !== false && $posB !== false) return $posA - $posB;
            if ($posA !== false) return -1;
            if ($posB !== false) return 1;
            return strcmp($a['label'], $b['label']);
        });

        // Кастомный порядок папок
        $folderOrder = ['core', 'project', 'task'];
        $dirNames = array_keys($dirItems);
        usort($dirNames, function($a, $b) use ($folderOrder) {
            $posA = array_search($a, $folderOrder);
            $posB = array_search($b, $folderOrder);
            if ($posA !== false && $posB !== false) return $posA - $posB;
            if ($posA !== false) return -1;
            if ($posB !== false) return 1;
            return strcmp($a, $b);
        });

        // Рендер элементов
        $renderList = function($list) {
            if (empty($list)) return '';
            $html = '<ul class="nav-pills">';
            foreach ($list as $item) {
                $html .= '<li class="' . ($item['active'] ? 'active' : '') . '">';
                $html .= '<a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['label']) . '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
            return $html;
        };

        $menuHtml = $renderList($rootItems);

        foreach ($dirNames as $dirName) {
            $itemsInDir = $dirItems[$dirName];
            // Сортируем внутри папки по label (алфавит)
            usort($itemsInDir, fn($a, $b) => strcmp($a['label'], $b['label']));
            $menuHtml .= '<div class="doc-folder">';
            $menuHtml .= '<div class="folder-label">' . htmlspecialchars(ucfirst($dirName)) . '</div>';
            $menuHtml .= $renderList($itemsInDir);
            $menuHtml .= '</div>';
        }

        return $menuHtml;
    }

    private function extractTitle($file)
    {
        $content = file_get_contents($file);
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}