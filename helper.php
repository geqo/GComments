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
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * @package     Joomla.Module
 * @subpackage  mod_gcomments
 * @since       version
 */
class ModGCommentsHelper
{
	/**
	 * Ajax proxy for getComments method
	 * @return array
	 * @since 0.7.0
	 * @throws Exception
	 */
	public static function getCommentsAjax()
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$context = $input->get('gcontext', 'com_content.article');
		$id      = $input->get('gitem_id', 0);
		$start   = $input->get('gstart', 0);
		$limit   = $input->get('glimit', 10);

		return self::getComments($context, $id, $start, $limit);
	}

	/**
	 * @param string $context    example:com_content.article
	 * @param int $id            Item id
	 * @param int $start
	 * @param int $limit
	 * @return array
	 * @since 0.7.0
	 */
	public static function getComments($context, $id, $start = 0, $limit = 10)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query
			->select('*')
			->from($db->qn('#__gcomments_comments'))
			->where($db->qn('context') . ' = ' . $db->q($context))
			->where($db->qn('bind_id') . ' = ' . $db->q($id))
			->where($db->qn('deleted') . ' = 0')
			->order($db->qn('creation_date') . ' DESC');

		$db->setQuery($query, $start, $limit);

		$result = $db->loadAssocList('id') ? : [];

		return $result;
	}

	/**
	 *
	 * @return bool
	 *
	 * @since 0.8.2
	 * @throws Exception
	 */
	public static function removeCommentAjax()
	{
		$user = Factory::getUser();
		$input = Factory::getApplication()->input;
		$id = (int) $input->getInt('comment_id', 0);

		if (
			$user->guest ||
			! in_array(8, $user->groups) ||
			$id === 0
		) {
			throw new Exception('Access denied!');
		}

		return self::removeComment($id);
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 *
	 * @since 0.8.2
	 */
	private static function removeComment($id)
	{
		$db = Factory::getDbo();
		$comment = new stdClass();
		$comment->id = $id;
		$comment->deleted = 1;
		return $db->updateObject('#__gcomments_comments',$comment, 'id');
	}

	/**
	 * Get total comments count for item
	 *
	 * @param string $context example:com_content.article
	 * @param int $id Item id
	 * @return mixed
	 * @since 0.7.0
	 */
	public static function getTotal($context, $id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query
			->select('COUNT(id)')
			->from($db->qn('#__gcomments_comments'))
			->where($db->qn('context') . ' = ' . $db->q($context))
			->where($db->qn('bind_id') . ' = ' . $db->q($id))
			->where($db->qn('deleted') . ' = 0');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * @since 0.7.0
	 * @throws Exception Throwing if form contain empty fields
	 */
	public static function addCommentAjax()
	{
		$app   = Factory::getApplication();
		$user  = Factory::getUser();
		$input = $app->input;
		$data  = [
			'gusername' => $user->guest ? trim($input->get('gusername', '', 'safehtml')) : $user->username,
			'gemail'    => $user->guest ? trim($input->get('gemail', '', 'safehtml')) : $user->email,
			'gtext'     => trim($input->get('gtext', '', 'safehtml')),
			'context'   => $input->get('context', '', 'cmd'),
			'item_id'   => $input->get('item_id', '', 'int'),
		];

		$captcha = $input->get('g-recaptcha-response', null);
		$params = self::getParams();

		if (! $data || in_array('', $data)) {
			throw new Exception(Text::_('MOD_GCOMMENTS_EMPTY_FORM_ERROR'));
		}

		if ((int) $params->get('show-captcha', 1) === 1) {
			self::checkCaptcha($captcha);
		}

		return self::saveMessage($data);
	}

	/**
	 * @param string $captcha
	 * @since 0.7.0
	 * @throws Exception Throws if captcha empty or invalid
	 */
	public static function checkCaptcha($captcha)
	{
		if (! $captcha) {
			throw new Exception(Text::_('MOD_GCOMMENTS_CAPTCHA_VALIDATION_ERROR'));
		}

		$params = self::getParams();

		$data = http_build_query([
			'secret' => $params->get('secret-key'),
			'response' => $captcha,
			'remoteip' => $_SERVER['REMOTE_ADDR']
		]);

		$opts = [
			'http' => [
				'method' => 'POST',
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'content' => $data,
			],
		];

		$context  = stream_context_create($opts);
		$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
		$result   = json_decode($response);

		if (! $result->success) {
			throw new Exception(Text::_('MOD_GCOMMENTS_CAPTCHA_VALIDATION_ERROR'));
		}
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @since 0.7.0
	 */
	protected static function saveMessage($data)
	{
		$user = Factory::getUser();
		$db = Factory::getDbo();
		$message = new stdClass();
		$message->user_name = $data['gusername'];
		$message->user_id = $user->guest ? 0 : $user->id;
		$message->user_email = $data['gemail'];
		$message->text = $data['gtext'];
		$message->bind_id = $data['item_id'];
		$message->context = $data['context'];
		$message->user_ip = $_SERVER['REMOTE_ADDR'];

		$db->insertObject('#__gcomments_comments', $message);

		$last = $db->insertid();

		$query = $db->getQuery(true);
		$query
			->select($db->qn([
				'user_name',
				'creation_date',
				'text',
			]))
			->from($db->qn('#__gcomments_comments'))
			->where($db->qn('id') . ' = ' . $db->q($last));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * @return Registry
	 * @since 0.7.0
	 */
	public static function getParams()
	{
		$module = ModuleHelper::getModule('gcomments');
		return new Registry($module->params);
	}
}
