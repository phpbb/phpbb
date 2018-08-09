var modal = document.getElementById('pm-smile-model');
var btn = document.getElementById("pm-smile-button");
var span = document.getElementsByClassName("pm-button-close")[0];

var to_modal = document.getElementById('pm-to-model');
var to_btn = document.getElementById("pm-to-button");
var to_span = document.getElementsByClassName("pm-to-button-close")[0];

var bb_modal = document.getElementById('pm-bbcode-model');
var bb_btn = document.getElementById("pm-bbcode-button");
var bb_span = document.getElementsByClassName("pm-to-button-close")[0];

/**
 * When the user clicks the button, open the modal
 */
btn.onclick = function() {
	modal.style.display = "block";
}

/**
 * When the user clicks on <span> (x), close the modal
 */
span.onclick = function() {
	modal.style.display = "none";
}

/**
 * When the user clicks anywhere outside of the modal, close it
 */
window.onclick = function(event) {
	if (event.target == modal) {
		modal.style.display = "none";
	}
}

/**
 * When the user clicks the button, open the modal
 */
to_btn.onclick = function() {
	to_modal.style.display = "block";
}

/**
 * When the user clicks on <span> (x), close the modal
 */
to_span.onclick = function() {
	to_modal.style.display = "none";
}

/**
 * When the user clicks anywhere outside of the modal, close it
 */
window.onclick = function(event) {
	if (event.target == to_modal) {
		to_modal.style.display = "none";
	}
}

/**
 * When the user clicks the button, open the modal
 */
bb_btn.onclick = function() {
	bb_modal.style.display = "block";
}

/**
 * When the user clicks on <span> (x), close the modal
 */
bb_span.onclick = function() {
	bb_modal.style.display = "none";
}

/**
 * When the user clicks anywhere outside of the modal, close it
 */
window.onclick = function(event) {
	if (event.target == bb_modal) {
		bb_modal.style.display = "none";
	}
}
