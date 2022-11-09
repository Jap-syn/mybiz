// CSV Export
function exportPrepare() {
   'use strict';
   $('#exportStDate').val($('#localpostStDate').val());
   $('#exportEndDate').val($('#localpostEndDate').val());
   $('#exportGmbTopicType').val($('#localpost_gmb_topic_type').val());
   $('#exportGmbActionType').val($('#localpost_gmb_action_type').val());
}

$(document).ready(function () {
   // 日時コンポーネントを設定する
   // 開始日／終了日に datepickr をセットする
   const flatpickrConfigDateOnly = {
      enableTime: false,
      dateFormat: 'Y-m-d',
      allowInput: true,
      defaultDate: true,
   };

   flatpickr('#localpostStDate', flatpickrConfigDateOnly);
   flatpickr('#localpostEndDate', flatpickrConfigDateOnly);

   // 編集ボタンクリック時のイベント設定
   $('.btn-open-edit').click(function () {
      openEdit($(this).data('locationid'));
   });
});

function openEdit(id) {
   location.href = edit_url.replace('||location_id||', id);
}
