// CSV Export
function exportPrepare() {
   'use strict';
   $('#exportAccount').val($('#account').val());
   $('#exportStDate').val($('#localpostStDate').val());
   $('#exportEndDate').val($('#localpostEndDate').val());
   $('#exportGmbTopicType').val($('#localpost_gmb_topic_type').val());
   $('#exportGmbActionType').val($('#localpost_gmb_action_type').val());
   $('#exportSyncStatus').val($('#localpost_sync_status').val());
}

$(document).ready(function () {
   // 日時コンポーネントを設定する
   // 開始日／終了日に datepickr をセットする
   const flatpickrConfigDateOnly = {
      locale: 'ja',
      enableTime: false,
      dateFormat: 'Y-m-d',
      allowInput: false,
      defaultDate: true,
   };

   flatpickr('#localpostStDate', flatpickrConfigDateOnly);
   flatpickr('#localpostEndDate', flatpickrConfigDateOnly);

   $(".delete-localpost").on('click', function (event) {
      event.preventDefault();
      if (window.confirm('取り消しは出来ません。よろしいですか？')) {
         $(this).parent('form').submit();
      };
   });
});
