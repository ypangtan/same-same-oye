document.addEventListener( 'DOMContentLoaded', function() {

    setInterval( getNotificationList, 5000 );

    $( '#readAllNotification' ).on( 'click', function() {
        seenNotification()
    } );

    $( document ).on( 'click', '.nk-notification-item', function() {
        seenNotification( $( this ).data( 'id' ) );
    } );

    getNotificationList();

    function getNotificationList() {
        $.ajax( {
            url: window.core.getNotificationList,
            type: 'POST',
            data: {
                '_token': window.core.csrfToken,
            },
            success: function( response ) {

                console.log( response );

                $( '#totalNewNotifications' ).html( response.unread );

                $( '.notification-dropdown > a' ).empty();
                if ( response.unread > 0 ) {
                    $( '.notification-dropdown > a' ).html(
                        `<div class="icon-status icon-status-info"><em class="icon ni ni-bell"></em></div>`
                    )
                } else {
                    $( '.notification-dropdown > a' ).html(
                        `<em class="icon ni ni-bell"></em>`
                    );
                }

                $( '.nk-notification' ).empty();

                response.notifications.map( function( v, i ) {
                    renderNotificationList( v );
                } );

                if ( response.notifications.length == 0 ) {
                    $( '.nk-notification' ).append( 
                        `
                        <div class="text-center" style="padding: 1.25rem 1.75rem;">
                            <div class="nk-notification-content">
                                ` + window.core.message.no_notification + `
                            </div>
                        </div>
                        `
                    );
                }
            },
            error: function( error ) {

            }
        } );
    }

    function seenNotification( id = 0 ) {

        $.ajax( {
            url: window.core.seenNotification,
            type: 'POST',
            data: {
                id,
                '_token': window.core.csrfToken,
            },
            success: function( response ) {
                getNotificationList();
            },
        } );
    }

    function renderNotificationList( item ) {

        let isRead = item.is_read == 1 ? 'read' : 'unread';

        let html = 
        `
        <a href="` + item.url + `">
            <div class="nk-notification-item dropdown-inner ` + isRead + `" data-id="` + item.id + `">
                <div class="nk-notification-icon">
                    <em class="icon icon-circle bg-warning-dim ni ` + item.icon + `"></em>
                </div>
                <div class="nk-notification-content">
                    <strong>` + item.system_title + `</strong>
                    <div class="nk-notification-text">` + item.system_content + `</div>
                    <div class="nk-notification-time">` + item.time_ago + `</div>
                </div>
            </div>
        </a>
        `;

        $( '.nk-notification' ).append( html );
    }
} );