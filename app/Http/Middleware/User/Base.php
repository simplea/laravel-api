<?php

namespace App\Http\Middleware\User;

use Closure;
use JWTAuth;

use Tymon\JWTAuth\Exceptions\JWTException;  
use Tymon\JWTAuth\Exceptions\TokenExpiredException;  
use Tymon\JWTAuth\Exceptions\TokenInvalidException; 

class Base
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            $iss   = JWTAuth::parseToken()->getClaim('iss')?? '';
            $match = preg_match('/company/', $iss);
            $user  = JWTAuth::parseToken()->authenticate();
            if (!$user || $match == 1 || !$user->hid) {  //获取到用户数据，并赋值给$user
                return response()->json([
                    'errcode' => 1004,  
                    'errmsg' => '无法获取用户', 
                    'data' => ''
                ]);
            }
            $request->attributes->add(['userId'=>$user->id]);//添加参数
            return $next($request);
        }catch(TokenExpiredException $e){
            return response()->json([  
                'errcode' => 1003,  
                'errmsg' => 'token 过期' , //token已过期
                'data' => ''
            ]);
        }catch(TokenInvalidException $e){
            return response()->json([  
                'errcode' => 1002,  
                'errmsg' => 'token 无效',  //token无效
                'data' => ''
            ]);
        }catch(JWTException $e){
            return response()->json([ 
                'errcode' => 1001,  
                'errmsg' => '缺少token' , //token为空
                'data' => ''
            ]);
        }
    }
}
