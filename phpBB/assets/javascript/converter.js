/**
 * Created by bala on 19/6/17.
 */
(function($){

	function startConversion($btn) {
		$btn.hide();


	}


	$(document).ready(function(){

		$("#btn").click(function(e){
			e.preventDefault();
			var xhreq = new XMLHttpRequest();
			xhreq.open('GET','/install/app.php/converter/start/ajax',true);
			xhreq.setRequestHeader('X-Requested-With','XMLHttpRequest');
			xhreq.onprogress = function(e){
				alert(e.currentTarget.responseText);
			}

			xhreq.send();



		});

	});

})(jQuery);