<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    /*
     * 设置访问权限，未登录用户不可访问编辑页，已登录用户不可以访问登录界面
     */
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['create', 'store', 'index', 'show', 'confirmEmail']
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

        $this->sendEmailConfirmationTo($user);

        session()->flash('success', '激活邮件已发送，请注意查收！');

        return redirect('/');
    }

    /*
     * 发送邮件
     */
    protected function sendEmailConfirmationTo($user)
    {
        $view    = 'emails.confirm';
        $data    = compact('user');
        $from    = 'zhangyujia1819@gmail.com';
        $name    = 'Rainy';
        $to      = $user->email;
        $subject = '感谢您注册SecondLaravel，请确认您的邮件！';

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    /*
     * 激活账号
     */
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activation_token = null;
        $user->activated        = true;

        $user->save();

        Auth::login($user);

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
