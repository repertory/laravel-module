<?php

namespace LaravelModule\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 操作成功返回
     *
     * @param array|string $data
     * @return \Illuminate\Http\JsonResponse
     */
    final protected function success($data = [])
    {
        if (is_string($data)) {
            $data = [
                'message' => $data,
            ];
        }
        return response()->json(array_merge([
            'status' => 'success',
            'message' => '操作成功',
        ], $data));
    }

    /**
     * 操作失败返回
     *
     * @param array|string $data
     * @return \Illuminate\Http\JsonResponse
     */
    final protected function error($data = [])
    {
        if (is_string($data)) {
            $data = [
                'message' => $data,
            ];
        }
        return response()->json(array_merge([
            'status' => 'error',
            'message' => '操作失败',
            'url' => app('request')->url(),
        ], $data));
    }
}
