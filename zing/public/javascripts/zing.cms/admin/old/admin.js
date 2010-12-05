var zing = {};

//
// TinyMCE configuration

zing.TINY_MCE_OPTION_SETS = {
    
    common: {
        script_url: '/javascripts/zing.cms/tiny_mce/tiny_mce.js',
        
        language: 'en',
        docs_language: 'en',
        
        theme: 'advanced',
        theme_advanced_layout_manager: 'SimpleLayout',
        theme_advanced_toolbar_location: 'top',
        theme_advanced_toolbar_align: 'left',
        
        plugin_insertdate_dateFormat: "%d/%m/%Y",
        plugin_insertdate_timeFormat: "%H:%M:%S",
        advlink_styles: "",
        dialog_type: "modal",
        
        content_css: '/stylesheets/content.css',
        
        convert_urls: false,
        relative_urls: false,
        remove_script_host: true,
        
        font_size_style_values: "xx-small,x-small,small,medium,large,x-large,xx-large",
        
        cleanup: true,
        extended_valid_elements: "a[name|href|target|title|onclick],hr[class|width|size|noshade]",
        invalid_elements: "script,style",
        
        verify_css_classes: false,
        verify_html: false
    },
    
    small: { width: 550, height: 120 },
    large: { width: 550, height: 350 },
    
    simple: {
        
    },
    
    advanced: {
        theme_advanced_buttons1: "print,separator,cut,copy,paste,pastetext,pasteword,selectall,separator,undo,redo,separator,search,replace,separator,insertdate,inserttime,separator,ltr,rtl,separator,forecolor,backcolor,visualaid,separator,code",
        theme_advanced_buttons2: "bullist,numlist,separator,outdent,indent,separator,tablecontrols,separator,advhr,anchor,link,unlink,image,charmap",
        theme_advanced_buttons3: "formatselect,styleselect,separator,bold,italic,underline,strikethrough,separator,sub,sup,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,cleanup,removeformat,separator,help",

        plugins: "table,directionality,searchreplace,print,advhr,advlink,contextmenu,insertdatetime,paste,safari",

        theme_advanced_statusbar_location: "bottom",
        theme_advanced_blockformats: "p,div,h1,h2,h3,h4,h5,h6"
    }
    
};

zing.TINY_MCE_DEFAULT_OPTION_SETS = ['large', 'advanced'];

$.rebind(function(context) {
    
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
	 * Any element with class .x-date-picker will become a datepicker, if the Calendar
	 * library has been included on the page.
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
	 			daFormat: time ? "%d/%m/%Y %H:%M" : "%d/%m/%Y",
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
                $.each(zing.TINY_MCE_DEFAULT_OPTION_SETS, function() { optionSets.push(this); });
            }

            var options = {};
            $.each(optionSets, function() {
                $.extend(options, zing.TINY_MCE_OPTION_SETS[this] || {});
            });

            $(this).tinymce(options);

        });
    }
    
});
