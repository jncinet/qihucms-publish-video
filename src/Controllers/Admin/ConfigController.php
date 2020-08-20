<?php

namespace Qihucms\PublishVideo\Controllers\Admin;

use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('发布设置')
            ->body(new ConfigForm());
    }
}
