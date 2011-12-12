<!-- jquery -->
<script type="text/javascript" src="/lib/jquery/v1.5/jquery-1.5.min.js"></script>

<!-- JQuery UI -->
<script type="text/javascript" src="/lib/jquery-plugin/ui/v1.8.13/js/jquery-ui-1.8.13.custom.min.js"></script>

<!-- ckeditor and jquery adapter -->
<script type="text/javascript" src="/lib/ckeditor/v3.5.2/ckeditor.js"></script>
<script type="text/javascript" src="/lib/ckeditor/v3.5.2/adapters/jquery.js"></script>

<!-- tinymce and jquery adapter -->
<script type="text/javascript" src="/lib/tinymce/v3.3.9.3/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
/*tinyMCE.init({
    theme : "advanced",
    mode : "textareas"
});*/
</script>

<!-- Apply field widget JS -->
<script type="text/javascript">

$(function() {

	$('.ui-widget-date').datepicker({
		dateFormat: 'yy-mm-dd'
	});

	// multi value grid
	(function() {
	
		$('.ui-widget-multi').sortable();
		$('.ui-widget-multi input[name*="[action][delete]"]').live('click',function(evt) {
			evt.preventDefault();
			evt.stopPropagation();
	
			// the way values get deleted is by setting them to an empty string
			$(evt.target).parent().find('input:not([type="submit"]):not([type="hidden"]):not(option),select,textarea').each(function() {
				
				/*if(this.nodeName == 'SELECT') {
					var option = document.createElement('option');
					option.value = '';
					$(this).append(option);
				}*/

				if(this.type == 'file') {
					var replace = document.createElement('input');
					replace.type = 'hidden';
					replace.name = this.name + '[value]';
					replace.value = '';
					$(this).replaceWith(replace);
				}
	
				$(this).val('');
			});
	
			$(evt.target).parent().hide();
			
		});

		$('input[name*="[action][add]"]').live('click',function(evt) {
			evt.preventDefault();
			evt.stopPropagation();

			var count = 0;
			var last = $(evt.target).parent().find('ol > *').each(function() { 
				count++;
			});

			var first = $(evt.target).parent().find('ol > *:first-child').get(0);			
			var clone = first.cloneNode(true);

			var input = $('input[name*="[action][delete]"]',clone).get(0);
			input.name = input.name.replace(/\[[0-9]*\]/,'[' + count + ']');
			input.id = input.id.replace(/-[0-9]*-/,'-' + (count + 1) + '-');

			var control = $('input:not([type="submit"]):not([type="hidden"]):not(option),select,textarea',clone).get(0);
			control.name = control.name.replace(/\[[0-9]*\]\[value\]/,'[' + count + '][value]');
			control.id = control.id.replace(/-[0-9]*$/, '-' + (count + 1) );
			$(control).val('');

			$('input[type="hidden"]',clone).remove();

			$(this).parent().find('ol').append(clone);
			$(clone).show();
			
		});

	})();
	
});

</script>