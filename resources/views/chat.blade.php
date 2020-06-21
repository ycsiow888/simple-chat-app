@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Chats</div>

                <div class="panel-body" id="chat">
                    <ul class="chat">

                    </ul>
                </div>

                <div class="panel-footer">
                    <span class="typing" style="font-style:italic">Other user is typing...</span>

                    {{-- <chat-form v-on:messagesent="addMessage" :user="{{ Auth::user() }}"></chat-form> --}}
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
        let currentUserId = "{{Auth::user()->id}}";
    
        $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $("#btn-input").keyup(function(event) {
        if (event.keyCode === 13) {
            $("#btn-chat").click();
        }
    });
    
    fetchMessage();
    $('.typing').hide()
    
    $('#btn-input').on('keydown', function(e){
      let channel = Echo.private('chat')
      let typing=false;
      if(this.value != ''){
          typing=true;
      }
      setTimeout( () => {
        channel.whisper('typing', {
          user: currentUserId,
          typing: typing
        })
      }, 300)
    })
    Echo.private('chat')
      .listenForWhisper('typing', (e) => {
          if(e.user != currentUserId && e.typing){
    $('.typing').show();
          }else{
              $('.typing').hide();
          }
      })
    Echo.private('chat')
                .listen('MessageSent', (e) => {
                    console.log("listening to Private chat hannel");
                    console.log('Listening to event MessageSent');
                    console.log("Receieved message : ",e);
                    let style = "text-align:left";
                    if(currentUserName == e.user.name){
                        style = "text-align:right";
                    }
                    let appendHtml = '<li class="left clearfix" style='+style+'>';
                                appendHtml +='<div class="chat-body clearfix">';
                            appendHtml +=         '<div class="header">';
                            appendHtml +=             '<strong class="primary-font">';
                                appendHtml += e.user.name;
                                       appendHtml +=  '</strong>';
                                    appendHtml += '</div>';
                                  appendHtml +=   '<p>';
                                    appendHtml +=e.message.message;
                                   appendHtml +=  '</p>';
                                appendHtml += '</div>';
                            appendHtml += '</li>';
                            chat.append(appendHtml);
                            messagesContainer.scrollTop(messagesContainer.prop("scrollHeight"));
                });
    
        function sendMessage(){
            let message = $('#btn-input').val();
            axios.post('/messages', {user:currentUser,message}).then(response => {});
            $('#btn-input').val('');
        }
        
        function fetchMessage(){
            axios.get('/messages').then(response => {
                console.log("fetching message from db",response);
    
                response.data.map(item=>{
                let style = "text-align:left";
                    if(currentUserId == item.user_id){
                        style = "text-align:right";
                    }
                    let appendHtml = '<li class="left clearfix" style='+style+'>';
                                appendHtml +='<div class="chat-body clearfix">';
                            appendHtml +=         '<div class="header">';
                            appendHtml +=             '<strong class="primary-font">';
                                appendHtml += item.user.name;
                                       appendHtml +=  '</strong>';
                                    appendHtml += '</div>';
                                  appendHtml +=   '<p>';
                                    appendHtml +=item.message;
                                   appendHtml +=  '</p>';
                                appendHtml += '</div>';
                            appendHtml += '</li>';
                            chat.append(appendHtml);
                            messagesContainer.scrollTop(messagesContainer.prop("scrollHeight"));
                })
                });
        }
</script>

@endsection