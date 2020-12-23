<?php

namespace Qihucms\PublishVideo\Controllers\Admin;

use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Qihucms\EditEnv\EditEnv;

class ConfigForm extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = '发布设置';

    public function handle(Request $request)
    {
        $data = $request->all();
        $env = new EditEnv();
        $env->setEnv($data);
        admin_success('保存成功');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->number('ff_video_width', '视频宽度')->help('转码统一的视频宽度，');
        $this->number('ff_video_height', '视频高度')->help('转码统一的视频高度');
        $this->number('ff_video_duration', '视频时长限制')
            ->help('限制视频发布时长，超出部份会被删除，为0时不限制，单位：秒，如果要动态修改，可以通过类的setInputDuration(15)方法修改');
        $this->text('ff_video_compress', '压缩参数')->help('留空默认为：-c:v libx264 -b:v 1500k -preset superfast');
        $this->number('ff_video_thread', '线程数');
    }

    public function data()
    {
        return [
            'ff_video_width' => config('qihu.ff_video_width', 544),
            'ff_video_height' => config('qihu.ff_video_height', 960),
            'ff_video_duration' => config('qihu.ff_video_duration', 0),
            'ff_video_compress' => config('qihu.ff_video_compress'),
            'ff_video_thread' => config('qihu.ff_video_thread', 0),
        ];
    }
}
