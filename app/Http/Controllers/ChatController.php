<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function store(Request $request){

        echo $request->ad_id . '<br/>';
        echo $request->coach_id . '<br/>';
        echo Auth::id();

        $chat_exists = Chat::where('coach_id', '=', $request->coach_id)
            ->where('advertisement_id', '=', $request->ad_id)
            ->where('student_id', '=', Auth::id())->exists();


        if($chat_exists == null){

            $chat = new Chat;
            $chat->advertisement_id = $request->ad_id;
            $chat->coach_id = $request->coach_id;
            $chat->student_id = Auth::id();
            $chat->save();
            return redirect('/chat');

        }

        else{

            return redirect('/chat');

        }


    }

    public function overview(){
        $chats = Chat::where('student_id',Auth::id())->orWhere('coach_id',Auth::id())->get();
         return view('chat',compact('chats'));
    }

    public function show($chat_id){

        $chat_info = Chat::select('coach_id','student_id')->where('id',$chat_id)->first();

        if( Auth::id() != $chat_info->coach_id && Auth::id() != $chat_info->student_id ){
            return redirect('/home');
        }
        $messages = Message::where('chat_id',$chat_id)->get();
        return view('chatscreen',compact('messages','chat_id'));

    }

    public function storeMessage(Request $request){

        $message = new Message;
        $message -> chat_id = $request -> chat_id;
        $message -> sender_id = Auth::id();
        $message -> message = $request -> message;
        $message -> save();
        return redirect('/chat/'.$request->chat_id);
    }

    public function ajaxLoad($chat_id){

        $chat_info = Chat::select('coach_id','student_id')->where('id',$chat_id)->first();

        if( Auth::id() != $chat_info->coach_id && Auth::id() != $chat_info->student_id ){
            return redirect('/home');
        }
        $messages = Message::where('chat_id',$chat_id)->get();
        $message_log = array();

        foreach($messages as $message){

            $user = $message->user->name;

            $message_info = array();
            $message_info['sender'] = $user;
            $message_info['message'] = $message['message'];
            array_push($message_log,$message_info);


        }

        return $message_log;
    }
}
