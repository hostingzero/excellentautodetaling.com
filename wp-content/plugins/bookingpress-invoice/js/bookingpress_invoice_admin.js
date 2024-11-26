var bookingpress_cursor_current_pos = 0;
var bookingpress_current_value_of_editor = '';
(function() {
    jQuery('#bookingpress_invoice_template_builder').trumbowyg({
        btns: [
            ['strong', 'em', 'del'],
            ['justifyLeft', 'justifyCenter', 'justifyRight'],
            ['insertImage'],
            ['horizontalRule'],
            ['undo', 'redo'],
            ['table'],
            ['fontsize'],
            ['foreColor'],
        ],
        plugins: {
            fontsize: {
                sizeList: [
                    '10px', '12px', '14px', '16px', '18px', '20px', '22px', '24px'
                ]
            }
        }
    })
    .on('tbwfocus', function(e){
        console.log(e.currentTarget.offsetTop);
        console.log(e.currentTarget.offsetWidth);
        bookingpress_current_value_of_editor = e.currentTarget.value;
        bookingpress_cursor_current_pos = e.currentTarget.selectionStart;
    })
    .on('tbwblur', function(e){
        jQuery('#bookingpress_invoice_template_builder').trumbowyg('restoreRange')
        jQuery('#bookingpress_invoice_template_builder').trumbowyg('saveRange')
    });
})();

function bookingpress_save_html_content(){
    var bookingpress_invoice_modified_html = jQuery('#bookingpress_invoice_template_builder').trumbowyg('html');
    return bookingpress_invoice_modified_html;
}

function bookingpress_add_tag(inserting_tag){
    // jQuery('#bookingpress_invoice_template_builder').trumbowyg('restoreRange');

    // jQuery('#bookingpress_invoice_template_builder').trumbowyg('execCmd', {
    //     //cmd: 'insertText',
    //     cmd: 'insertHTML',
    //     param: 'This is testing',
    //     forceCss: false
    // });
    /*jQuery('#bookingpress_invoice_template_builder').trumbowyg('saveRange');
    let value = jQuery('#bookingpress_invoice_template_builder').trumbowyg('getRangeText');
    console.log(value);*/

    console.log(bookingpress_current_value_of_editor.length);
    console.log(bookingpress_cursor_current_pos);
    var front = bookingpress_current_value_of_editor.substring(0, bookingpress_cursor_current_pos);
	var back = (bookingpress_current_value_of_editor).substring(bookingpress_cursor_current_pos, bookingpress_current_value_of_editor.length); 
	bookingpress_current_value_of_editor = front + inserting_tag + back;
}