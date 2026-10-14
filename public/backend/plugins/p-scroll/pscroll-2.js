(function ($) {
	"use strict";

	if (document.querySelector('.content')) {
		const ps5 = new PerfectScrollbar('.content', {
			useBothWheelAxes: true,
			suppressScrollX: true,
		});
	}

	if (document.querySelector('.content-1')) {
		const ps6 = new PerfectScrollbar('.content-1', {
			useBothWheelAxes: true,
			suppressScrollX: true,
		});
	}

})(jQuery);