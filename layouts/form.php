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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

Text::script('MOD_GCOMMENTS_CAPTCHA_VALIDATION_ERROR');
Text::script('MOD_GCOMMENTS_EMPTY_FORM_ERROR');

$formTitle = $params->get('form-text', '');

?>

<div class="gcomment gcomments-form-block">
    <?php if ($formTitle) : ?>
    <div class="gcomment-head">
        <?php echo Text::_($formTitle) ?>
    </div>
    <?php endif; ?>
    <div class="gerror"></div>
    <div class="gcomment-body">
        <form action="/index.php?option=com_ajax&format=json&module=gcomments&method=addComment" data-item-id="<?php echo $item_id; ?>" method="post" class="gcomments-form">
            <div class="control-group ginput-block">
                <label class="glabel " for="gusername"><?php echo Text::_('MOD_GCOMMENTS_USERNAME_LABEL'); ?></label>
                <input
                        class="gusername"
                        type="text"
                        name="gusername"
                        placeholder="<?php echo Text::_('MOD_GCOMMENTS_USERNAME_LABEL'); ?>"
                        <?php echo (! $user->guest ? 'value="' . $user->username . '"' :''); ?>
                        <?php echo (! $user->guest && ! $user->get('isRoot') ? 'disabled="disabled"' :''); ?>
                        required="required"
                        class="required<?php echo (! $user->guest ? ' disabled' :''); ?>"
                >
            </div>
            <div class="control-group ginput-block">
                <label class="glabel " for="gemail"><?php echo Text::_('MOD_GCOMMENTS_EMAIL_LABEL'); ?></label>
                <input
                        class="gemail"
                        type="email"
                        name="gemail"
                        placeholder="<?php echo Text::_('MOD_GCOMMENTS_EMAIL_LABEL'); ?>"
                        <?php echo (! $user->guest ? 'value="' . $user->email . '"' :''); ?>
                        <?php echo (! $user->guest && ! $user->get('isRoot') ? 'disabled="disabled"' :''); ?>
                        required="required"
                        class="required<?php echo (! $user->guest ? ' disabled' :''); ?>"
                >
            </div>
            <div class="control-group ginput-block">
                <label class="glabel " for="gtext"><?php echo Text::_('MOD_GCOMMENTS_TEXT_LABEL'); ?></label>
                <textarea name="gtext" class="gtext" cols="30" rows="10"></textarea>
            </div>
            <input type="hidden" name="context" value="<?php echo $context; ?>">
            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
            <?php if ($captcha === 1) : ?>
                <div id="recaptcha"></div>
            <?php endif; ?>
            <button class="gsubmit" type="submit"><?php echo Text::_('JSUBMIT'); ?></button>
        </form>
    </div>
</div>