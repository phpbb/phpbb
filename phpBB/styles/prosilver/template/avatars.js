function avatar_simplify() {
    var node = document.getElementById('av_options');
    for (var i = 0; i < node.children.length; i++) {
        child = node.children[i];
        child.style.display = 'none';
    }

    var selected = document.getElementById('avatar_driver').value;
    var id = 'av_option_' + selected;
    node = document.getElementById(id);
    if (node != null) {
        node.style.display = 'block';
    }
}

avatar_simplify();
document.getElementById('avatar_driver').onchange = avatar_simplify;
