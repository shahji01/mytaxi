<x-master-layout :assets="$assets ?? []">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch card-height">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title mb-0">{{ $pageTitle ?? ''}}</h4>
                        </div>
                        
                        <?php echo $button; ?>
                    </div>
                    <div class="card-body">
                        <div class="card-header-toolbar">
                            {{ Form::open(['method' => 'GET']) }}
                                <div class="row p-2">
                                    <div class="form-group col-md-3">
                                        {{ Form::label('last_actived_at', __('message.status') . '<span data-toggle="tooltip" data-html="true" data-placement="top" title="Active user: Who last activated date in 1 day<br>Engaged user: Who last activated date in 2-15 days<br>Inactive user: Who last activated date in more than 15 days">(info)</span>', ['class' => 'form-control-label'], false) }}
                                        {{ Form::select('last_actived_at', [ '' => __('message.all'), 'active_user' => __('message.active_user'), 'engaged_user' => __('message.engaged_user'), 'inactive_user' => __('message.inactive_user') ], $last_actived_at ?? [], [ 'class' => 'form-control select2js']) }}
                                    </div>      
                                    <div class="form-group col-sm-0 mt-3">
                                        <button class="btn btn-primary text-white mt-3 pt-2 pb-2">{{ __('message.submit') }}</button>
                                    </div>
                                    <div class="form-group col-sm-2 mt-3">
                                        @if(isset($reset_file_button))
                                        {!! $reset_file_button !!}
                                        @endif
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                        {{ $dataTable->table(['class' => 'table  w-100'],false) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('bottom_script')
       {{ $dataTable->scripts() }}
    @endsection
</x-master-layout>
