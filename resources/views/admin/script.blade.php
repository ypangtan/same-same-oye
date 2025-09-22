    <script src="{{ asset( 'admin/js/bundle.js' ) }}"></script>
    <script src="{{ asset( 'admin/js/scripts.js' ) }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    {{-- <script src="https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.js"></script> --}}
    <script src="{{ asset( 'admin/js/jquery.loading.min.js' ) . Helper::assetVersion() }}"></script>
    <script src="{{ asset( 'admin/js/flatpickr-4.6.13.js' ) . Helper::assetVersion() }}"></script>
    <script src="{{ asset( 'admin/js/flatpickr-monthSelect.js' ) . Helper::assetVersion() }}"></script>
    <script src="{{ asset( 'admin/js/select2.min.js' ) . Helper::assetVersion() }}"></script>
    <script src="{{ asset( 'admin/js/notification-helper.js' ) . Helper::assetVersion() }}"></script>


    <!-- DataTables -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.dataTables.js"></script>

    <!-- JSZip for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- pdfMake for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- Buttons for HTML5 export -->
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    
    <!-- jsDelivr :: Sortable :: Latest (https://www.jsdelivr.com/package/npm/sortablejs) -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>

        window['core'] = {
            csrfToken: '{{ csrf_token() }}',
            getNotificationList: '{{ route( 'admin.core.getNotificationList' ) }}',
            seenNotification: '{{ route( 'admin.core.seenNotification' ) }}',
            message: {
                no_notification: '{{ __( 'notification.no_notification' ) }}',
            }
        }

        $.fn.select2.amd.define('select2/i18n/zh',[],function () {
            return {
                searching: () => '查找中…',
                noResults: () => '未找到结果',
            }
        } );

        Number.prototype.toFixedDown = function(digits) {
		var re = new RegExp("(\\d+\\.\\d{" + digits + "})(\\d)"),
			m = this.toString().match(re);
		return m ? parseFloat(m[1]).toFixed(digits) : this.valueOf().toFixed( 2 ).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        let modalSuccess = new bootstrap.Modal( document.getElementById( 'modal_success' ) ),
            modalDanger = new bootstrap.Modal( document.getElementById( 'modal_danger' ) ),
            modalWarning = new bootstrap.Modal( document.getElementById( 'modal_warning' ) );

        function resetInputValidation() {

            $( '.dropzone' ).each( function( i, v ) {
                if ( $( this ).hasClass( 'is-invalid' ) ) {
                    $( this ).removeClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).html( '' );
                }
            } );

            $( '.form-control' ).each( function( i, v ) {
                if ( $( this ).hasClass( 'is-invalid' ) ) {
                    $( this ).removeClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).html( '' );
                }
            } );

            $( '.form-select' ).each( function( i, v ) {
                if ( $( this ).hasClass( 'is-invalid' ) ) {
                    $( this ).removeClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).html( '' );
                }
            } );
        }

        function buttonSubmitting( e ) {

            $( e ).addClass( 'disabled' );
        }

        function buttonSubmitted( e ) {

            $( e ).removeClass( 'disabled' );
        }

        document.addEventListener( 'DOMContentLoaded', function() {

            $( '#_logout' ).click( function( e ) {

                e.preventDefault();

                $.ajax( {
                    url: '{{ route( 'admin.signout' ) }}',
                    type: 'POST',
                    data: { '_token': '{{ csrf_token() }}' },
                    success: function() {
                        document.getElementById( 'logoutForm' ).submit();
                    }
                } );
            } );

            $( document ).on( 'focus', '.form-control', function() {
                if ( $( this ).hasClass( 'is-invalid' ) ) {
                    $( this ).removeClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( '' );
                }
            } );

            $( document ).on( 'focus', '.form-select', function() {
                if ( $( this ).hasClass( 'is-invalid' ) ) {
                    $( this ).removeClass( 'is-invalid' ).nextAll( 'div.invalid-feedback' ).text( '' );
                }
            } );

            $( document ).on( 'hidden.bs.modal', '.modal', function() {
                $( '.modal .form-control' ).removeClass( 'is-invalid' ).val( '' ).nextAll( 'div.invalid-feedback' ).text( '' );
                $( '.modal .form-select' ).removeClass( 'is-invalid' ).val( '' ).nextAll( 'div.invalid-feedback' ).text( '' );
            } );

            let parents = [];
            let menus = [];

            // and when you show it, move it to the body
            $( '.datatable' ).on( 'show.bs.dropdown', function( e ) {

                let target = $( e.target );

                // save the parent
                parents.push( target.parent() );

                // grab the menu
                let dropdownMenu = target.next();

                // save the menu
                menus.push( dropdownMenu );

                // detach it and append it to the body
                $( 'body' ).append( dropdownMenu.detach() );

                // grab the new offset position
                let eOffset = target.offset();

                // make sure to place it where it would normally go (this could be improved)
                dropdownMenu.css( {
                    'display': 'block',
                    'top': eOffset.top + target.outerHeight(),
                    'left': eOffset.left
                } );
            } );

            // and when you hide it, reattach the drop down, and hide it normally
            $( '.datatable-wrap' ).on( 'hide.bs.dropdown', function( e ) {

                menus.forEach( function( element, index ) {
                    let parent = parents[index];
                    let dropdownMenu = element;

                    parent.append( dropdownMenu.detach() );
                    dropdownMenu.hide();

                    menus.splice( index, 1 );
                    parents.splice( index, 1 );
                } )
            } );
        } );
    </script>