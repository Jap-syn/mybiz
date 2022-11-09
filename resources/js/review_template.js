function deleteTemplate(templateId) {
   'use strict';
   if (window.confirm('削除しますか？')) {
      $("input[name='review_reply_template_id']").val(templateId);
      $("form[name='delete_template']").submit();
   }
}

function deleteTemplateViaAxios(templateId) {
   'use strict';
   if (window.confirm('削除しますか？')) {
      window.axios({
         url: '/template/delete',
         method: 'POST',
         data: {
            'review_reply_template_id': templateId
         }
      }).then(function (response) {
         location.reload();
      }).catch(function (error) {
      });
   }
}