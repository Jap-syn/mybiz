function getTemplate() {
    'use strict';
    window.axios({
        url: '/template/async/find',
        method: 'POST',
        data: {
            'review_reply_template_id':document.querySelector("#template").value
        }
    }).then(function(response) {
        document.querySelector("#gmb_comment").value = response.data.result.template;
        document.querySelector(".emoji-wysiwyg-editor").innerHTML = response.data.result.template;
    }).catch(function(error) {
    });
}

function postConfirm() {
    'use strict';
    if (window.confirm('返信してよろしいですか？')) {
        return true;
    }
    return false;
}

function deleteConfirm() {
    'use strict';
    if (window.confirm('削除してよろしいですか？')) {
        $('#is_deleted').val('1');
        return true;
    }
    return false;
}

$(document).ready(function() {
    //返信日時に datepickr をセットする
    const flatpickrConfigDateOnly = {
        locale    : 'ja',
        allowInput: true,
        minDate   : 'today'
    };

    flatpickr('#scheduled_sync_time', flatpickrConfigDateOnly);
});