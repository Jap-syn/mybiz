const speed = 'fast';
let brand_locations = '';

function postConfirm(){
    if(!$('#upload-files').val()){
        if(confirm('投稿してよろしいですか？')){
            return true;
        }
        return false;
    }
    return true;
}

function exportPrepare() {
    'use strict';
    $('#exportAccount').val($('#account').val());
    $('#exportStDate').val($('#localpostStDate').val());
    $('#exportEndDate').val($('#localpostEndDate').val());
    $('#exportGmbTopicType').val($('#localpost_gmb_topic_type').val());
    $('#exportGmbActionType').val($('#localpost_gmb_action_type').val());
    $('#exportSyncStatus').val($('#localpost_sync_status').val());
}

function setTopicType(topicType) {
    $("input:hidden[name='gmb_topic_type']").val(topicType);
    switch (topicType) {
        case 'EVENT':
            $('#gmb_event_title_row').show(speed);
            $('#has_event_time_row').show(speed);
            $('#start_end_wrapper').show(speed);
            $('#gmb_action_type_wrapper').show(speed);
            $('#gmb_offer_detail_wrapper').hide(speed);
            break;
        case 'OFFER':
            $('#gmb_event_title_row').show(speed);
            $('#has_event_time_row').show(speed);
            $('#start_end_wrapper').show(speed);
            $('#gmb_action_type_wrapper').hide(speed);
            $('#gmb_offer_detail_wrapper').show(speed);
            break;
        default:
            $('#gmb_event_title_row').hide(speed);
            $('#has_event_time_row').hide(speed);
            $('#start_end_wrapper').hide(speed);
            $('#gmb_action_type_wrapper').show(speed);
            $('#gmb_offer_detail_wrapper').hide(speed);
        //
    }
}

function setActionType(actionType) {
    if (!actionType
        || actionType == 'ACTION_TYPE_UNSPECIFIED'
        || actionType == 'CALL') {
        $("#gmb_action_type_url_row").hide(speed);
    } else {
        $("#gmb_action_type_url_row").show(speed);
    }
}

// ブランド選択時に店舗リストを更新する
function selectLocationsByAccount(selected_locations) {
    // 都道府県とキーワード検索のためにブランドに属するlocation_idを保持
    brand_locations = selected_locations;

    // 都道府県とキーワードを初期化する
    $("#pref_name").val('');

    // 現在選択されているブランドに属する、全 location_id を取得する
    const arr_account_locations = ($('#account_id option:selected').data('locations') + '').split(',');
    const arr_selected_locations = selected_locations ? (selected_locations + '').split(',') : [];

    // 店舗チェックボックスの全リストを取得してループする
    $("input:checkbox[name='location_id[]']").each(function () {

        // 店舗が引数で指定された場合は、その店舗をチェック状態にする
        // （指定されなかった場合は、バックエンド側でセットされた checked 状態がそのまま有効になる）
        if (selected_locations) {
            if (arr_selected_locations.includes($(this).val())) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        }

        // 選択中のブランドに属している店舗は表示、それ以外は非表示にする
        if (arr_account_locations.includes($(this).val())) {
            // 店舗の修正不可の場合は選択されている店舗のみを表示する
            if (!isCreate && $(this).prop('checked') == false) {
                $(this).parent().hide();
            } else {
                $(this).parent().show();
            }
        } else {
            // ただし万が一checkedになっている場合は隠してしまうとまずいので表示させたままにする
            if ($(this).prop('checked') == false) {
                $(this).parent().hide();
            }
        }
    });
}

// 都道府県選択時に店舗リストを更新する
function findByAccount(pref_name, keyword) {
    console.log(keyword);

    const arr_selected_locations = brand_locations ? (brand_locations + '').split(',') : [];

    // 店舗チェックボックスの全リストを取得してループする
    $("input:checkbox[name='location_id[]']").each(function () {
        $(this).parent().hide();

        // ブランド対象店舗かつ都道府県が合致する店舗を表示する
        if (brand_locations) {
            if (arr_selected_locations.includes($(this).val())) {
                if (($(this).data('pref') == pref_name || pref_name == '') && ($(this).data('name').indexOf(keyword) > -1 || keyword == '')) {
                    $(this).parent().show();
                }
            }
        }
    });
}

// 選択店舗のプレビュー
function updateLocationPreview(){
    const locationPreview = document.getElementById('location_preview');

    // リストを初期化する
    locationPreview.innerHTML = '';

    // 店舗チェックボックスの全リストを取得してループする
    $("input:checkbox[name='location_id[]']").each(function () {
        if ($(this).prop('checked') == true) {
            let locationNameTab = document.createElement('div');
            locationNameTab.innerHTML = $(this).data('name');
            locationPreview.appendChild(locationNameTab);
        }
    });
}

// イベント開始日/終了日の日時コンポーネントをセットする
function setEventFlatpickr() {

    let flatpickrConfig = {};

    const isClickable = !isReadOnly;

    if ($("#gmb_has_event_time").prop("checked")) {
        flatpickrConfig = {
            locale: "ja",
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            altFormat: true,
            altFormat: 'Y-m-d H:i',
            allowInput: true,
            time_24hr: true,
            defaultHour: 0,
            defaultMinute: 0,
            clickOpens: isClickable,
        };

    } else {
        flatpickrConfig = {
            locale: "ja",
            enableTime: false,
            dateFormat: 'Y-m-d',
            allowInput: true,
            clickOpens: isClickable,
        };
    }

    flatpickr('#gmb_event_start_time', flatpickrConfig);
    flatpickr('#gmb_event_end_time', flatpickrConfig);
}

