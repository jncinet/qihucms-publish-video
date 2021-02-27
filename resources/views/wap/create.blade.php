@extends('layouts.wap')

@section('title', '发布作品')
@section('header_title', '发布作品')

@section('styles')
    <style type="text/css">
        .select-video-input {
            height: 3.125rem;
        }

        .videoSelectWrap {
            transform: translate(0, 0);
            transition: all 1s
        }

        .showUploading {
            transform: translate(0, -95px);
        }

        .video-uploading {
            height: 0;
            overflow: hidden;
            transition: all 1s
        }

        .showUploading > div.video-uploading {
            height: 64px;
            transform: translate(0, 0);
        }

        .showVideoView {
            transform: translate(0, -159px);
        }

        .video-view {
            height: 0;
            overflow: hidden;
            transition: all 1s
        }

        .showVideoView > div.video-view {
            height: 266px;
        }
    </style>
@endsection

@section('content')
    @if(cache('config_publish_video_auth') != 'all' && Auth::user()['vip_rank'] < 1)
        <div class="p-3">
            <div class="alert alert-warning" role="alert">
                对不起，只有VIP会员才可以发布视频；
                <hr>
                <a href="{{ route('vip') }}" class="btn btn-block btn-warning">成为VIP会员</a>
            </div>
        </div>
    @else
        <div id="video-wrap" class="position-relative videoSelectWrap">
            <div class="vw-100 bg-secondary d-flex py-3 text-center text-white-50">
                <div class="w-50 position-relative select-video-border">
                    <input class="w-100 file-input-transparent select-video-input" type="file"
                           id="ps" accept="video/*" capture="user">
                    <i class="iconfont icon-jingcailuzhi mr-2 font-size-26"></i>
                    <div>拍摄视频</div>
                </div>
                <div class="w-50 position-relative">
                    <input class="w-100 file-input-transparent select-video-input" type="file"
                           id="sc" accept="video/*">
                    <i class="iconfont icon-shangchuan mr-2 font-size-26"></i>
                    <div>本地上传</div>
                </div>
            </div>

            <div class="vw-100 video-uploading bg-success">
                <div class="d-flex py-3 justify-content-center align-items-center">
                    <div class="spinner-border text-white-50" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div class="pl-2 text-white-50">正在上传...</div>
                </div>
            </div>
            <div class="position-relative video-view bg-secondary">
                <div class="btn btn-sm btn-primary qh-btn-rounded px-3 position-absolute"
                     style="z-index:1;right: 1rem;top:1rem;" onclick="reUpload()">重新选择
                </div>
                <video id="videoView"
                       src=""
                       class="d-block bg-black" webkit-playsinline="true" x-webkit-airplay="allow"
                       playsinline
                       x5-video-player-type="h5-page" x5-video-orientation="portrait"
                       x5-video-player-fullscreen="true"
                       width="100%" height="266" autoplay controls>
                    暂不支持视频预览
                </video>
            </div>

            <div class="weui-cells weui-cells_form mt-0">
                <form id="adForm">
                    <input type="hidden" name="src" id="videoSrc">
                    <input type="hidden" name="status" value="3">
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <textarea name="desc" class="weui-textarea" placeholder="视频介绍" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <input name="tags" class="weui-input" type="text" placeholder="视频标签，多个请用英文逗号隔开">
                        </div>
                    </div>

                    <div class="weui-cell">
                        <div class="weui-cell__bd"><label class="weui-label">位置</label></div>
                        <div class="weui-cell__bd">
                            <input name="city" style="text-align: right;color:#999999" class="weui-input" type="text"
                                   id="city" placeholder="我的位置">
                        </div>
                    </div>

                    <div class="weui-cell weui-cell_switch">
                        <div class="weui-cell__bd">VIP观看</div>
                        <div class="weui-cell__ft">
                            <input name="price" value="1" class="weui-switch" type="checkbox">
                        </div>
                    </div>

                </form>
            </div>
            <div class="p-3">
                <button onclick="submitForm()" type="button" class="btn btn-block btn-primary qh-btn-rounded">发布
                </button>
            </div>
        </div>
    @endif
    @include('components.wap.placeholder_nav')
    @include('components.wap.nav', ['index' => 'publish'])
@endsection

