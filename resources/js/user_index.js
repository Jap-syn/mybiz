function destroyUser(userId) {
   'use strict';
   if (window.confirm('削除しますか？（一度削除したユーザーは元に戻すことは出来ません）')) {
      $("input[name='destroy_user_id']").val(userId);
      $("form[name='destroy_user_form']").attr('action', 'user/' + userId);
      $("form[name='destroy_user_form']").submit();
   }
}
