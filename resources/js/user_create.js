const speed = 'fast';

// ブランド選択時に店舗リストを更新する
function selectAccountsByEnterprise(selected_accounts) {

    // 現在選択されている所属企業に属する、全 account_id を取得する
    const arr_enterprise_accounts = ($('#enterprise_id option:selected').data('accounts') + '').split(',');
    const arr_selected_accounts = selected_accounts ? (selected_accounts + '').split(',') : [];

    // ブランドチェックボックスの全リストを取得してループする
    $("input:checkbox[name='account_ids[]']").each(function () {

        // ブランドが引数で指定された場合は、そのブランドをチェック状態にする
        // （指定されなかった場合は、バックエンド側でセットされた checked 状態がそのまま有効になる）
        if (selected_accounts) {
            if (arr_selected_accounts.includes($(this).val())) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        }

        // 選択中の企業に属しているブランドは表示、それ以外は非表示にする
        if (arr_enterprise_accounts.includes($(this).val())) {
            // 店舗の修正不可の場合は選択されている店舗のみを表示する
            if (isReadOnly && $(this).prop('checked') == false) {
                $(this).parent().hide();
            } else {
                $(this).parent().show();
            }
        } else {
            // checkedになっている場合はチェックをはずしてから非表示にする
            $(this).prop('checked', false);
            $(this).parent().hide();
        }
    });
}

$(document).ready(function () {
    // ブランド指定時のハンドラをセットする
    $("#enterprise_id").change(function () {
        selectAccountsByEnterprise($('option:selected', this).data('accounts'));
    })

    // 店舗の全選択
    $("#btn-select-all").click(function () {
        $("input:checkbox[name='account_ids[]']").each(function () {
            if(!$(this).parent().is(":hidden")){
                $(this).prop('checked', true);
            }
        });
    });

    // 店舗の全解除
    $("#btn-de-select-all").click(function () {
        $("input:checkbox[name='account_ids[]']").each(function () {
            if(!$(this).parent().is(":hidden")){
                $(this).prop('checked', false);
            }
        });
    });

    // 新規登録時、店舗表示の初期化（新規登録時でもブランドは選択されており、全選択状態にする）
    if (isCreate) {
        selectAccountsByEnterprise(
            $("#enterprise_id option:selected").data('accounts')
        );
    } else {
        selectAccountsByEnterprise();
    }

    $('#allow_local_post').change(function () {
        $('#edit_local_post').prop('disabled', !$(this).prop('checked'));
    });
    $('#allow_local_post').trigger('change');

    $('#allow_review').change(function () {
        $('#edit_review').prop('disabled', !$(this).prop('checked'));
    });
    $('#allow_review').trigger('change');
});
