<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

@ini_set("max_execution_time", "600");
define('MOBICMS', 1);

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';
$do = isset($_REQUEST['do']) ? trim($_REQUEST['do']) : false;

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

// Проверяем права доступа
if ($systemUser->rights < 6) {
    echo _t('Access denied');
    require ROOT_PATH . 'system/end.php';
    exit;
}

$headmod = 'admin';
$textl = _t('Admin Panel');
require ROOT_PATH . 'system/head.php';

$array = [
    'forum',
    'news',
    'ads',
    'counters',
    'ip_whois',
    'languages',
    'settings',
    'smilies',
    'access',
    'antispy',
    'httpaf',
    'ipban',
    'antiflood',
    'ban_panel',
    'karma',
    'reg',
    'mail',
    'search_ip',
    'usr',
    'usr_adm',
    'usr_clean',
    'usr_del',
];

if (!empty($act) && in_array($act, $array) && is_file(__DIR__ . '/includes/' . $act . '.php')) {
    require(__DIR__ . '/includes/' . $act . '.php');
} else {
    $regtotal = $db->query("SELECT COUNT(*) FROM `users` WHERE `preg`='0'")->fetchColumn();
    $bantotal = $db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `ban_time` > '" . time() . "'")->fetchColumn();
    echo '<div class="phdr"><b>' . _t('Admin Panel') . '</b></div>';

    // Блок пользователей
    echo '<div class="user"><p><h3>' . _t('Users') . '</h3><ul>';

    if ($regtotal && $systemUser->rights >= 6) {
        echo '<li><span class="red"><b><a href="index.php?act=reg">' . _t('On registration') . '</a>&#160;(' . $regtotal . ')</b></span></li>';
    }

    echo '<li><a href="index.php?act=usr">' . _t('Users') . '</a>&#160;(' . $container->get('counters')->users() . ')</li>' .
        '<li><a href="index.php?act=usr_adm">' . _t('Administration') . '</a>&#160;(' . $db->query("SELECT COUNT(*) FROM `users` WHERE `rights` >= '1'")->fetchColumn() . ')</li>' .
        ($systemUser->rights >= 7 ? '<li><a href="index.php?act=usr_clean">' . _t('Database cleanup') . '</a></li>' : '') .
        '<li><a href="index.php?act=ban_panel">' . _t('Ban Panel') . '</a>&#160;(' . $bantotal . ')</li>' .
        ($systemUser->rights >= 7 ? '<li><a href="index.php?act=antiflood">' . _t('Antiflood') . '</a></li>' : '') .
        ($systemUser->rights >= 7 ? '<li><a href="index.php?act=karma">' . _t('Karma') . '</a></li>' : '') .
        '<br>' .
        '<li><a href="../users/search.php">' . _t('Search by Nickname') . '</a></li>' .
        '<li><a href="index.php?act=search_ip">' . _t('Search IP') . '</a></li>' .
        '</ul></p></div>';

    if ($systemUser->rights >= 7) {
        // Блок модулей
        $spam = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `spam`='1';")->fetchColumn();

        echo '<div class="gmenu"><p>';
        echo '<h3>' . _t('Modules') . '</h3><ul>' .
            '<li><a href="index.php?act=forum">' . _t('Forum') . '</a></li>' .
            '<li><a href="index.php?act=news">' . _t('News') . '</a></li>' .
            '<li><a href="index.php?act=ads">' . _t('Advertisement') . '</a></li>';

        if ($systemUser->rights == 9) {
            echo '<li><a href="index.php?act=counters">' . _t('Counters') . '</a></li>';
        }

        echo '</ul></p></div>';

        // Блок системных настроек
        echo '<div class="menu"><p>' .
            '<h3>' . _t('System') . '</h3>' .
            '<ul>' .
            ($systemUser->rights == 9 ? '<li><a href="index.php?act=settings"><b>' . _t('System Settings') . '</b></a></li>' : '') .
            '<li><a href="index.php?act=smilies">' . _t('Update Smilies') . '</a></li>' .
            ($systemUser->rights == 9 ? '<li><a href="index.php?act=languages">' . _t('Language Settings') . '</a></li>' : '') .
            '<li><a href="index.php?act=access">' . _t('Permissions') . '</a></li>' .
            '</ul>' .
            '</p></div>';

        // Блок безопасности
        echo '<div class="rmenu"><p>' .
            '<h3>' . _t('Security') . '</h3>' .
            '<ul>' .
            '<li><a href="index.php?act=antispy">' . _t('Anti-Spyware') . '</a></li>' .
            ($systemUser->rights == 9 ? '<li><a href="index.php?act=ipban">' . _t('Ban by IP') . '</a></li>' : '') .
            '</ul>' .
            '</p></div>';
    }
    echo '<div class="phdr" style="font-size: x-small"><b>mobiCMS 0.2.0</b></div>';
}

require ROOT_PATH . 'system/end.php';
