$ = jQuery;

function affiliate_tracking_pageLoaded() {
	var affiliate = getUrlParameter("affiliate");
	var action = "register";
	var user = "anonymous";

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

$(document).ready(function() {
	affiliate_tracking_pageLoaded();
});
