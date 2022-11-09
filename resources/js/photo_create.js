const speed = 'fast';
let brand_locations = '';

function postConfirm() {
    if (!$('#upload-files').val()) {
        if (confirm('投稿してよろしいですか？')) {
            return true;
        }
        return false;
    }
    return true;
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

$(document).ready(function () {
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
            if (!$(this).parent().is(":hidden")) {
                $(this).prop('checked', true);
            }
        });
    });

    // 店舗の全解除
    $("#btn-de-select-all").click(function () {
        $("input:checkbox[name='location_id[]']").each(function () {
            if (!$(this).parent().is(":hidden")) {
                $(this).prop('checked', false);
            }
        });
    });

    // プレビューの更新
    $("#location_checkboxes").change(function () {
        updateLocationPreview();
    })

    // 新規登録時、店舗表示の初期化（新規登録時でもブランドは選択されており、全選択状態にする）
    if (isCreate) {
        selectLocationsByAccount(
            $("#account_id option:selected").data('locations')
        );
    } else {
        selectLocationsByAccount();
    }

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

let dropZone = document.getElementById('drop-zone');
let preview = document.getElementById('preview');
var fileInput = document.getElementById('file-input');
let deleteMediaItem2 = document.getElementById('delete_media_item2');

dropZone.addEventListener('dragover', function (e) {
    e.stopPropagation();
    e.preventDefault();
}, false);

dropZone.addEventListener('dragleave', function (e) {
    e.stopPropagation();
    e.preventDefault();
}, false);

fileInput.addEventListener('change', function (e) {

    if ((fileInput.files.length + document.getElementById( "register_media_items" ).childElementCount) > maxFileLength) {
        alert('アップロードできるファイルは10ファイルまでです。');
        return false;
    }

    // 投稿可能なファイルかチェックする
    if(!isExtensionsAllowed(fileInput.files) || !isDoesExceedFileSize(fileInput.files)){
        return false;
    }
    previewFile();
});

dropZone.addEventListener('drop', function (e) {
    e.stopPropagation();
    e.preventDefault();
    let dropFiles = e.dataTransfer.files; //ドロップしたファイルを取得
    const dataTransfer = new DataTransfer();

    // 投稿可能なファイルかチェックする
    if(!isExtensionsAllowed(dropFiles) || !isDoesExceedFileSize(dropFiles)){
        return false;
    }

    // 既に追加されているファイルを先に追加する
    for (let i = 0; i < fileInput.files.length; i++) {
        dataTransfer.items.add(fileInput.files[i]);
    }

    for (let i = 0; i < dropFiles.length; i++) {
        if ((dataTransfer.items.length + document.getElementById( "register_media_items" ).childElementCount) < maxFileLength) {
            dataTransfer.items.add(dropFiles[i]);
        } else {
            alert('アップロードできるファイルは10ファイルまでです。');
            break;
        }
    }
    fileInput.files = dataTransfer.files;

    previewFile();
}, false);

function isExtensionsAllowed(files) {
    let permit_type = ['image/jpeg', 'image/png', 'video/mp4', 'video/quicktime', 'video/x-ms-wmv'];

    for (let i = 0; i < files.length; i++) {
        if (permit_type.indexOf(files[i].type) == -1) {
            alert('jpeg/png/mp4/mov/wmv以外のファイルはアップロードできません');
            return false
        }
    }
    return true
}

function isDoesExceedFileSize(files) {
    for (let i = 0; i < files.length; i++) {
        if ((80 * 1024 * 1024) < files[i].size) {
            // 80MBまでOK
            alert('ファイルサイズが80MBを超える画像/動画はアップロードできません');
            return false;
        }
    }
    return true
}

function previewFile() {
    /* FileReaderで読み込み、プレビュー画像を表示。 */
    preview.innerHTML = '';
    const files = fileInput.files;

    let index = 0
    for (let i = 0; i < files.length; i++) {
        let fileListTemp = document.createElement('dev');
        fileListTemp.classList.add('d-flex', 'justify-content-between', 'mb-1')

        let fileAreaDev = document.createElement('dev');
        fileAreaDev.classList.add('col-2', 'mb-1', 'd-flex');

        // TODO:サムネの表示処理は必要な場合、動画のサムネをどうするか検討する
        // let fileImage = document.createElement('img');
        // fileImage.classList.add('thumbnail')
        //
        // var fileReader = new FileReader();
        // fileReader.onload = (function () {
        //     fileImage.src = fileReader.result;
        // });
        // fileReader.readAsDataURL(files[i]);


        let fileNameTag = document.createElement('p');
        fileNameTag.classList.add('mr-1', 'text-nowrap')
        fileNameTag.innerText = files[i].name;

        // TODO:サムネの表示処理は必要な場合、動画のサムネをどうするか検討する
        // fileAreaDev.appendChild(fileImage)
        fileAreaDev.appendChild(fileNameTag)

        let deleteButton = this.createDeleteBtn(index);

        fileListTemp.appendChild(fileAreaDev)
        fileListTemp.appendChild(deleteButton)
        preview.appendChild(fileListTemp)

        index++
    }
}

function deleteFile(event) {

    const dataTransfer = new DataTransfer();
    let index = 0

    for (let i = 0; i < fileInput.files.length; i++) {
        if (this.deleteIndex != index) {
            dataTransfer.items.add(fileInput.files[i])
        }
        index++
    }

    fileInput.files = dataTransfer.files;

    previewFile();
}

function deleteRegisteredFile(index, mediaItem2Ids) {

    // 削除対象をフォームに追加
    let inputTemp = document.createElement('input');
    inputTemp.setAttribute("type", "hidden");
    inputTemp.setAttribute("name", "delete_media_item2_ids[]");
    inputTemp.setAttribute("value", mediaItem2Ids);
    deleteMediaItem2.appendChild(inputTemp)

    // 要素の削除
    const selectId = "#media_item2_" + index;
    $(selectId).remove();

    if (!document.getElementById( "register_media_items" ).hasChildNodes()) {
        let element = document.getElementById('all_delete_media_item2');
        element.value = true;
    }
}

function createDeleteBtn(deleteIndex) {
    let deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.classList.add('btn', 'btn-primary');
    deleteButton.innerText = "削除";
    deleteButton.addEventListener('click', {deleteIndex: deleteIndex, handleEvent: deleteFile});

    return deleteButton;
}
