document.addEventListener('DOMContentLoaded', function () {
    var widget;

    var enableCaptcha = false;

    if (pubKey !== '') {
        enableCaptcha = true;
    }

    if (enableCaptcha) {
        let interval = setInterval(function () {
            if (window.grecaptcha) {
                let captcha = document.getElementById('recaptcha');
                widget = grecaptcha.render(captcha, {
                    'sitekey': pubKey,
                    'theme': 'light'
                });
                clearInterval(interval);
            }
        }, 100);
    }

    let loadMore = document.getElementById('gcomments-more');

    if (loadMore) {
        loadMore.onclick = function(e) {
            e.preventDefault();
            if (start < total) {
                getData(start);
                start += limit;
            }
        };
    }

    let gcommentsForm = document.getElementById('gcomments-form');
    gcommentsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!enableCaptcha) {
            return submitComment(this);
        }
        if (checkCaptcha() === true) {
            submitComment(this);
        } else {
            raiseError(Joomla.JText._('MOD_GCOMMENTS_CAPTCHA_VALIDATION_ERROR'));
        }
    });

    Object.objectLength = function(obj) {
        let size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) size++;
        }
        return size;
    };
});

function checkCaptcha() {
    let captcha = document.getElementById('g-recaptcha-response');
    try {
        return captcha.val() !== undefined && captcha.val() !== '';
    } catch (err) {
        console.log(err);
        return false;
    }
}

function submitComment(form) {
    let submit = document.getElementById('gsubmit'),
        action = form.attributes.action.value,
        inputs = document.getElementsByClassName('ginput'),
        data = {};

    for (let input in inputs) {
        if (inputs.hasOwnProperty(input)) {
            data[inputs[input].name] = inputs[input].value;
        }
    }

    sendRequest(action, data, function () {
        submit.disabled = this.readyState !== 4;
        if (window.grecaptcha) {
            grecaptcha.reset();
        }
        if (this.response === false) {
            raiseError('Unknown server error');
            return;
        }
        if (this.response.success === false) {
            raiseError(this.response.message);
            return;
        }
        document.getElementById('gtext').value = '';
        appendComment(this.response.data, true);
    });
}

function deleteComment(id) {
    sendRequest(
        'index.php?option=com_ajax&format=json&module=gcomments&method=removeComment',
        {
            comment_id: id
        }, function () {
            if (this.response === false) {
                raiseError('Unknown server error');
                return;
            }
            if (this.response.success === false) {
                raiseError(this.response.message);
                return;
            }

            let commentBlock = document.querySelector('[data-comment-block="' + id + '"]');
            commentBlock.style.display = 'none';
        }
    );
}

function raiseError(error) {
    let errorBlock = document.getElementsByClassName('gerror')[0];
    errorBlock.innerText = error;
    errorBlock.style.display = 'block';
}

function hideError() {
    let errorBlock = document.getElementsByClassName('gerror')[0];
    errorBlock.style.display = 'none';
}

function getData(lstart) {
    let data = {
        gcontext: context,
        glimit: limit,
        gitem_id: itemId,
        gstart: lstart
    };

    sendRequest(
        'index.php?option=com_ajax&format=json&module=gcomments&method=getComments',
        data,
        function () {
            if (this.response === false) {
                raiseError('Unknown server error');
                return;
            }
            if (this.response.success === false) {
                raiseError(this.response.message);
                return;
            }

            makeComments(this.response.data);

            if (start >= total) {
                document.getElementById('gcomments-loader').style.display = 'none';
            }
        }
    );
}

function makeComments(data) {
    let messages = [];
    for (let key in data) {
        if (data.hasOwnProperty(key)) {
            messages.unshift(data[key]);
        }
    }
    for (let key in messages) {
        if (messages.hasOwnProperty(key)) {
            appendComment(messages[key], false);
        }
    }
}

function sendRequest(url, data, callback) {
    let xhr = new XMLHttpRequest(), queryContent = '', i = 0, result = false;

    for (let key in data) {
        if (data.hasOwnProperty(key)) {
            queryContent += key + '=' + data[key];
            if (queryContent.length > 0 && i < (Object.objectLength(data) - 1)) {
                queryContent += '&';
            }
            i++;
        }
    }

    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.responseType = 'json';
    xhr.timeout = 3000;
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (typeof callback === 'function') {
                callback.apply(xhr);
            }
        }
    };
    xhr.send(queryContent);

}

function processResponse(response) {

}

function throwError(error) {

}

function appendComment(data, prepend) {
    let gCommentsBlock = document.querySelector('[data-item-id="' + itemId + '"]');

    if (prepend) {
        gCommentsBlock.prepend(getBlock(data))
    } else {
        gCommentsBlock.appendChild(getBlock(data));
    }
}

function getBlock(data) {
    let gComment         = document.createElement('div'),
        gCommentHead     = document.createElement('div'),
        gCommentUsername = document.createElement('span'),
        gCommentDate     = document.createElement('span'),
        gCommentBody     = document.createElement('div');
    gComment.dataset.commentBlock = data.id;
    gComment.className         = 'gcomment';
    gCommentHead.className     = 'gcomment-head';
    gCommentUsername.className = 'gcomment-username';
    gCommentDate.className     = 'gcomment-date';
    gCommentBody.className     = 'gcomment-body';
    gCommentUsername.innerText = data.user_name;
    gCommentDate.innerText     = data.creation_date;
    gCommentBody.innerHTML     = data.text;
    gCommentHead.appendChild(gCommentUsername);
    gCommentHead.appendChild(gCommentDate);
    gComment.appendChild(gCommentHead);
    gComment.appendChild(gCommentBody);

    if (isAdmin) {
        let gCommentAction = document.createElement('div'),
            gCommentActionDelete = document.createElement('button');
        gCommentAction.className = 'gcomment-action';
        gCommentActionDelete.className = 'gcomment-delete';
        gCommentActionDelete.dataset.comment = data.id;
        gCommentActionDelete.innerHTML = gComments.deleteButtonText;
        gCommentActionDelete.onclick = function() {
            deleteComment(data.id);
        };
        gCommentAction.appendChild(gCommentActionDelete);
        gComment.appendChild(gCommentAction);
    }

    return gComment;
}