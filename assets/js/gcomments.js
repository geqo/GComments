document.addEventListener('DOMContentLoaded', function() {
    var widget;

    var enableCaptcha = false;

    if (pubKey !== '') {
        enableCaptcha = true;
    }

    if (enableCaptcha) {
        var interval = setInterval(function(){
            if(window.grecaptcha){
                var captcha = document.getElementById('recaptcha');
                widget = grecaptcha.render(captcha, {
                    'sitekey' : pubKey,
                    'theme' : 'light'
                });
                clearInterval(interval);
            }
        }, 100);
    }

    jQuery('.gcomments-more').on('click', function(e) {
        e.preventDefault();
        if (start < total) {
            getData(start);
            start += limit;
        }
    });

    jQuery('#gcomments-form').on('submit', function (e) {
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
            jQuery('#gsubmit').prop('disabled', true);
        },
        complete: function () {
            jQuery('#gsubmit').prop('disabled', false);
            if (window.grecaptcha) {
                grecaptcha.reset();
            }
        },
        success: function(data) {
            if (data.success === true) {
                jQuery('#gtext').val('');
                addMessage(data.data);
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
        url: 'index.php?option=com_ajax&format=json&module=gcomments&method=removeComment',
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
            gcontext: context,
            glimit: limit,
            gitem_id: itemId,
            gstart: lstart
        },
        type: 'GET',
        url: 'index.php?option=com_ajax&format=json&module=gcomments&method=getComments',
        dataType: 'json',
        success: function(data) {
            if (data.success === true) {
                makeComments(data.data);
                if (start >= total) {
                    jQuery('.gcomments-loader').hide();
                }
            }
        }
    });
}

function makeComments(data) {
    var messages = [];
    jQuery.each(data, function(index, value) {
        messages.unshift(value);
    });
    jQuery.each(messages, function(index, value) {
        jQuery('.gcomments').append(
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

function addMessage(data) {
    var message = getBlock(data);
    jQuery('.gcomments').prepend(message);
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
        '</div>' +
        '</div>';
}

function getAction(id) {
    if (isAdmin) {
        return '<div class="gcomment-action">' +
            '<button class="gcomment-delete" onclick="deleteComment(this.dataset.comment)" data-comment="' + id + '">' + deleteButton + '</button>' +
            '</div>';
    }
    return '';
}
