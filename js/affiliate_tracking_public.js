$ = jQuery;

function affiliate_tracking_pageLoaded(user, action) {
	var affiliate = getUrlParameter("affiliate");
//	var action = "hit";
//	var user = "anonymous";

	if (affiliate != undefined) {
		$.getJSON( "/process_hit/"+affiliate+"/"+action+"/"+user, function(data) {
			console.log("success: " + data);
		}).fail(function(data) {
			console.log("error: " + data);
		});
	}
}


function getUrlParameter(sParam) {
	var sPageURL = decodeURIComponent(window.location.search.substring(1)),
		sURLVariables = sPageURL.split('&'),
		sParameterName,
		i;

	for (i = 0; i < sURLVariables.length; i++) {
		sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] === sParam) {
			return sParameterName[1] === undefined ? true : sParameterName[1];
		}
	}
};

function affiliate_tracking_addToForm() {
	var affiliate = getUrlParameter("affiliate");
	var affiliate_value = "none";
	if (affiliate != undefined) {
		affiliate_value = affiliate;
	}

	$("#registration-form form").append('<textarea id="00N1a0000092oyh" style="display:none" name="00N1a0000092oyh" type="text" wrap="soft">'+affiliate_value+'</textarea>')

	$(".submit-area input:submit").removeAttr("name");
	var testing = false;

	$(".submit-area input").click(function() {
	 	var email = $("#registration-form form input#email").val();

		$.ajax({
			url:'/affiliate_tracking/add_code/new/'+email,
			type:'post',
			success: function (response) {
				console.log(response);

				affiliate_tracking_pageLoaded(email, "register");

				testing = true;

				$("#registration-form form").append('<textarea style="display:none" id="00N1a0000092oym" name="00N1a0000092oym" type="text" wrap="soft">'+response+'</textarea>');

				var returnURL = $("#registration-form input[name='retURL']").attr("value");
				returnURL = returnURL + "?affiliate="+response;
				$("#registration-form input[name='retURL']").attr("value", returnURL);


			//	$('form').attr('action', 'https://example.com');
				$('form')[0].submit();

			},
			error: function () {
				console.log("error");
				return true;
		},
	});
	return testing;
	});
}

$(document).ready(function() {
	affiliate_tracking_pageLoaded("anonymous", "hit");
	affiliate_tracking_addToForm();
});
