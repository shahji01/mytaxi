<div class="card-body">
{{ Form::open(['method' => 'GET', 'route' => 'adminEarningReport','id' => 'filter-form' ]) }}
    <div class="row justify-content-end align-items-end">
        <div class="form-group col-auto">
            {{ Form::label('from_date',__('message.from').'<span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
            {{ Form::date('from_date',$params['from_date'] ?? request('from_date'),[ 'placeholder' => __('message.date'),'class' =>'form-control min-datepickerall', 'id' => 'from_date_main']) }}
        </div>
        <div class="form-group col-auto">
            {{ Form::label('to_date',__('message.to').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
            {{ Form::date('to_date', $params['to_date'] ?? request('to_date'),[ 'placeholder' => __('message.date'), 'class' =>'form-control min-datepickerall', 'id' => 'to_date_main']) }}
        </div>
        <div class="form-group col-sm-0">
            <button type="submit" class="btn btn-md btn-primary text-white  clearListPropertynumber">{{ __('message.apply_filter') }}</button>
            <a href="{{ route('adminEarningReport') }}" class="btn btn-md btn-light text-dark">
                <i class="ri-repeat-line" style="font-size:12px"></i> {{ __('message.reset_filter') }}
            </a>
            <div class="dropdown d-inline">
                <button class="btn btn-success btn-md text-center dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ __('message.export') }}
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="{{ route('download-admin-earning',request()->all()) }}">
                        <i class="fas fa-file-csv"></i> {{__('message.excel')}}
                    </a>
                    <a class="dropdown-item" href="{{ route('download-adminearningpdf',request()->all()) }}">
                        <i class="fas fa-file-pdf"></i> {{__('message.pdf')}}
                    </a>
                </div>
            </div>
        </div>
    </div>
{{ Form::close() }}
</div>