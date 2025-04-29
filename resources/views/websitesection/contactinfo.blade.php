<x-master-layout :assets="$assets ?? []">
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
                        {{ Form::model($contactinfo ?? null, ['method' => 'POST', 'route' => ['settingUpdate'], 'id' => 'main_form']) }}
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::hidden('key[]', 'about_title') !!}
                                    {!! Form::hidden('type[]', 'contact_us') !!}
                                    {{ Form::label('title', __('message.about_title') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::text('value[]', $title ?? null, ['placeholder' => __('message.about_title'), 'class' => 'form-control']) }}
                                </div>
                            </div>
                        {{ Form::close() }}
                        {{ Form::open(['route' => ['image-save'], 'method' => 'POST', 'files' => true, 'id' => 'image_form','enctype' => 'multipart/form-data']) }}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::hidden('key', 'contact_us_image') !!}
                                    {!! Form::hidden('type', 'contact_us') !!}
                                    {{ Form::label('contact_us_image', __('message.image'), ['class' => 'form-control-label']) }}
                                    <div class="custom-file">
                                        {{ Form::file('contact_us_image', ['class' => 'custom-file-input',  'lang' => 'en', 'accept' => 'image/*']) }}
                                        <label class="custom-file-label"
                                            for="contact_us_image">{{ __('message.choose_file', ['file' => __('message.image')]) }}</label>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-2">
                                    @if($contactinfo->isNotEmpty())
                                    @foreach($contactinfo as $image)
                                        @if(getMediaFileExit($image, 'contact_us_image'))
                                            <div class="form-group  position-relative">
                                                <img src="{{ getSingleMedia($image, 'contact_us_image') }}"  class="avatar-100 mt-1 img-fluid">
                                            </div>
                                        @endif
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        {{ Form::close() }}
                        {{ Form::model($information ?? null, ['method' => 'POST', 'route' => ['AppSetting'], 'id' => 'main_form_setting']) }}
                            {!! Form::hidden('id',$information->id) !!}
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {{ Form::label('contact_email', __('message.support_email') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::text('contact_email', old('contact_email'), ['placeholder' => __('message.support_email'), 'class' => 'form-control']) }}
                                </div>

                                <div class="form-group col-md-12">
                                    {{ Form::label('contact_number',__('message.contact_number') .' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    {{ Form::text('support_number', old('support_number'),[ 'placeholder' => __('message.contact_number'), 'class' => 'form-control', 'id' => 'phone','required']) }}
                                </div>
                                <div class="form-group col-md-12">
                                    {{ Form::label('facebook_url', __('message.facebook_url'),['class' => 'form-control-label']) }}
                                    {{ Form::text('facebook_url', old('facebook_url'), ['placeholder' => __('message.facebook_url'), 'class' => 'form-control']) }}
                                </div>
                                <div class="form-group col-md-12">
                                    {{ Form::label('twitter_url', __('message.twitter_url'),['class' => 'form-control-label']) }}
                                    {{ Form::text('twitter_url', old('twitter_url'), ['placeholder' => __('message.twitter_url'), 'class' => 'form-control']) }}
                                </div>
                                <div class="form-group col-md-12">
                                    {{ Form::label('linkedin_url', __('message.linkedin_url'),['class' => 'form-control-label']) }}
                                    {{ Form::text('linkedin_url', old('linkedin_url'), ['placeholder' => __('message.linkedin_url'), 'class' => 'form-control']) }}
                                </div>
                                <div class="form-group col-md-12">
                                    {{ Form::label('instagram_url', __('message.instagram_url'),['class' => 'form-control-label']) }}
                                    {{ Form::text('instagram_url', old('instagram_url'), ['placeholder' => __('message.linkedin_url'), 'class' => 'form-control']) }}
                                </div>
                            </div>
                        {{ Form::close() }}
                        {{ Form::model($contactinfo ?? null, ['method' => 'POST', 'route' => ['settingUpdate'], 'id' => 'main_form_url']) }}
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::hidden('key[]', 'play_store_link') !!}
                                    {!! Form::hidden('type[]', 'app_content') !!}
                                    {{ Form::label('play_store_link', __('message.play_store_url') ,['class' => 'form-control-label']) }}
                                    {{ Form::text('value[]', $play_store ?? null, ['placeholder' => __('message.play_store_url'), 'class' => 'form-control']) }}
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::hidden('key[]', 'app_store_link') !!}
                                    {!! Form::hidden('type[]', 'app_content') !!}
                                    {{ Form::label('app_store_link', __('message.app_store_url'),['class' => 'form-control-label']) }}
                                    {{ Form::text('value[]', $app_store ?? null, ['placeholder' => __('message.app_store_url'), 'class' => 'form-control']) }}
                                </div>
                            </div>
                        {{ Form::close() }}
                        <hr>
                        <div class="col-md-12 mt-4 mb-4">
                            {{ Form::submit(__('message.save'), ['class' => 'btn btn-md btn-primary   float-md-right','id' => 'saveButton']) }}
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
                        $('#main_form_setting').submit();
                    });
                    $.post($('#main_form').attr('action'), $('#main_form').serialize(), function(response) {
                        $('#main_form_url').submit();
                    });
                });
            });
        </script>
    @endsection
</x-master-layout>
