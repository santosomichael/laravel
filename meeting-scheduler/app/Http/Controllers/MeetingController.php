<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Meeting;
use App\User;
use Carbon\Carbon;
use JWTAuth;

class MeetingController extends Controller
{
    // public function __construct() {
    //     $this->midleware('jwt.auth', ['only' => [
    //         'update', 'store', 'destroy'
    //     ]]);
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $meetings = Meeting::with('users')->get();
        foreach ($meetings as $meeting) {
            $meeting->view_meeting = [
                'href'=> 'api/v1/meeting/'.$meeting->id,
                'method' => 'GET'
            ];
        }

        $response = [
            'msg' => 'List of all Meeting',
            'meeting' => [
                $meetings
            ]
        ];
        return response()->json($response, 200);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->validate($request, [
        //     'title' => 'required',
        //     'description' => 'required',            
        //     'user_id' => 'required'
        // ]);
       

        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');        
        $user_id = $user->id;

        $meeting = new Meeting ([
            'time' => Carbon::createFromFormat('YmdHie', $time),
            'title' => $title,
            'description' => $description
        ]);

        if($meeting->save()) {
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/'.$meeting->id,
                'method' => 'GET'
            ];

            $message = [
                'msg' => 'Meeting created',
                'meeting' => $meeting
            ];
        }
        
        return response()->json($message, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();
        $meeting->view_meeting = [
            'href' => 'api/v1/meeting',
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'meeting_information',
            'meeting' => $meeting
        ];
        return response()->json($response, 200);
    }

    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = Meeting::with('users')->findOrFail($id);

        if(!$meeting->users()->where('users.id',$user_id)-> first()) {
            $failResponse = [
                'msg'=>'user not registered for meeting, update not successful'
            ];
            return response()->json($failResponse, 401);
        }
        $meeting->time = Carbon::createFromFormat('YmdHie', $time);
        $meeting->title = $title;
        $meeting->description = $description;
        if (!$meeting->update()) {
            $failResponse = [
                'msg' => 'Error during updating'
            ];
            return response()->json($failResponse, 404);
        }

        $meeting ->view_meeting = [
            'href' => 'api/v1/meeting' . $meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting updated',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);

        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        if(!$meeting->users()->where('users.id',$user->id)-> first()) {
            $failResponse = [
                'msg'=>'user not registered for meeting, delete not successful'
            ];
            return response()->json($failResponse, 401);
        }

       
        $users = $meeting->users;
        $meeting->users()->detach();
        if (!$meeting->delete()) {
            foreach ($users as $user) {
                $meeting->users()->attach($user);
            }
            return response()->json(['msg' => 'deletion failed'], 404);
        } 
        
        $response = [
            'msg' => 'Meeting Deleted',
            'create' => [
                'href' => 'api/v1/meeting'.$meeting->id,
                'method' => 'POST',
                'params' => 'title:'.$meeting->title.', description:'.$meeting->description.', time:'.$meeting->time
            ]
        ];

        return response()->json($response, 201);
    }
}
