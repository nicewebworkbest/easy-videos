(function($){
	$(document).on('submit', '#settings-form', function(){
		if ($('#google-api-key').val() == '') {
			$('.result').html('Please add Google API Key!');
			return false;
		}
	});
})(jQuery);