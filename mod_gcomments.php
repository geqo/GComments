<?php
/*
    Licensed under the GNU AGPL v3
    This file is part of GComments module (mod_gcomments)
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.
    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

JLoader::register('ModGCommentsHelper', __DIR__ . '/helper.php');

$user     = Factory::getUser();
$input    = Factory::getApplication()->input;
$document = Factory::getDocument();
$layout   = $params->get('layout', 'default');
$limit    = (int) $params->get('comments-limit', 10);
$option   = $input->get('option', '');
$view     = $input->get('view', '');
$item_id  = 0;

if ($option === 'com_content' && $view === 'article') {
    $context = 'com_content.article';
}

if (isset($attribs['context'])) {
    $context  = $attribs['context'];
}
if (isset($attribs['id'])) {
    $item_id  = $attribs['id'];
}
if ($context === '') {
    return;
}


$item_id  = (int) $item_id ? : $input->get('id', 0);
$start    = 0;
$captcha  = (int) $params->get('show-captcha', 1);
$pub_key  = '';
$sec_key  = '';

if ($captcha === 1) {
    $pub_key = $params->get('public-key', null);
    $sec_key = $params->get('secret-key', null);
}

if (! $pub_key || ! $sec_key) {
    $captcha === 0;
}

$formLayout = 'form';

$comments = ModGCommentsHelper::getComments($context, $item_id, $start, $limit);
$total    = ModGCommentsHelper::getTotal($context, $item_id);
$itemId = $item_id;
$pubKey = $pub_key;
$isAdmin = $user->get('isRoot') ? 'true' : 'false';

require ModuleHelper::getLayoutPath('mod_gcomments', $layout);
