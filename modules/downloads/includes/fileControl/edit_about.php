<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

defined('MOBICMS') or die('Error: restricted access');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Http\Response $response */
$response = $container->get(Mobicms\Http\Response::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

require ROOT_PATH . 'system/head.php';

// Редактирование описания файла
$req_down = $db->query("SELECT * FROM `download__files` WHERE `id` = '" . $id . "' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $req_down->fetch();

if (!$req_down->rowCount() || !is_file($res_down['dir'] . '/' . $res_down['name']) || ($systemUser->rights < 6 && $systemUser->rights != 4)) {
    echo '<a href="?">' . _t('Downloads') . '</a>';
    require ROOT_PATH . 'system/end.php';
    exit;
}

if (isset($_POST['submit'])) {
    $text = isset($_POST['opis']) ? trim($_POST['opis']) : '';

    $stmt = $db->prepare("
        UPDATE `download__files` SET
        `about`    = ?
        WHERE `id` = ?
    ");

    $stmt->execute([
        $text,
        $id,
    ]);

    $response->redirect('?act=view&id=' . $id)->sendHeaders();
} else {
    echo '<div class="phdr"><b>' . _t('Description') . ':</b> ' . htmlspecialchars($res_down['rus_name']) . '</div>' .
        '<div class="list1"><form action="?act=edit_about&amp;id=' . $id . '" method="post"><p>' .
        '<small>' . _t('Maximum 500 characters') . '</small><br>' .
        '<textarea name="opis">' . htmlentities($res_down['about'], ENT_QUOTES, 'UTF-8') . '</textarea><br>' .
        '<input type="submit" name="submit" value="' . _t('Save') . '"/></p></form></div>' .
        '<div class="phdr"><a href="?act=view&amp;id=' . $id . '">' . _t('Back') . '</a></div>';
}

require ROOT_PATH . 'system/end.php';
