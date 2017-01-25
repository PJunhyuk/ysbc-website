/*
 * js script for Startuply Import Demo data page
 */
(function(){
	if($ === undefined) {
		$ = jQuery;
	}

	var demoForm = $(".ventcamp-import");

	demoForm.find("input[name='import']").click(function(event) {
		if(confirm('Please leave the browser open and wait until the page loading finishes completely (can take up to several minutes)!')) {
			return true;
		} else {
			event.preventDefault();
			event.stopPropagation();
			return false;
		}
	});

	demoForm.find("input[name='reset']").click(function(event) {
		if(confirm('WARNING!\r\nThis will erase ALL your wordpress content (except plugins) and reset it to default clean state. \r\nDo not login in any popups until page finishes loading COMPLETELY\r\nAre you sure to continue?')) {
			return true;
		} else {
			event.preventDefault();
			event.stopPropagation();
			return false;
		}
	});
})();