@push('scripts')

    @if(session('result'))
        $.toast("{{ session('result') }}", "text");
    @endif
    @if(config('filesystems.default') == 'oss')
        <script src="//gosspublic.alicdn.com/aliyun-oss-sdk-6.4.0.min.js"></script>
    @endif
    <script src="{{ asset('js/upload.js') }}"></script>
    <script>
        function submitForm() {
            var param = new FormData(document.getElementById('adForm'));
            var config = {headers: {"Content-Type": "multipart/form-data"}};
            $.showLoading('请稍后...');
            axios.post('{{route('publish-video.store')}}', param, config)
                .then(function (res) {
                    $.hideLoading();
                    $.toast('发布成功', function () {
                        setTimeout(function () {
                            window.location.reload();
                        }, 1500);
                    });
                })
                .catch(function (err) {
                    $.hideLoading();
                    if (err.response) {
                        var errs = err.response.data.errors;
                        var arrErrs = Array();
                        for (var index in errs) {
                            arrErrs.push(errs[index].join('，'));
                        }
                        $.alert(arrErrs.join('<br/>'));
                        return
                    }
                    console.log(err.response)
                });
        }

        function reUpload() {
            $('#video-wrap').removeClass('showUploading').removeClass('showVideoView');
            $('#videoView').attr('src', '');
            $('#ps,#sc').val('');
        }

        $('#ps,#sc').on('change', function () {
            $('#video-wrap').addClass('showUploading');
            var files = $(this).get(0).files;
            @switch(config('filesystems.default'))
            @case('qiniu')
            $.qn({
                path: 'video',
                file: files[0],
                tokenUrl: "{{ route('upload.qiniu.token') }}",
                success: function (res) {
                    console.log(res);
                    setTimeout(function () {
                        $('#video-wrap').addClass('showVideoView');
                    }, 2000);
                    $('#videoView').attr('src', "{{ config('filesystems.disks.qiniu.domain') }}/" + res.key);
                    $('#videoSrc').val(res.key);
                },
                fail: function (err) {
                    $.toast("上传失败", "cancel");
                }
            });
            @break
            @case('oss')
            $.oss({
                path: 'video',
                file: files[0],
                accessKeyId: "{{ config('filesystems.disks.oss.access_key') }}",
                accessKeySecret: "{{ config('filesystems.disks.oss.secret_key') }}",
                bucket: "{{ config('filesystems.disks.oss.bucket') }}",
                endpoint: "{{ config('filesystems.disks.oss.endpoint') }}",
                cname: {{ config('filesystems.disks.oss.isCName') }},
                success: function (res) {
                    console.log(res);
                    setTimeout(function () {
                        $('#video-wrap').addClass('showVideoView');
                    }, 2000);
                    $('#videoView').attr('src', res.url);
                    $('#videoSrc').val(res.name);
                },
                fail: function (err) {
                    $.toast("上传失败", "cancel");
                }
            });
            @break
            @default

            $.bd({
                path: 'video',
                input: 'video',
                file: files[0],
                uploadUrl: "{{ route('upload') }}",
                success: function (res) {
                    setTimeout(function () {
                        $('#video-wrap').addClass('showVideoView');
                    }, 2000);
                    $('#videoView').attr('src', res.data.url);
                    $('#videoSrc').val(res.data.name);
                    console.log(res);
                },
                fail: function (err) {
                    $.toast("上传失败", "cancel");
                    reUpload();
                }
            });
            @endswitch
        });

        function receiveAppLocation(data) {
            //城市：data.city,
            //省份：data.province
            const city = data.province + ' ' + data.city;
            sessionStorage.setItem('userCity', city);
            $('#city').val(city);
            $("#city").cityPicker({showDistrict: false});
        }

        document.addEventListener("DOMContentLoaded", function () {
            const userCity = sessionStorage.getItem('userCity');
            if (userCity !== null && userCity.length > 0) {
                $('#city').val(userCity);
                return;
            }
            wx.config( {!! $weChatJsSdk !!} );
            var weChatGps = undefined;
            wx.ready(function () {
                wx.getLocation({
                    type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                    success: function (res) {
                        axios.get("{{ route('publish-video.location') }}", {
                            params: {
                                type: 'gps',
                                latitude: res.latitude,
                                longitude: res.longitude
                            }
                        })
                            .then(function (response) {
                                if (response.data.status === 0) {
                                    const city = response.data.result.address_component.province + ' ' + response.data.result.address_component.city;
                                    sessionStorage.setItem('userCity', city);
                                    $('#city').val(city);
                                    weChatGps = true;
                                    $("#city").cityPicker({showDistrict: false});
                                }
                            })
                            .catch(function (error) {
                            });
                    }
                });
            });

            if (weChatGps !== true) {
                if (window.Qihu) {
                    window.Qihu.location('receiveAppLocation');
                } else {
                    axios.get("{{ route('publish-video.location') }}", {
                        params: {
                            type: 'ip'
                        }
                    })
                        .then(function (res) {
                            if (res.data.status === 0) {
                                let city = res.data.result.ad_info.province;
                                if (res.data.result.ad_info.adcode > 0) {
                                    city += ' ' + res.data.result.ad_info.city;
                                }
                                if (city.length > 1) {
                                    sessionStorage.setItem('userCity', city);
                                    $('#city').val(city);
                                    $("#city").cityPicker({showDistrict: false});
                                }
                            }
                        })
                        .catch(function (err) {
                            console.log(err.response);
                        });
                }
            }
        });
    </script>
@endpush