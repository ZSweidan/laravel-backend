<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class PostController extends Controller
{  /*
    In Request,
    Post data is defined as follows:
    post_id
    title
    content 
    image
    role
    device_token to be associated with fcm
    */
    public function getPosts()
    {
      //return all posts
      return Post::all();
    }
   

    public function getPostById(Request $request)
    {
      //return post
      return Post::where("id",$request->post_id)->get();
    }

    public function addPost(Request $request)
    {
      //adds a post
      $extension = $request->file('image')->extension();
      $path = Storage::disk('spaces')->putFileAs('posts', $request->file('image'), time().'.'.$extension, 'public');

        Post::create([
           "title" => $request->title,
           "content" => $request->content,
           "post_status" => $request->post_status,
           "image" => $path
        ]);
        $user = Auth::user();
        if($user->role == "editor")
        return  $this->sendNotification($user);
        // response()->json(['notifiedd']);
     
    }
    public function deletePost(Request $request){
        $post = Post::find($request->post_id);
        $post->delete();
    }
 
    public function editPost(Request $request){
        Post::where('id', $request->post_id)
        ->update([
            "title" => $request->title,
            "content" => $request->content,
            "image" => $request->image
        ]);
    }

    public function sendNotification(User $user)
    {   $firebaseToken = $user->device_token;
        // return $firebaseToken;
        // $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        //   $firebaseToken = {"cdyl3Z1qoUWBqU0_63ktcv:APA91bEE5UsVzXMQGBpDM4TrvLsZZecxyHMMwMgpI1GEmFjlnmiPuEW0B_7s52eZEtPGNBQHhPlc5LGipIHxKeArbJ0SgC-cNz3FwyAoBQcqmFkkV3FtMoed3MzKnD76V7GveR_5D9EH"};
         $SERVER_API_KEY = 'AAAAsozdNQg:APA91bFqlqaNSWGO-RZfo3nINzaU2LG1bj3Z5GC8qbnJaYkhuj_x9evP2y4bica7N7RDUJvzratwPmGOhi2WLeaiLh0X8ZMKLVp6hRd2BAGzJ0gNZ696_NbaKsjcKehi7vjTr2fcSL53';
  
        
     
         $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $user->firstName,
                "body" => $user->lastName,  
            ]
        ];
       
        $dataString = json_encode($data);
    
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
     
        $ch = curl_init();
       
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
               
        $response = curl_exec($ch);
       
          dd($response);
    }


}
