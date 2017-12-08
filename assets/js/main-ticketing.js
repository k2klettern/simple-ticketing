/**
 * Created by Eric Zeidan on 09/02/2017.
 */
jQuery(document).ready(function($) {
    $('a.delete-ticket').on('click', function(e){
        e.preventDefault();
        if (!confirm("Are you sure to delete this Ticket?")) {
            return false;
        }
        var esta = $(this);
        var post_id = $(this).attr('data-postid');
        var nonce = $(this).attr('data-nonce');
        $.ajax({
            type: 'post',
            dataType : "json",
            url: myajaxvars.ajaxurl,
            data: {
                action: "st_delete_ticketing",
                nonce: nonce,
                postid: post_id
            },
            success: function (response) {
                if(response.type == "success") {
                    $(esta).closest('tr').remove();
                }
                else {
                    alert('error: ' + response.text);
                }
            }
        });
    });

    $('a.close-ticket').on('click', function(e){
        e.preventDefault();
        if (!confirm("Are you sure to close this Ticket?")) {
            return false;
        }
        var esta = $(this);
        var post_id = $(this).attr('data-postid');
        var nonce = $(this).attr('data-nonce');
        $.ajax({
            type: 'post',
            dataType : "json",
            url: myajaxvars.ajaxurl,
            data: {
                action: "st_close_ticketing",
                nonce: nonce,
                postid: post_id
            },
            success: function (response) {
                if(response.type == "success") {
                    location.reload();
                }
                else {
                    alert('error: ' + response.text);
                }
            }
        });
    });

    $('a.reopen-ticket').on('click', function(e){
        e.preventDefault();
        if (!confirm("Are you sure to re-open this Ticket?")) {
            return false;
        }
        var esta = $(this);
        var post_id = $(this).attr('data-postid');
        var nonce = $(this).attr('data-nonce');
        $.ajax({
            type: 'post',
            dataType : "json",
            url: myajaxvars.ajaxurl,
            data: {
                action: "st_reopen_ticketing",
                nonce: nonce,
                postid: post_id
            },
            success: function (response) {
                if(response.type == "success") {
                    location.reload();
                }
                else {
                    alert('error: ' + response.text);
                }
            }
        });
    });

} );

                var options = {
               valueNames: [ 'postname', 'status', 'date', 'moddate' ]
              };

                var userList = new List('tickets', options);
