<?php

namespace Qihucms\PublishVideo\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShortVideoRequest;
use App\Models\ShortVideo;
use App\Services\WechatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Qihucms\TencentLbs\TencentLbs;

class CreateController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $weChatJsSdk = (new WechatService())->jssdk();
        return view('publishVideo::wap.create', compact('weChatJsSdk'));
    }

    /**
     * @param ShortVideoRequest $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(ShortVideoRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::id();
        if (empty($data['city'])) {
            $lbs = new TencentLbs();
            $adInfo = $lbs->ipLocation($request->ip());
            $data['city'] = isset($adInfo['result']['ad_info']) ? $adInfo['result']['ad_info']['province'] . ' ' . $adInfo['result']['ad_info']['city'] : '未知位置';
        }
        // 转码压缩
        if (empty($data['src'])) {
            return $this->errorJson('视频不存在');
        } else {
            // 转码压缩
            if (Cache::get('config_avsmart_status')) {
                $saveName = $this->saveName('short_video', 'mp4');
                $run_result = app('videoFFMpeg')->avThumb($data['src'], $saveName);
                if ($run_result['return_var'] == 0) {
                    $data['src'] = $saveName;
                }
            }
            // 截图
            $coverPath = $this->saveName('video_cover', 'jpg');
            $run_result = app('videoFFMpeg')->vFrame($data['src'], $coverPath);
            if ($run_result['return_var'] == 0) {
                $data['cover'] = $coverPath;
            }
            // 视频信息
            $data['exif'] = app('videoFFMpeg')->avInfo($data['src']);
        }
        $data['status'] = Cache::get('config_check_short_video', 0);
        return ShortVideo::create($data);
    }

    protected function saveName($path = 'video', $suffix = 'mp4')
    {
        $path .= DIRECTORY_SEPARATOR . Auth::id();
        Storage::disk('public')->makeDirectory($path);
        return $path . DIRECTORY_SEPARATOR . Str::random(26) . '.' . $suffix;
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function location(Request $request)
    {
        $type = $request->get('type', 'ip');
        $lbs = new TencentLbs();
        if ('ip' == $type) {
            $data = $lbs->ipLocation($request->ip());
        } elseif ('gps' == $type) {
            $data = $lbs->gpsLocation($request->get('latitude'), $request->get('longitude'));
        } elseif ('app_lbs' == $type) {
            $data = [
                'result' => [
                    'ad_info' => [
                        'province' => $request->get('province'),
                        'city' => $request->get('city'),
                        'district' => $request->get('district')
                    ]
                ]
            ];
        } else {
            return response()->json(['msg' => '参数错误'], 422);
        }
        if ($request->get('is_session')) {
            session([
                'ad_info' => [
                    'province' => $data['result']['ad_info']['province'],
                    'city' => $data['result']['ad_info']['city'],
                    'district' => $data['result']['ad_info']['district']
                ]
            ]);
        }
        return $data;
    }
}