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
			$(evt.target).closest('li').find('input:not([type="submit"]):not([type="hidden"]):not(option),select,textarea').each(function() {
				
				if(this.nodeName == 'SELECT') {
					var option = document.createElement('option');
					option.value = '';
					$(this).append(option);
				}

				if(this.type == 'file') {
                                        //var replace = document.createElement('input');
                                        //replace.type = 'hidden';
                                        //replace.name = this.name + '[value]';
                                        //replace.value = '';
					$('input[type="hidden"][name="' + this.name  + '[value]"]').val('');
				}
	
				$(this).val('');
			});
	
			$(evt.target).closest('li').hide();
			
		});
                
                // single media field delete handler
                $('.one input[type="submit"][name*="[delete]"]').live('click',function(evt) {
                    evt.preventDefault();
                    evt.stopPropagation();
                    
                    //var name = $(evt.target).closest('.input').find('input[type=file]').attr('name');
                    //$(evt.target).closest('.input').find('input[type="hidden"][name="' + name + '[value]"]').val('');
                    
                    // clear all other fields
                    $(evt.target).closest('.input').find('input,textarea,select').each(function() {
                        if(this !== evt.target) {
                            $(this).val('');
                        }
                    });
                    
                    $(evt.target).closest('.input').find('.preview').css('background-image','none');
                    
                    
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

			$('input:not([type="submit"]):not([type="hidden"]):not(option),select,textarea',clone).each(function() {
                        
                            if(this.type === 'file') {
                                this.name = this.name.replace(/\[[0-9]*\]/,'[' + count + ']');
                            } else {
                                this.name = this.name.replace(/\[[0-9]*\]\[([a-zA-Z0-9_]*?)\]/,'[' + count + '][$1]');
                            }
                            
                            console.log(this.name);

                            this.id = this.id.replace(/-[0-9]*$/, '-' + (count + 1) );
                            $(this).val('');
                            
                        });

			$('input[type="hidden"]',clone).remove();

			$(this).parent().find('ol').append(clone);
                        
                        // remove image elements (for image files)
                        $('img',clone).remove();
                        
                        // remove label for generic files
                        $('input[type=file] + p',clone).remove();
                        
                        $('.preview',clone).replaceWith('<div class="preview"></div>');
                        
			$(clone).show();
			
		});

	})();
	
});


