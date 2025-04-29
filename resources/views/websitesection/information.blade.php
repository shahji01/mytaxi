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
                        {{ Form::model($information ?? null, ['method' => 'POST', 'route' => ['settingUpdate'], 'id' => 'main_form']) }}
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::hidden('key[]', 'app_name') !!}
                                    {!! Form::hidden('type[]', 'app_info') !!}
                                    {{ Form::label('app_name', __('message.app_name') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::text('value[]', $app_name ?? null, ['placeholder' => __('message.app_name'), 'class' => 'form-control']) }}
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::hidden('key[]', 'image_title') !!}
                                    {!! Form::hidden('type[]', 'app_info') !!}
                                    {{ Form::label('image_title', __('message.title') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::text('value[]',$image_title ?? null, ['placeholder' => __('message.title'), 'class' => 'form-control']) }}
                                </div>
                            </div>
                        {{ Form::close() }}
                        <div class="row">
                            <div class="form-group col-md-12 col-lg-6">
                                {{ Form::open(['route' => ['image-save'], 'method' => 'POST', 'files' => true, 'id' => 'image_form', 'enctype' => 'multipart/form-data']) }}
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            {!! Form::hidden('key', 'background_image') !!}
                                            {!! Form::hidden('type', 'app_info') !!}
                                            {{ Form::label('background_image', __('message.background_image'), ['class' => 'form-control-label'], false) }}
                                            <div class="custom-file">
                                                {{ Form::file('background_image', ['class' => 'custom-file-input', 'lang' => 'en', 'accept' => 'image/*']) }}
                                                <label class="custom-file-label" for="background_image">{{ __('message.choose_file', ['file' => __('message.image')]) }}</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            @if($information->isNotEmpty())
                                                @foreach($information as $image)
                                                    @if(getMediaFileExit($image, 'background_image'))
                                                        <div class="form-group position-relative mt-3">
                                                            <img src="{{ getSingleMedia($image, 'background_image') }}" class="avatar-100 img-fluid">
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                {{ Form::close() }}
                            </div>
                            <div class="form-group col-md-12 col-lg-6">
                                {{ Form::open(['route' => ['image-save'], 'method' => 'POST', 'files' => true, 'id' => 'image_form_road', 'enctype' => 'multipart/form-data']) }}
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            {!! Form::hidden('key', 'logo_image') !!}
                                            {!! Form::hidden('type', 'app_info') !!}
                                            {{ Form::label('logo_image', __('message.logo_image'), ['class' => 'form-control-label'], false) }}
                                            <div class="custom-file">
                                                {{ Form::file('logo_image', ['class' => 'custom-file-input', 'lang' => 'en', 'accept' => 'image/*']) }}
                                                <label class="custom-file-label" for="logo_image">{{ __('message.choose_file', ['file' => __('message.image')]) }}</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            @if($information->isNotEmpty())
                                                @foreach($information as $image)
                                                    @if(getMediaFileExit($image, 'logo_image'))
                                                        <div class="form-group position-relative mt-3">
                                                            <img src="{{ getSingleMedia($image, 'logo_image') }}" class="avatar-100 img-fluid">
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <hr>
                        <div class="col-md-12 mt-1 mb-4">
                            <button class="btn btn-md btn-primary float-md-right" id="saveButton">{{ __('message.save') }}</button>
                        </div>
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
                    $.post($('#main_form').attr('action'), $('#main_form').serialize(), function(response) {
                        $('#image_form_road').submit();
                    });
                });
            });
        </script>
    @endsection
</x-master-layout>
