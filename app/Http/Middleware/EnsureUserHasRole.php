<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * 檢查使用者是否登入且擁有權限.
     * 使用路徑：->middleware('role:admin,hr')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! $request->user()->hasRole(...$roles)) {
            abort(403, '您沒有權限使用這個頁面，請聯繫系統管理員。');
        }

        return $next($request);
    }
}
