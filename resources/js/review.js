function getLocations() {
    'use strict';
    changeIsAutoreplied();
    window.axios({
        url: '/review/async/findLocations',
        method: 'POST',
        data: {
            'account': document.querySelector("#reviewAccount").value
        }
    }).then(function(response) {
        var locations = response.data.result;
        $('#reviewLocation > option').remove();
        $('#reviewLocation').append($('<option>').html('選択してください').val(''));
        $.each(locations, function(key, value) {
          //  var $option = $('<option>').val(key).text(value);
            var $option = $('<option>').val(value).text(key);
            $('#reviewLocation').append($option);
        });
        getReviewAutoreplied();
    }).catch(function(error) {
        console.log(error);
    });
}

function changeIsAutoreplied() {
    'use strict';
    window.axios({
        url: '/review/async/findIsAutoreplied',
        method: 'POST',
        data: {
            'account': document.querySelector("#reviewAccount").value
        }
    }).then(function(response) {
        var isAutoreplied = response.data.result;
        if (isAutoreplied == null) {
            $('#is_autoreplied').val('');
            $('#change').text('自動返信を有効にする');
            if (!$('#change').hasClass('disabled')) {
                $('#change').addClass('disabled');
            }
        } else {
            $('#is_autoreplied').val(isAutoreplied);
            if (isAutoreplied == '0') {
                $('#change').text('自動返信を有効にする');
            } else {
                $('#change').text('自動返信を無効にする');
            }
            if ($('#change').hasClass('disabled')) {
                $('#change').removeClass('disabled');
            }
        }
    }).catch(function(error) {
        console.log(error);
    });
}

function exportPrepare() {
    'use strict';
    $('#exportAccount').val($('#reviewAccount').val());
    $('#exportLocation').val($('#reviewLocation').val());
    $('#exportStDate').val($('#reviewStDate').val());
    $('#exportEndDate').val($('#reviewEndDate').val());
    $('#exportRate').val($('#reviewRate').val());
    $('#exportReplyStatus').val($('#reviewReplyStatus').val());
    $('#exportSyncStatus').val($('#reviewSyncStatus').val());
}

function changePrepare() {
    'use strict';
    var message;
    var isAutoreplied;
    if ($('#reviewAccount').val() == '') {
        return false;
    }
    if ($('#is_autoreplied').val() == '0') {
        message = '自動返信を有効にしますか？'
        isAutoreplied = '1';
    } else {
        message = '自動返信を無効にしますか？'
        isAutoreplied = '0';
    }
    if (window.confirm(message)) {
        $('#changeAccount').val($('#reviewAccount').val());
        $('#changeIsAutoreplied').val(isAutoreplied);
        return true;
    } else {
        return false;
    }
}

function setReviewAutoreplied() {
    'use strict';
    var account = document.querySelector('#reviewAccount').value;
    var autoReplied = document.querySelector('#reviewAutoReplied').value;
    if (account == '') return;

    window.axios({
        url: '/review/async/updateAutoReplied',
        method: 'POST',
        data: {
            'account': account,
            'autoReplied': autoReplied
        }
    }).then(function(response) {
        // 処理結果をメッセージ表示する
        if (response.data.result) {
            alert('自動返信の設定を更新しました');
        } else {
            alert('自動返信の設定に失敗しました');
        }

    }).catch(function(error) {
        console.log(error);
    });
}

function getReviewAutoreplied() {
    'use strict';
    var account = document.querySelector('#reviewAccount').value;
    if (account == '') {
        $("#reviewAutoReplied").val(0);
        return;
    }

    window.axios({
        url: '/review/async/getAutoReplied',
        method: 'POST',
        data: {
            'account': account
        }
    }).then(function(response) {
        var result = response.data.result;
        $("#reviewAutoReplied").val(result);

    }).catch(function(error) {
        console.log(error);
    });
}

$(document).ready(function() {
    const period = 0 - $('#period').val();
    var date = new Date();
    const minDate = (period == 0) ? '' : date.setMonth(date.getMonth() + period);
    const maxDate = (period == 0) ? '' : 'today';

    const flatpickrConfig = {
        locale : 'ja',
        dateFormat: 'Y-m-d',
        allowInput: false,
        defaultDate: true,
        minDate: minDate,
        maxDate: maxDate
    };

    flatpickr('#reviewStDate', flatpickrConfig);
    flatpickr('#reviewEndDate', flatpickrConfig);
});