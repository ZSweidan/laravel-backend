<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{  
//  /**
//      * Get a validator for an incoming registration request.
//      *
//      * @param  array  $data
//      * @return \Illuminate\Contracts\Validation\Validator
//      */
//     public function validator(Request $data)
//     { // return "Hello";
//         return Validator::make($data, [
//             'firstName' => ['required', 'string', 'max:255'],
//             'lastName' => ['required', 'string', 'max:255'],
//             'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
//             'password' => ['required', 'string', 'min:8', 'confirmed'],

//         ]);
//     }

//     /**
//      * Create a new user instance after a valid registration.
//      *
//      * @param  array  $data
//      * @return \App\Models\User
//      */
//     protected function create(array $data)
//     {
//         return User::create([
//             'firstName' => $data['firstName'],
//             'secondName' => $data['secondName'],
//             'email' => $data['email'],
//             'password' => Hash::make($data['password']),
//         ]);
//     }
    /**
     * Create user
     *
     * @param  [string] firstName
     * @param  [string] lastName
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        // $request->validate([
            // 'firstName' => 'required|string',
            // 'email' => 'required|string|email|unique:users',
            // 'password' => 'required|string|confirmed'
        // ]);
        $user = new User([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);
        $user->save();
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
      
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
      
        return response()->json([
            'role' => $user->role,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json(Auth::user());
        // return "hello";
    }


      /** 
     * Write code on Method
     *
     * @return response()
     */
    public function saveToken(Request $request)
    {
        auth()->user()->update(['device_token'=>$request->token]);
        return response()->json(['token saved successfully.']);
    }
  
   
    public function sendNotification(Request $request)
    {
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        //   $firebaseToken = {"cdyl3Z1qoUWBqU0_63ktcv:APA91bEE5UsVzXMQGBpDM4TrvLsZZecxyHMMwMgpI1GEmFjlnmiPuEW0B_7s52eZEtPGNBQHhPlc5LGipIHxKeArbJ0SgC-cNz3FwyAoBQcqmFkkV3FtMoed3MzKnD76V7GveR_5D9EH"};
         $SERVER_API_KEY = 'AAAAsozdNQg:APA91bFqlqaNSWGO-RZfo3nINzaU2LG1bj3Z5GC8qbnJaYkhuj_x9evP2y4bica7N7RDUJvzratwPmGOhi2WLeaiLh0X8ZMKLVp6hRd2BAGzJ0gNZ696_NbaKsjcKehi7vjTr2fcSL53';
  
         $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,  
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
    //     // $data = [
    //     //     "registration_ids" => $firebaseToken,
    //     //     "notification" => [
    //     //         "title" => $request->title,
    //     //         "body" => $request->body,  
    //     //     ]
    //     // ];
    //     $data = [
    //         "registration_ids" => $firebaseToken,
    //         "notification" => [
    //             // {
    //             //     "title":$request->input('title'),
    //             //     "body":$request->input('body')
    //             // }  
    //                 "title"=>$request->input('title'),
    //                 "body"=>$request->input('body')
    //         ]
    //     ];
    //     $dataString = json_encode($data);
    
    //     $headers = [
    //         'Authorization: key=' . $SERVER_API_KEY,
    //         'Content-Type: application/json',
    //     ];
    
    //     $ch = curl_init();
      
    //     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);              
    //     $response = curl_exec($ch);
    // echo $response;
    //     dd($data);
    }
}