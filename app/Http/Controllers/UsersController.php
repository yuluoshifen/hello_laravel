<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /*
     * 设置访问权限，未登录用户不可访问编辑页，已登录用户不可以访问登录界面
     */
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['create', 'store', 'index', 'show']
        ]);

        $this->middleware('guest', [
            'only' => 'create'
        ]);
    }

    /*
     * 显示注册
     */
    public function create()
    {
        return view('users.create');
    }

    /*
     * 执行注册
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|max:50',
            'email'    => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password)
        ]);

        //注册成功自动登录
        Auth::login($user);

        session()->flash('success', '注册成功！');

        return redirect()->route('users.show', [$user]);
    }

    /*
     * 用户列表
     */
    public function index()
    {
        $users = User::paginate(10);

        return view('users.index', compact('users'));
    }

    /*
     * 显示用户信息
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /*
     * 编辑用户信息
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    /*
     * 执行编辑用户信息
     */
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);

        $data         = [];
        $data['name'] = $request->name;
        if ($request->has('password'))
        {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        session()->flash('success', '编辑成功！');

        return redirect()->route('users.show', compact('user'));
    }

    /*
     * 删除用户(仅管理员)
     */
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '删除成功！');

        return back();
    }
}
