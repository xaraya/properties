(function($) {
    function toggleLabel() {
        var input = $(this);
        setTimeout(function() {
            var def = input.attr('title');
            if (!input.val() || (input.val() == def)) {
                input.prev('span').css('visibility', '');
                if (def) {
                    var dummy = $('<label></label>').text(def).css('visibility','hidden').appendTo('body');
                    input.prev('span').css('margin-left', dummy.width() + 3 + 'px');
                    dummy.remove();
                }
            } else {
                input.prev('span').css('visibility', 'hidden');
            }
        }, 0);
    };

    function resetField() {
        var def = $(this).attr('title');
        if (!$(this).val() || ($(this).val() == def)) {
            $(this).val(def);
            $(this).prev('span').css('visibility', '');
        }
    };

    $(document).on('keydown','input', toggleLabel);
    $(document).on('paste', 'input', toggleLabel);
    $(document).on('change', 'input', toggleLabel);

    $(document).on('focusin', 'input', function() {
        //$(this).prev('span').css('color', '#ccc');
        $(this).prev('span').css('visibility', 'hidden');
    });
    $(document).on('focusout', 'input', function() {
        $(this).prev('span').css('color', '#999');
    });

    $(function() {
        $('input').each(function() { toggleLabel.call(this); });
    });

})(jQuery);

