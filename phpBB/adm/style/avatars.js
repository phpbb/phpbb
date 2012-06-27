function avatar_simplify() {
    $('#av_options').hide();

    var selected = $('#avatar_driver').val();
    $('#av_option_' + selected).show();
}

avatar_simplify();
$('#avatar_driver').on('change', avatar_simplify);
