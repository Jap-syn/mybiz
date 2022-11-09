function bulkDeleteConfirm() {
    'use strict';
    var vals = $('input[name=media_item2_group_id]:checked').map(function() {
        return $(this).val();
    }).get();
    if (vals.length == 0) {
        window.alert('削除する写真を選択してください')
        return false;
    }
    if (window.confirm('削除してよろしいですか？')) {
        $('#deleteAccount').val($('#account').val());
        $('#deleteItemIds').val(vals);
        return true;
    }
    return false;
}

function deleteConfirm() {
    'use strict';
    if (window.confirm('削除してよろしいですか？')) {
        return true;
    }
    return false;
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

    flatpickr('#startDate', flatpickrConfig);
    flatpickr('#endDate', flatpickrConfig);

    $('.image_box .image_checkbox').click(function() {
        return false;
    });
    $('img.thumbnail').on('click', function() {
        if (!$(this).is('.checked')) {
            $(this).addClass('checked');
            $(this).next('input[name=media_item2_group_id]').prop('checked', true);
        } else {
            $(this).removeClass('checked');
            $(this).next('input[name=media_item2_group_id]').prop('checked', false);
        }
    });

    $('#btn-select-all').click(function () {
        if ($('img.thumbnail').length) {
            $('img.thumbnail').addClass('checked');
            $('.image_box .image_checkbox').prop('checked', true);
        }
    });

    $('#btn-de-select-all').click(function () {
        if ($('img.thumbnail').length) {
            $('img.thumbnail').removeClass('checked');
            $('.image_box .image_checkbox').prop('checked', false);
        }
    });
});