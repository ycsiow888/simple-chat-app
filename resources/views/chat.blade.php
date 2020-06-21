@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Online Status: <i id="online_status"></i>
                </div>

                <div class="panel-body" id="chat">
                    <ul class="chat">

                    </ul>
                </div>

                <div class="panel-footer">
                    <span class="typing" style="font-style:italic">Other user is typing...</span>

                    {{-- <chat-form v-on:messagesent="addMessage" :user="{{ Auth::user() }}">
                    </chat-form> --}}
                    <div class="input-group">

                        <input id="btn-input" type="text" name="message" class="form-control input-sm"
                            placeholder="Type your message here..." v-model="newMessage" @keyup.enter="sendMessage">

                        <span class="input-group-btn">
                            <button class="btn btn-primary btn-sm" type="submit" id="btn-chat" onClick="sendMessage()">
                                Send
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    const messagesContainer = $('#chat');
    const chat = $('.chat');

    let currentUser = "{{ Auth::user() }}";
    let currentUserName = "{{ Auth::user()->name }}";
    let currentUserId = "{{ Auth::user()->id }}";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // get user online
    getUser();

    // fetch message
    fetchMessage();

    $("#btn-input").keyup(function (event) {
        if (event.keyCode === 13) {
            $("#btn-chat").click();
        }
    });

    $('.typing').hide()

    // when user not focusing on input then no longer typing message
    $('#btn-input').focusout(function(){
        let channel = Echo.private('chat')

        channel.whisper('typing', {
                user: currentUserId,
                typing: false
            })
    })

    // when user typing telling other user for typing event
    $('#btn-input').on('keydown', function (e) {
        let channel = Echo.private('chat')
        let typing = false;
        if (this.value != '') {
            typing = true;
        }
        setTimeout(() => {
            typing=true;
            if (this.value == '') {
            typing = false;
        }
            channel.whisper('typing', {
                user: currentUserId,
                typing: typing
            })
        }, 300)
    })

    // listen other user for typing event and whenever receiving message
    Echo.private('chat')
        .listenForWhisper('typing', (e) => {
            if (e.user != currentUserId && e.typing) {
                $('.typing').show();
            } else {
                $('.typing').hide();
            }
        })
        .listen('MessageDelivered', (e) => {
            let image =
                "{{ URL::asset('image/green_tick.svg') }}";
            $('.pending_image').attr('src', image);
        })
    

    // update user online status and send message
    Echo.join('chat')
        .listen('UserOnlines', (e) => {
            $('#online_status').text('Online').css({
                'color': 'green',
                'font-weight': 'bold'
            });
        }).listen('UserOffline', (e) => {
            $('#online_status').text('Offline').css({
                'color': 'red',
                'font-weight': 'bold'
            });
        }).listen('MessageSent', (e) => {
            // This will happen whenever receiving message from sender

            let style = "text-align:left";
            if (currentUserName == e.user.name) {
                style = "text-align:right";
            }

            let appendHtml = '<li class="left clearfix" style=' + style + '>';
            appendHtml += '<div class="chat-body clearfix">';
            appendHtml += '<div class="header">';
            appendHtml += '<strong class="primary-font">';
            appendHtml += e.user.name;
            appendHtml += '</strong>';
            appendHtml += '</div>';
            appendHtml += '<p class="pending_reading">';
            appendHtml += e.message.message;
            appendHtml += '</p>';
            appendHtml +=
                '<img class="pending_image" style="width:10px;height:10px" src="{{ URL::asset("image/grey_tick.png") }}"></img>';
            appendHtml += '</div>';
            appendHtml += '</li>';

            chat.append(appendHtml);

            messagesContainer.scrollTop(messagesContainer.prop("scrollHeight"));
        });



    $("#logout").click(function () {
        axios.post('/offline', {
            id: currentUserId
        }).then(response => {});
    });



    function sendMessage() {
        let message = $('#btn-input').val();

        if (message != '') {
            axios.post('/messages', {
                user: currentUser,
                message
            }).then(response => {
                if ($('#online_status').text() == 'Online') {
                    axios.post("/receivedMessages", {
                        id: response.data.data.id
                    }).then(response => {});
                }
            });

            $('#btn-input').val('');
        }
    }

    function fetchMessage() {
        axios.get('/messages').then(response => {
            response.data.map(item => {
                let style = "text-align:left";
                let image =
                    "<img src ='{{ URL::asset('image/green_tick.svg') }}' style='height:10px;width:10px'></img>";
                if (currentUserId == item.user_id) {
                    style = "text-align:right";
                }
                if (item.status != 'Delivered') {
                    image =
                        "<img src ='{{ URL::asset('image/grey_tick.png') }}' style='height:10px;width:10px'></img>";
                }
                let appendHtml = '<li class="left clearfix" style=' + style + '>';
                appendHtml += '<div class="chat-body clearfix">';
                appendHtml += '<div class="header">';
                appendHtml += '<strong class="primary-font">';
                appendHtml += item.user.name;
                appendHtml += '</strong>';
                appendHtml += '</div>';
                appendHtml += '<p>';
                appendHtml += item.message;
                appendHtml += '</p>';
                appendHtml += image;
                appendHtml += '</div>';
                appendHtml += '</li>';
                chat.append(appendHtml);
                messagesContainer.scrollTop(messagesContainer.prop("scrollHeight"));
            })
        });
    }

    function getUser() {
        axios.get('/getUser', {}).then(response => {
            if (response.data.online_status == 'offline') {
                $('#online_status').text('Offline').css({
                    'color': 'red',
                    'font-weight': 'bold'
                });
            } else {
                $('#online_status').text('Online').css({
                    'color': 'green',
                    'font-weight': 'bold'
                });
            }
        });
    }
</script>

@endsection