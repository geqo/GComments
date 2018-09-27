<?php
/*
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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
?>

<div class="gcomments">
	<?php if (isset($comments) || $comments)  : ?>
		<?php foreach ($comments as $key => $comment) : ?>
            <div class="gcomment">
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
            </div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php if ($total > $limit) : ?>
    <div class="gcomments-loader">
        <a href="" class="gcomments-more"><?php echo Text::_('MOD_GCOMMENTS_LOAD_MORE'); ?></a>
    </div>
<?php endif; ?>

<?php
if ($formLayout !== 'none') {
	include __DIR__ . '/../layouts/' . $formLayout . '.php';
}
?>

<script>
    var limit = <?php echo $limit; ?>,
        itemId = <?php echo $item_id; ?>,
        context = '<?php echo $context; ?>',
        start = <?php echo ($start + $limit); ?>,
        total = <?php echo $total; ?>,
        pubKey = '<?php echo $pub_key ?>';
</script>