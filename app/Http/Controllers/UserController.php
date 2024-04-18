<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::leftJoin('roles', 'users.role_id', '=', 'roles.id')
                     ->select('users.*', 'roles.name as role_name')
                     ->get();

        return view('users.index', ['users' => $users]);
    }
}
