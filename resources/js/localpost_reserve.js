$(document).ready(function () {
   // 投稿日時に datepickr をセットする
   const flatpickrConfig = {
      enableTime: true,
      dateFormat: 'Y-m-d H:i',
      altFormat: true,
      altFormat: 'Y-m-d H:i',
   };
   flatpickr('#scheduled_sync_time', flatpickrConfig);
});
