<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\UserAdded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // validation
        $request->validate([
            'search' => 'string',
            'page' => 'integer',
            'sortBy' => 'nullable|in:name,email,created_at'
        ]);

        $search = $request->get('search');
        $page = $request->get('page') ?? 1 ;
        $sortBy = $request->get('sortBy') ?? 'created_at';
        $perpage = 2;
        $offset = $perpage * ($page - 1);

        $query = User::query();
        $query->select('id','email','name','created_at')
            ->selectRaw('(select count(1) from orders where orders.user_id = users.id) as orders_count')
            ->orderBy($sortBy);

        if ($search) {
            $query->whereAny([
                'name',
                'email'
            ], 'like', '%'.$search.'%');
        }
            
        $result = $query->offset($offset)->limit($perpage)->get();
        return response()->json([
            'page' => $page,
            'users' => $result
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'bail|required|string|email|unique:users',
            'password' => 'bail|required|string|min:8',
            'name' => 'bail|required|string|min:3|max:50'
        ],['email.unique' => 'Email has been registered']);

        $user = new User();

        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->name = $request->input('name');
        $user->save();

        foreach(['admin@mailinator.com',$request->input('email')] as $recipient) {
            Mail::to($recipient)->send(new UserAdded($user));   
        }

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'created_at' => $user->created_at
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
