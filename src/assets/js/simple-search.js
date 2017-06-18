"use strict";
$(function(){

	var searchTimeout, searchXhr;

	$('input[data-simlesearch]').on('input', function(event) {

		var element = $(this),
			container = element.closest('.live-search-results'),
			liveHref = element.data('simpleLiveHref'),
			liveTarget = element.data('simpleLiveTarget'),
            minCount = element.data('simpleMinCount') || 3,
			time = element.data('simpleTime') || 500;

		if ( liveHref ){
			searchTimeout && clearInterval(searchTimeout);
			searchXhr && searchXhr.abort();

			if ( element.val().replace(' ', '').length >= minCount ) {

				searchTimeout = setTimeout( function(){

					searchXhr = $.ajax({
						type: "POST",
						url: liveHref,
						cache: false,
						data: { q : element.val() },
						dataType: 'json',
						success: function(response) {

							if (response.status == 'success') {
								container
									.find(liveTarget)
									.html( $(response.html) )
									.show();
							} else {
								container
                                    .find(liveTarget)
									.hide();
							}
						}
					});

				}, time)

			}
		}
	});

});