$(document).ready(function () {
    // Action Type 変更時のハンドラをセットする
    $("#gmb_action_type").change(function () {
        setActionType($(this).val());
    });

    // ブランド指定時のハンドラをセットする
    $("#account_id").change(function () {
        selectLocationsByAccount($('option:selected', this).data('locations'));
        updateLocationPreview();
    })

    // 都道府県指定時のハンドラをセットする
    $("#pref_name").change(function () {
        findByAccount($(this).val(), $("#account_keyword").val());
    })

    // ブランド指定時のハンドラをセットする
    $("#account_keyword").change(function () {
        findByAccount($("#pref_name").val(), $(this).val());
    })

    // 店舗の全選択
    $("#btn-select-all").click(function () {
        $("input:checkbox[name='location_id[]']").each(function () {
            if(!$(this).parent().is(":hidden")){
                $(this).prop('checked', true);
            }
        });
    });

    // 店舗の全解除
    $("#btn-de-select-all").click(function () {
        $("input:checkbox[name='location_id[]']").each(function () {
            if(!$(this).parent().is(":hidden")){
                $(this).prop('checked', false);
            }
        });
    });

    // プレビューの更新
    $("#location_checkboxes").change(function () {
        updateLocationPreview();
    })

    // 表示項目の初期化処理（切り替え用 function を強制的にコールする）
    setActionType($("#gmb_action_type").val());
    setTopicType($("#gmb_topic_type").val());
    // 新規登録時、店舗表示の初期化（新規登録時でもブランドは選択されており、全選択状態にする）
    if (isCreate) {
        selectLocationsByAccount(
            $("#account_id option:selected").data('locations')
        );
    } else {
        selectLocationsByAccount();
    }

    // カレンダー部品の設定
    setEventFlatpickr();

    // 投稿日時に datepickr をセットする
    if (!isReadOnly) {
        const flatpickrConfigDateOnly = {
            locale: 'ja',
            enableTime: false,
            dateFormat: 'Y-m-d',
            allowInput: true,
            minDate: 'today',
        };

        flatpickr('#scheduled_sync_time', flatpickrConfigDateOnly);
    }

    // 「予約する」チェックボックスの制御
    $('#is_scheduled').change(function () {
        $('#scheduled_sync_time').prop('disabled', !$(this).prop('checked'));
        $('#scheduled_range').prop('disabled', !$(this).prop('checked'));
        if (isReadOnly) {
            // 強制的に変更不可にする
            $('#scheduled_range').prop('disabled', true);
        }
    });
    $('#is_scheduled').trigger('change');

    updateLocationPreview();
});

// upload image

$('#upload-files').on('change', function (e) {
    upload_file = e.target.files[0];

    // 指定の拡張子以外の場合はアラート
    var permit_type = ['image/jpeg', 'image/png'];
    var has_error = false;
    var error_msg = '';
    if (upload_file && permit_type.indexOf(upload_file.type) == -1) {
        has_error = true;
        error_msg = 'jpeg/png以外のファイルはアップロードできません';
    }
    if ((80 * 1024 * 1024) < upload_file.size) {
        // 80MBまでOK
        has_error = true;
        error_msg = 'ファイルサイズが80MBを超える画像はアップロードできません';
    }
    if (has_error) {
        alert(error_msg);
        $(this).val('');
        $("#upload-preview").attr('src', '');
        return false;
    }

    var reader = new FileReader();
    reader.onload = function (e) {
        $("#upload-preview").attr('src', e.target.result);
    }
    reader.readAsDataURL(upload_file);
    $('.saved-image').hide();
});

// dropzone
if ($("#gmb-manager-dropzone").length) {
    Dropzone.autoDiscover = false;
    const dz = new Dropzone("#gmb-manager-dropzone", {
        uploadMultiple: true,
        maxFiles: 1,
        acceptedFiles: ".jpeg,.jpg,.png",
        addRemoveLinks: true,
        timeout: 300000, // 300秒 = 5分
        dictFileTooBig: "ファイルサイズが大きすぎます。(@{{filesize}}MB). 最大サイズ: @{{maxFilesize}}MB.",
        // dictInvalidFileType: "画像もしくは動画ファイルのみアップロード可能です",
        dictInvalidFileType: "画像ファイル（ jpeg もしくは png ）のみアップロード可能です",
        dictMaxFilesExceeded: "ファイルは1ファイルのみ追加可能です",
        dictRemoveFile: "削除する",
        dictCancelUpload: "中止する",
        dictCancelUploadConfirmation: "ファイルアップロードをキャンセルしますか？",
        autoProcessQueue: false,
        thumbnailWidth: 200,
        thumbnailHeight: 200,

        url: '/localpost/upload',
        headers: {
            'X-CSRF-TOKEN': $("input:hidden[name='_token']").val()
        },
        success: function (file, response) {
            $("input:hidden[name='gmb_source_url']").val(response)
        },
    });
    dz.on('maxfilesexceeded', function (file) {
        this.removeFile(file);
    });
    dz.on("addedfile", function (file) {
        // file.accepted = true;   // true にするとプレビューが表示されなくなる
        this.emit('complete', file);
    });

    // 読み取り専用状態の場合
    if (isReadOnly) {
        dz.disable();
        $('#gmb-manager-dropzone').hide();
    }

    var fileArea = document.getElementById('upload-files-wrapper');
    var fileInput = document.getElementById('upload-files');

    fileArea.addEventListener('drop', function (evt) {
        evt.preventDefault();
        fileArea.classList.remove('dragenter');
        var files = evt.dataTransfer.files;
        fileInput.files = files;
    });
}
