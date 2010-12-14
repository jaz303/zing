//
// Default Rebind

$.rebind(function(context) {
	
	var config = zing.cms.config.admin;
    
    /*
     * Select boxes as navigation
     */ 
    $('select.x-link-select', context).change(function() {
        document.location.href = this.options[this.selectedIndex].value;
    });
    
    /*
     * Multi-select boxes
     */
    $('ul.multi-select', context).click(function(evt) {
        if (evt.target.nodeName.toLowerCase() == 'li') {
            $('input[type=checkbox],input[type=radio]', evt.target).click();
        }
    });
    
    /*
	 * Datepickers
	 *
	 * Any element with class .date-picker or .datetime-picker will become a datepicker,
	 * if the Calendar library has been included on the page.
	 */
    if (typeof Calendar != 'undefined') {
        var calendarSeq = 0;
	 	$('.datetime-picker, .date-picker').each(function() {
	 	    
	 	    var input   = $('input[type=hidden]', this)[0],
	 	        display = $('input[type=text]', this)[0],
	 	        button  = $('a', this)[0];
	 	        
            input.id    = 'dt-' + (calendarSeq++);
            display.id  = 'dt-' + (calendarSeq++);
            button.id   = 'dt-' + (calendarSeq++);
            
            var time    = $(this).is('.datetime-picker');
	 	    
	 	    Calendar.setup({
	 			inputField: input.id,
	 			displayArea: display.id,
	 			button: button.id,
	 			ifFormat: "%Y-%m-%dT%H:%M:%S",
	 			daFormat: time ? config.datePicker.formatWithTime : config.datePicker.format,
				firstDay: 1,
	 			showsTime: time,
	 			timeFormat: 24,
	 			date: '2010-05-22T11:15:00'
	 		});
	 	});
	}
    
    /*
     * TinyMCE
     */
    if (typeof $.fn.tinymce == 'function') {
        $('textarea.tinymce').each(function() {
            var optionSets = ['common'];
            
            $.each(this.className.split(/\s+/), function() {
                if (this.match(/^tinymce-options-(.*?)$/)) optionSets.push(RegExp.$1);
            });

            if (optionSets.length == 1) {
                $.each(config.tinyMCE.defaultOptionSets, function() { optionSets.push(this); });
            }

            var options = {};
            $.each(optionSets, function() {
                $.extend(options, config.tinyMCE.optionSets[this] || {});
            });

			options.script_url = '/javascripts/zing.cms/tiny_mce/tiny_mce.js',
			
            $(this).tinymce(options);

        });
    }
    
});

//
// Admin Initialisation

$(function() {
	Rebind.one(document);
});