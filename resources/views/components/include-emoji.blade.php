<link href="{{ asset('css/emoji.css') }}" rel="stylesheet" type="text/css">

<script type="text/javascript" src="{{ mix('js/emoji/config.js') }}" defer></script>
<script type="text/javascript" src="{{ mix('js/emoji/util.js') }}" defer></script>
<script type="text/javascript" src="{{ mix('js/emoji/jquery.emojiarea.js') }}" defer></script>
<script type="text/javascript" src="{{ mix('js/emoji/emoji-picker.js') }}" defer></script>
<script>
window.onload = function () {
    $(function () {
        
       // Initializes and creates emoji set from sprite sheet
       window.emojiPicker = new EmojiPicker({
          emojiable_selector: '[data-emojiable=true]',
          assetsPath: '/img',
          popupButtonClasses: 'fa fa-smile-o'
       });
       // Finds all elements with `emojiable_selector` and converts them to rich emoji input fields
       // You may want to delay this step if you have dynamically created input fields that appear later in the loading process
       // It can be called as many times as necessary; previously converted input fields will not be converted again
       window.emojiPicker.discover();
    });
 }
</script>