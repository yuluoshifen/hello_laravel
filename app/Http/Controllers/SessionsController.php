<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    /*
     * 设置访问权限，已登录用户不可以访问登录界面
     */
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => 'create'
        ]);
    }

    /*
     * 显示登录
     */
    public function create()
    {
        return view('sessions.create');
    }

    /*
     * 执行注册
     */
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required|min:6'
        ]);

        if (Auth::attempt($credentials, $request->has('remember')))
        {
            if (Auth::user()->activated)
            {
                session()->flash('success', '登陆成功！');

                return redirect()->intended(route('users.show', [Auth::user()]));
            }
            else
            {
                Auth::logout();

                session()->flash('warning', '账号未激活！');

                return redirect('/');
            }
        }
        else
        {
            session()->flash('danger', '用户名或密码错误！');

            return redirect()->back()->withInput(); //withInput验证失败跳转时，显示原有input框的数据
        }
    }

    /*
     * 退出登录
     */
    public function destroy()
    {
        Auth::logout();

        session()->flash('success', '退出成功！');

        return redirect('login');   //redirect第一个参数：path
    }
}
