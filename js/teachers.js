/* directives for http://JSLint.com */
/*jslint browser, for, this, white, multivar */
/*global $, OT, console */

var Teacher = Object.create(LanguageLab);

Teacher.groupContainerID = 'groups';

Teacher.makeConnection = function() {
    $(document).ready(function() {
        $.getJSON(Teacher.rootURL + '/api/groups.php?context=' + Teacher.context, function(response) {
            if (response.groups !== undefined) {
                for(var i = 0; i < response.groups.length; i++) {
                    Teacher.displayGroup(response.groups[i].group);
                }
            }
        });
        $.getJSON(Teacher.rootURL + '/api/session.php?context=' + Teacher.context + '&user=' + Teacher.user + '&user_name=' + Teacher.userName, function(response) {
            Teacher.initializeSession(response.api_key, response.session_id, response.token);
        });
    });
}

Teacher.displayGroup = function(id) {
    // FIXME should really be displaying an OpenTok session for this group
    $('#' + Teacher.groupContainerID).append(
        '<div id="wrapper-' + id + '" class="droppable">' +
            '<p class="label label-info">' +
                'Group ' + id + ' ' +
                '<a href="javascript:Teacher.deleteGroup(' + id + ');"><span class="glyphicon glyphicon-remove"></span></a>' +
            '</p>' +
            '<ul id="' + id + '" class="connected"></ul>' +
        '</div>'
        );
        $('.connected').sortable({
            connectWith: '.connected',
            opacity: 0.5,
            placeholder: 'placeholder col-xs-4',
            update: Teacher.sortableUpdate
        });
}

Teacher.sortableUpdate = function(event, ui) {
    /*
     * using the 'receive callback' -- won't trigger if position
     * within list is changed only if the list it is in changes
     * (cf. https://forum.jquery.com/topic/sortables-update-callback-and-connectwith#14737000000631169)
     */
    if (this === ui.item.parent()[0]) {
        /* handle event for new list */
        var groupID = $(ui.item[0]).parent().attr('id');
        var user = $(ui.item[0]).find('.embed-responsive-item').attr('user');
        if (groupID === Teacher.thumbnailContainerID) {
            $.getJSON(Teacher.rootURL + '/api/group_membership.php?context=' + Teacher.context + '&user=' + user + '&action=reset');
        } else {
            $.getJSON(Teacher.rootURL + '/api/group_membership.php?context=' + Teacher.context + '&user=' + user + '&group=' + groupID);
        }
        // TODO delete this object
    } else {
        /* handle event for old list */
        // disconnect user from previous session so they will reconnect to new session
        // need to have the session variable and the stream ID of the user I want to disconnect
        //TODO: streamID
        //Teacher.forceUnpublish();
    }
}

Teacher.addGroup = function() {
    $.getJSON(Teacher.rootURL + '/api/session.php?context=' + Teacher.context + '&user=' + Teacher.user + '&user_name=' + Teacher.userName + '&type=group', function(response) {
        Teacher.displayGroup(response.group);
    });
}

Teacher.deleteGroup = function (id) {
    $.getJSON(Teacher.rootURL + '/api/groups.php?context=' + Teacher.context + '&group=' + id + '&action=delete', function(response) {
        if (response.result) {
            $('#wrapper-' + id).remove();
        } else {
            alert('Error!');
        }
    });
}

Teacher.resetGroups = function() {
    // TODO make list of users and OpenTok sessions affected
    $.getJSON(Teacher.rootURL + '/api/groups.php?context=' + Teacher.context + '&action=reset', function (response) {
        if (response.result) {
            $('#' + Teacher.groupContainerID).empty();
        } else {
            alert('Error!');
        }
    });

    // disconnect users from their (now deleted) OpenTok sessions, forcing them to reconnect to the class session
    Teacher.forceDisconnect();

    // Teacher.unpublish();
}