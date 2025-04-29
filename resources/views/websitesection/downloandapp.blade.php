<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                    <div class="card card-block card-stretch">
                        <div class="card-body p-0">
                            <div class="d-flex justify-content-between align-items-center p-3">
                                <h5 class="font-weight-bold">{{ $pageTitle ?? __('message.list') }}</h5>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($dowloandapp ?? null, ['method' => 'POST', 'route' => ['settingUpdate'], 'id' => 'main_form']) }}
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::hidden('key[]', 'download_title') !!}
                                    {!! Form::hidden('type[]', 'download_app') !!}
                                    {{ Form::label('download_title', __('message.title') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::text('value[]',$download_title ?? null, ['placeholder' => __('message.title'), 'class' => 'form-control']) }}
                                </div>

                                <div class="form-group col-md-12">
                                    {!! Form::hidden('key[]', 'download_subtitle') !!}
                                    {!! Form::hidden('type[]', 'download_app') !!}
                                    {{ Form::label('download_subtitle', __('message.subtitle') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::text('value[]', $download_subtitle ?? null, ['placeholder' => __('message.subtitle'), 'class' => 'form-control']) }}
                                </div>
                            </div>
                        {{ Form::close() }}
                        {{ Form::open(['route' => ['image-save'], 'method' => 'POST', 'files' => true, 'id' => 'image_form','enctype' => 'multipart/form-data']) }}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    {!! Form::hidden('key', 'download_app_logo') !!}
                                    {!! Form::hidden('type', 'download_app') !!}
                                    {{ Form::label('download_app_logo', __('message.image') , ['class' => 'form-control-label'],false) }}
                                    <div class="custom-file">
                                        {{ Form::file('download_app_logo', ['class' => 'custom-file-input', 'lang' => 'en', 'accept' => 'image/*']) }}
                                        <label class="custom-file-label"
                                            for="download_app_logo">{{ __('message.choose_file', ['file' => __('message.image')]) }}</label>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-2">
                                    @if($dowloandapp->isNotEmpty())
                                    @foreach($dowloandapp as $image)
                                        @if(getMediaFileExit($image, 'download_app_logo'))
                                            <div class="form-group  position-relative">
                                                <img src="{{ getSingleMedia($image, 'download_app_logo') }}" class="avatar-100 mt-1 img-fluid">
                                            </div>
                                        @endif
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12 mt-1 mb-4">
                                {{ Form::submit(__('message.save'), ['class' => 'btn btn-md btn-primary   float-md-right','id' => 'saveButton']) }}
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('bottom_script')
        <script>
            $(document).ready(function() {
                $('#saveButton').click(function(e) {
                    e.preventDefault();
                    $.post($('#main_form').attr('action'), $('#main_form').serialize(), function(response) {
                        $('#image_form').submit();
                    });
                });
            });
        </script>
    @endsection
</x-master-layout>
