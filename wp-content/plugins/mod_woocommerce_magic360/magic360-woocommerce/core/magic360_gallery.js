 jQuery( function( $ ) {

    $(document).ready(function(){
        if ($('.magic360-image').length > 5){
            $('.magic360-delete-all').bind('click', deleteAll);
            
        }
        updateOptions();
        $('#magic360_columns').bind('input', columnsChanged);
        $('#magic360_multi_rows').bind('click', multiSpinCheck);
    });
    // Product gallery file uploads
    var magic360_gallery_frame;
    var $magic360_data = $( '#magic360_data' );
    var $magic360_images    = $( '#magic360_images_container' ).find( 'ul.magic360_images' );

    jQuery( '.add_magic360_images' ).on( 'click', 'a', function( event ) {
        var $el = $( this );

        magic360_dataObject = JSON.parse($magic360_data.val());

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( magic360_gallery_frame ) {
            magic360_gallery_frame.open();
            return;
        }

        // Create the media frame.
        magic360_gallery_frame = wp.media.frames.product_gallery = wp.media({
            // Set the title of the modal.
            title: $el.data( 'choose' ),
            button: {
                text: $el.data( 'update' )
            },
            states: [
                new wp.media.controller.Library({
                    title: $el.data( 'choose' ),
                    filterable: 'all',
                    multiple: true
                })
            ]
        });

        // When an image is selected, run a callback.
        magic360_gallery_frame.on( 'select', function() {
            var selection = magic360_gallery_frame.state().get( 'selection' );
            var attachment_ids = magic360_dataObject.images_ids;

            //attachment_ids = attachment_ids.length == 0? [] : attachment_ids[0].split(',');

            selection.map( function( attachment ) {
                attachment = attachment.toJSON();

                if ( attachment.id ) {
                    attachment_ids.push(attachment.id);
                    var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                    $magic360_images.append( '<li class="magic360-image" data-attachment_id="' + attachment.id + '"><img src="' + attachment_image + '" /><ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">' + $el.data('text') + '</a></li></ul></li>' );
                }
            });

            if ($('.magic360-image').length > 5 && $('.magic360-delete-all').length == 0){
                $('#magic360_images_container').append('<a class="button button-primary button-large magic360-delete-all">Delete all images</a>');
                $('.magic360-delete-all').bind('click', deleteAll);
            }
                disableChecbox();
                magic360_dataObject.images_ids = attachment_ids
                if(!magic360_dataObject.options.set_columns){
                    magic360_dataObject.options.columns = attachment_ids.length;
                    $('#magic360_columns').val(attachment_ids.length);
                }
                $magic360_data.val( JSON.stringify(magic360_dataObject) );
        });

        // Finally, open the modal.
        magic360_gallery_frame.open();
    });


    // delete an item
    $( '#magic360_images_container' ).on( 'click', 'a.delete', function() {
        $( this ).closest( 'li.magic360-image' ).remove();

        if ($('.magic360-image').length <= 5 && $('.magic360-image').length > 0){
            $('.magic360-delete-all').unbind('click');
            $('.magic360-delete-all').remove();
        }

        var attachment_ids = [];

        $( '#magic360_images_container' ).find( 'ul li.magic360-image' ).css( 'cursor', 'default' ).each( function() {
            attachment_ids.push(jQuery( this ).attr( 'data-attachment_id' ) );
        });

        disableChecbox();
        magic360_dataObject = JSON.parse($magic360_data.val());
        if( $('.magic360-image').length == 0 ){
            magic360_dataObject.options.set_columns = false;    
        }

        magic360_dataObject.images_ids = attachment_ids;
        if(!magic360_dataObject.options.set_columns){
            $('#magic360_columns').val(magic360_dataObject.images_ids.length);
            magic360_dataObject.options.columns = attachment_ids.length;   
        }
        $magic360_data.val( JSON.stringify(magic360_dataObject) );

        // remove any lingering tooltips
        $( '#tiptip_holder' ).removeAttr( 'style' );
        $( '#tiptip_arrow' ).removeAttr( 'style' );

        return false;
    });

    // Remove all images
    function deleteAll() {
        $('ul.magic360_images').empty();
        $('.magic360-delete-all').unbind('click');
        $('.magic360-delete-all').remove();
        var attachment_ids = [];

        magic360_dataObject = JSON.parse($magic360_data.val());
        magic360_dataObject.images_ids = attachment_ids;
        magic360_dataObject.options.columns = attachment_ids.length;
        magic360_dataObject.options.set_columns = false;
        $magic360_data.val( JSON.stringify(magic360_dataObject) );
        $('#magic360_multi_rows').attr('checked', false);
        $('#magic360_columns').attr("disabled", true);
        $('#magic360_columns').val('0');
        disableChecbox();

    }


    function multiSpinCheck(){
        magic360_dataObject = JSON.parse($magic360_data.val());

        if($('#magic360_multi_rows').is(":checked")){
            $('#magic360_columns').attr("disabled", false);
        }else{
            $('#magic360_columns').attr("disabled", true);
            $('#magic360_columns').val(magic360_dataObject.images_ids.length);
            magic360_dataObject.options.columns = $('#magic360_columns').val();
            magic360_dataObject.options.set_columns = false;
            $magic360_data.val( JSON.stringify(magic360_dataObject) );

        }

        magic360_dataObject.options.checked = $('#magic360_multi_rows').is(":checked");
        $magic360_data.val( JSON.stringify(magic360_dataObject) );
    }

    function updateOptions () {
        magic360_dataObject = JSON.parse($magic360_data.val());
        disableChecbox();
        $('#magic360_multi_rows').attr('checked', magic360_dataObject.options.checked);
        $('#magic360_columns').attr('disabled', !magic360_dataObject.options.checked);
        $('#magic360_columns').val(magic360_dataObject.options.columns);
    }


    function columnsChanged(){
        magic360_dataObject = JSON.parse($magic360_data.val());
        magic360_dataObject.options.set_columns = true;
        magic360_dataObject.options.columns = $('#magic360_columns').val();
        $magic360_data.val( JSON.stringify(magic360_dataObject) );
    }

    function disableChecbox(){
        if($('.magic360-image').length == 0){
            $('#magic360_multi_rows').attr('disabled', true);
            $('#magic360_multi_rows').attr('checked', false);
            $('#magic360_columns').attr('disabled', true);
            $('#magic360_columns').val('0');
        }else{
            $('#magic360_multi_rows').attr('disabled', false);
        }
    }

});