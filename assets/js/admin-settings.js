jQuery(document).ready(function($) {
	var message, container, messageBox, deleteInput, buttons, confirm, cancel, lineBreak;
	container = document.createElement( 'div' );
	messageBox = document.createElement( 'p' );
	deleteInput = document.createElement( 'input' );
	deleteInput.type = 'text';
	deleteInput.id = 'confirmDeleteInput';
	buttons = document.createElement( 'div' );
	buttons.style.marginTop = '10px';
	buttons.style.backgroundColor = '#f4f5f6';
	confirm = document.createElement( 'div' );
	confirm.style.padding = '8px';
	confirm.style.cursor = 'default';
	confirm.style.backgroundColor = '#d9534f';
	confirm.style.borderColor = '#d9534f';
	confirm.style.fontSize = '14pt';
	confirm.style.fontWeight = 'bold';
	confirm.style.color = '#ffffff';
	confirm.style.borderRadius = '4px';
	cancel = document.createElement( 'div' );
	cancel.style.padding = '8px';
	cancel.style.cursor = 'default';
	cancel.style.backgroundColor = '#5bc0de';
	cancel.style.borderColor = '#5bc0de';
	cancel.style.fontSize = '14pt';
	cancel.style.fontWeight = 'bold';
	cancel.style.color = '#ffffff';
	cancel.style.borderRadius = '4px';
	lineBreak = document.createElement( 'br' );
	container.classList.add( 'message' );
	messageBox.innerHTML += 'This will DELETE all forms, form submissions,' +
		' and deactivate Ninja Forms';

	messageBox.appendChild( lineBreak );
	messageBox.innerHTML += '<br>Type <span style="color:red;">DELETE</span>' +
		' to' +
		' confirm';

	container.appendChild( messageBox );
	container.appendChild( deleteInput );
	container.appendChild( lineBreak );
	confirm.innerHTML = 'Delete';
	confirm.classList.add( 'confirm', 'nf-button', 'primary' );
	confirm.style.float = 'left';
	cancel.innerHTML = 'Cancel';
	cancel.classList.add( 'cancel', 'nf-button', 'secondary' );
	cancel.style.float = 'right';
	buttons.appendChild( confirm );
	buttons.appendChild( cancel );
	buttons.classList.add( 'buttons' );
	container.appendChild( buttons );
	message = document.createElement( 'div' );
	message.appendChild( container );

	deleteAllDataModel = new jBox( 'Modal', {
		width: 450,
		addClass: 'dashboard-modal',
		overlay: true,
		closeOnClick: 'body'
	} );

	deleteAllDataModel.setContent( message.innerHTML );
	deleteAllDataModel.setTitle( 'Delete All Ninja Forms Data?' );

	var btnCancel = deleteAllDataModel.container[0].getElementsByClassName('cancel')[0];
	btnCancel.addEventListener('click', function() {
		deleteAllDataModel.close();
	} );

	var startDeletions = function() {
		console.log('do it');
	};

	var btnDelete = deleteAllDataModel.container[0].getElementsByClassName('confirm')[0];
	btnDelete.addEventListener('click', function() {
		var confirmVal = $( '#confirmDeleteInput' ).val();

		if ( 'DELETE' === confirmVal ) {
			startDeletions();
		} else {
			deleteAllDataModel.close();
		}
	} );

    $( '.js-delete-saved-field' ).click( function(){

        var that = this;

        var data = {
            'action': 'nf_delete_saved_field',
            'field': {
                id: $( that ).data( 'id' )
            },
            'security': nf_settings.nonce
        };

        $.post( nf_settings.ajax_url, data )
            .done( function( response ) {
                $( that ).closest( 'tr').fadeOut().remove();
            });
    });

    $( '#nfRollback' ).on( 'click', function( event ){
        var rollback = confirm( nf_settings.i18n.rollbackConfirm );
        if( ! rollback ){
            event.preventDefault();
        }
    });


	$( document ).on( 'click', '#delete_on_uninstall', function( e ) {
		deleteAllDataModel.open();
	} );

	$( document ).on( 'click', '.nf-delete-on-uninstall-yes', function( e ) {
		e.preventDefault();
		$( "#delete_on_uninstall" ).attr( 'checked', true );

	} );
});
