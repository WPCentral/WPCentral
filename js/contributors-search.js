jQuery( document ).ready(function($){
	var request;

	$("#searchform-contributor").keyup(function(e){
		var q = $(this).val();

		if ( request ) {
			request.abort();
		}

		if ( ! q ) {
			$("#contributors").empty();
			return;
		}

		request = $.getJSON("http://wpcentral.io/api/contributors/",
		{
			search: q,
			format: "json"
		},
		function(data) {
			$("#contributors").empty();
			$("#contributors").append("<p>Results for <b>" + q + "</b></p>");
			$.each(data, function(i,item){
				$("#contributors").append("<div><a href='http://wpcentral.io/contributors/" + encodeURIComponent(item.username) + "'>" + item.name + "</a><br>" + item.company + "<br><br></div>");
			});
		});
	});
});