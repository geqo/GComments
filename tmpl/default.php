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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

HTMLHelper::stylesheet(Uri::base() . '/media/mod_gcomments/css/gcomments.css');
HTMLHelper::script(Uri::base() . '/media/mod_gcomments/js/gcomments.js');

if ($captcha === 1) {
    HTMLHelper::script('https://www.google.com/recaptcha/api.js');
}

if ($formOnTop) {
    include __DIR__ . '/../layouts/' . $formLayout . '.php';
}
?>

<div class="gcomments" data-item-id="<?php echo $item_id ?>">
	<?php if (isset($comments) || $comments)  : ?>
		<?php foreach ($comments as $key => $comment) : ?>
            <div class="gcomment" data-comment-block="<?php echo $comment['id'] ?>">
                <div class="gcomment-head">
                <span class="gcomment-username">
                    <?php echo $comment['user_name']; ?>
                </span>
                    <span class="gcomment-date">
                    <?php echo $comment['creation_date']; ?>
                </span>
                </div>
                <div class="gcomment-body">
					<?php echo $comment['text']; ?>
                </div>
				<?php if ($user->get('isRoot')) : ?>
                    <div class="gcomment-action">
                        <button class="gcomment-delete" onclick="deleteComment(this.dataset.comment)" data-comment="<?php echo $comment['id']; ?>"><?php echo Text::_('MOD_GCOMMENTS_DELETE_COMMENT') ?></button>
                    </div>
				<?php endif; ?>
            </div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php if ($total > $limit) : ?>
    <div class="gcomments-loader">
        <a href="" class="gcomments-more" data-item-id="<?php echo $item_id ?>"><?php echo Text::_('MOD_GCOMMENTS_LOAD_MORE'); ?></a>
    </div>
<?php endif; ?>

<?php
if ($formLayout !== 'none' && ! $formOnTop) {
	include __DIR__ . '/../layouts/' . $formLayout . '.php';
}
?>

<script>
    var gcomments_limit = <?php echo $limit; ?>,
        gcomments_itemId = '<?php echo $item_id; ?>',
        gcomments_context = '<?php echo $context; ?>',
        gcomments_start = <?php echo ($start + $limit); ?>,
        gcomments_total = <?php echo $total; ?>,
        gcomments_pubKey = '<?php echo $pub_key ?>',
        gcomments_deleteButton = '<?php echo Text::_('MOD_GCOMMENTS_DELETE_COMMENT') ?>',
        gcomments_isAdmin = <?php echo $user->get('isRoot') ? 'true' : 'false' ?>,
        gcomments_order = <?php echo $order; ?>,
        gcomments_base = '<?php echo \Joomla\CMS\Uri\Uri::base(); ?>';
</script>