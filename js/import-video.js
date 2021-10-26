(function($){
	$(document).on('submit', '#import-form', function(){
		$.ajax({
			url: js_vars.ajax_url,
			type: "POST",
			data: $(this).serialize(),
			success: function(response){
				if (response.success == true){
					if (response.data.success == false){
						$('.result').append('<div>'+response.data.message+'</div>');
					} else {
						$('.result').append('<div>'+response.data.message+'</div>');
						if (response.data.next_page_token){
							$('#page-token').val(response.data.next_page_token);
							$('#import-form').submit();
						}
					}
				} else {
					$('.result').append('<div>'+response.data.message+'</div>');
				}
			}
		});
		return false;
	});
})(jQuery);