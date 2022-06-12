document.addEventListener('DOMContentLoaded', function() {
    var widget;

    var enableCaptcha = false;

    if (gcomments_pubKey !== '') {
        enableCaptcha = true;
    }

    if (enableCaptcha) {
        var interval = setInterval(function(){
            if(window.grecaptcha){
                var captcha = document.getElementById('recaptcha');
                widget = grecaptcha.render(captcha, {
                    'sitekey' : gcomments_pubKey,
                    'theme' : 'light'
                });
                clearInterval(interval);
            }
        }, 100);
    }

    jQuery('.gcomments-more').on('click', function(e) {
        e.preventDefault();
        if (gcomments_start < gcomments_total) {
            getData(gcomments_start, jQuery(this).data('item-id'));
            gcomments_start += gcomments_limit;
        }
    });

    jQuery('.gcomments-form').on('submit', function (e) {
        e.preventDefault();
        if (! enableCaptcha) {
            return submitComment(this);
        }
        if (checkCaptcha() === true) {
            submitComment(this);
        } else {
            raiseError(Joomla.JText._('MOD_GCOMMENTS_CAPTCHA_VALIDATION_ERROR'));
        }
    });
});

function checkCaptcha() {
    var captcha = jQuery('#g-recaptcha-response');
    try {
        return captcha.val() !== undefined && captcha.val() !== '';
    } catch (err) {
        console.log(err);
        return false;
    }
}

function submitComment(form) {
    jQuery.ajax({
        data: jQuery(form).serialize(),
        url: jQuery(form).attr('action'),
        method: jQuery(form).attr('method'),
        dataType: 'json',
        beforeSend: function() {
            hideError();
            jQuery('.gsubmit').prop('disabled', true);
        },
        complete: function () {
            jQuery('.gsubmit').prop('disabled', false);
            if (window.grecaptcha) {
                grecaptcha.reset();
            }
        },
        success: function(data) {
            if (data.success === true) {
                jQuery(form).find('.gtext').val('');
                addMessage(data.data, jQuery(form).data('item-id'));
            } else {
                raiseError(data.message);
            }
        }
    });
}

function deleteComment(id) {
    jQuery.ajax({
        data: {
            comment_id: id
        },
        type: 'POST',
        url: '../index.php?option=com_ajax&format=json&module=gcomments&method=removeComment',
        dataType: 'json',
        success: function(data) {
            if (data.success === true) {
                jQuery('[data-comment-block="' + id +'"]').toggle();
            } else {
                console.log(data);
                alert(data.message);
            }
        }
    });
}

function raiseError(error) {
    var errorBlock = jQuery('.gerror');
    jQuery(errorBlock).text(error);
    jQuery(errorBlock).show();
}

function hideError() {
    jQuery('.gerror').hide();
}

function getData(lstart) {

    jQuery.ajax({
        data: {
            gcontext: gcomments_context,
            glimit: gcomments_limit,
            gitem_id: gcomments_itemId,
            gstart: lstart
        },
        type: 'GET',
        url: '../index.php?option=com_ajax&format=json&module=gcomments&method=getComments',
        dataType: 'json',
        success: function(data) {
            if (data.success === true) {
                makeComments(data.data, gcomments_itemId);
                if (gcomments_start >= gcomments_total) {
                    jQuery('.gcomments-loader').hide();
                }
            }
        }
    });
}

function makeComments(data, itemId) {
    var messages = [];
    jQuery.each(data, function(index, value) {
        messages.unshift(value);
    });
    jQuery.each(messages, function(index, value) {
        jQuery('.gcomments[data-item-id="' + itemId + '"]').append(
            '<div class="gcomment" data-comment-block="' + value.id + '">' +
            '<div class="gcomment-head">' +
            '<span class="gcomment-username">' +
            value.user_name +
            '</span>' +
            '<span class="gcomment-date">' +
            value.creation_date +
            '</span>' +
            '</div>' +
            '<div class="gcomment-body">' +
            value.text +
            '</div>' + getAction(value.id) +
            '</div>'
        );
    })
}

function addMessage(data, item_id) {
    let message = getBlock(data);
    if (! gcomments_order) {
        jQuery('.gcomments[data-item-id="' + item_id + '"]').prepend(message);
    } else {
        jQuery('.gcomments[data-item-id="' + item_id + '"]').append(message);
    }
}

function getBlock(data) {
    return '<div class="gcomment">' +
        '<div class="gcomment-head">' +
        '<span class="gcomment-username">' +
        data.user_name +
        '</span>' +
        '<span class="gcomment-date">' +
        data.creation_date +
        '</span>' +
        '</div>' +
        '<div class="gcomment-body">' +
        data.text +
        '</div>' + getAction(data.id) +
        '</div>';
}

function getAction(id) {
    if (gcomments_isAdmin) {
        return '<div class="gcomment-action">' +
            '<button class="gcomment-delete" onclick="deleteComment(this.dataset.comment)" data-comment="' + id + '">' + gcomments_deleteButton + '</button>' +
            '</div>';
    }
    return '';
}
