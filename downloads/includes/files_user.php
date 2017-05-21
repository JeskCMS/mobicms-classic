<?php

defined('MOBICMS') or die('Error: restricted access');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $systemUser->getConfig();

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

// Файлы юзера
$textl = _t('User Files');

require 'classes/download.php';
require '../system/head.php';

if (($user = $tools->getUser($id)) === false) {
    echo _t('User does not exists');
    require '../system/end.php';
    exit;
}

echo '<div class="phdr"><a href="/profile?user=' . $id . '">' . _t('Profile') . '</a></div>' .
    '<div class="user"><p>' . $tools->displayUser($user, ['iphide' => 0]) . '</p></div>' .
    '<div class="phdr"><b>' . _t('User Files') . '</b></div>';

$total = $db->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '2'  AND `user_id` = " . $id)->fetchColumn();

// Навигация
if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('?act=user_files&amp;id=' . $id . '&amp;', $start, $total, $userConfig->kmess) . '</div>';
}

// Список файлов
$i = 0;

if ($total) {
    $req_down = $db->query("SELECT * FROM `download__files` WHERE `type` = '2'  AND `user_id` = " . $id . " ORDER BY `time` DESC LIMIT $start, $userConfig->kmess");

    while ($res_down = $req_down->fetch()) {
        echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down) . '</div>';
    }
} else {
    echo '<div class="rmenu"><p>' . _t('The list is empty') . '</p></div>';
}

echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

// Навигация
if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('?act=user_files&amp;id=' . $id . '&amp;', $start, $total, $userConfig->kmess) . '</div>' .
        '<p><form action="?" method="get">' .
        '<input type="hidden" name="USER" value="' . $id . '"/>' .
        '<input type="hidden" value="user_files" name="act" />' .
        '<input type="text" name="page" size="2"/><input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
}

echo '<p><a href="?">' . _t('Downloads') . '</a></p>';
require '../system/end.php';
