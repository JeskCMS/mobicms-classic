<?php

defined('MOBICMS') or die('Error: restricted access');

$obj = new Library\Hashtags(0);

if (isset($_GET['tag'])) {
    /** @var Psr\Container\ContainerInterface $container */
    $container = App::getContainer();

    /** @var PDO $db */
    $db = $container->get(PDO::class);

    /** @var Mobicms\Checkpoint\UserConfig $userConfig */
    $userConfig = $container->get(Mobicms\Api\UserInterface::class)->getConfig();

    /** @var Mobicms\Api\ToolsInterface $tools */
    $tools = $container->get(Mobicms\Api\ToolsInterface::class);

    $tag = urldecode($_GET['tag']);

    if ($obj->getAllTagStats($tag)) {
        $total = sizeof($obj->getAllTagStats($tag));
        $page = $page >= ceil($total / $userConfig->kmess) ? ceil($total / $userConfig->kmess) : $page;
        $start = $page == 1 ? 0 : ($page - 1) * $userConfig->kmess;

        echo '<div class="phdr"><a href="?"><strong>' . _t('Library') . '</strong></a> | ' . _t('Tags') . '</div>';

        if ($total > $userConfig->kmess) {
            echo '<div class="topmenu">' . $tools->displayPagination('?act=tags&amp;tag=' . urlencode($tag) . '&amp;',
                    $start, $total, $userConfig->kmess) . '</div>';
        }

        foreach (new LimitIterator(new ArrayIterator($obj->getAllTagStats($tag)), $start, $userConfig->kmess) as $txt) {
            $row = $db->query("SELECT `id`, `name`, `time`, `uploader`, `uploader_id`, `count_views`, `comm_count`, `comments` FROM `library_texts` WHERE `id` = " . $txt)->fetch();
            $obj = new Library\Hashtags($row['id']);
            echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">'
                . (file_exists('../files/library/images/small/' . $row['id'] . '.png')
                    ? '<div class="avatar"><img src="../files/library/images/small/' . $row['id'] . '.png" alt="screen" /></div>'
                    : '')
                . '<div class="righttable"><a href="index.php?id=' . $row['id'] . '">' . $tools->checkout($row['name']) . '</a>'
                . '<div>' . $tools->checkout($db->query("SELECT SUBSTRING(`text`, 1 , 200) FROM `library_texts` WHERE `id`=" . $row['id'])->fetchColumn(), 0, 2) . '</div></div>'
                . '<div class="sub">' . _t('Who added') . ': ' . '<a href="' . App::getContainer()->get('config')['mobicms']['homeurl'] . '/profile/?user=' . $row['uploader_id'] . '">' . $tools->checkout($row['uploader']) . '</a>' . ' (' . $tools->displayDate($row['time']) . ')</div>'
                . '<div><span class="gray">' . _t('Number of readings') . ':</span> ' . $row['count_views'] . '</div>'
                . '<div>' . ($obj->getAllStatTags() ? _t('Tags') . ' [ ' . $obj->getAllStatTags(1) . ' ]' : '') . '</div>'
                . ($row['comments'] ? '<div><a href="?act=comments&amp;id=' . $row['id'] . '">' . _t('Comments') . '</a> (' . $row['comm_count'] . ')</div>' : '')
                . '</div>';
        }

        echo '<div class="phdr">' . _t('Total') . ': ' . intval($total) . '</div>';

        if ($total > $userConfig->kmess) {
            echo '<div class="topmenu">' . $tools->displayPagination('?act=tags&amp;tag=' . urlencode($tag) . '&amp;', $start, $total, $userConfig->kmess) . '</div>';
        }
        echo '<p><a href="?">' . _t('To Library') . '</a></p>';
    }
} else {
    Library\Utils::redir404();
}
