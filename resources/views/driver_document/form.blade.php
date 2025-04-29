<x-master-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
            {!! Form::model($data, ['route' => ['driverdocument.update', $id], 'method' => 'patch', 'enctype' => 'multipart/form-data' ]) !!}
        @else
            {!! Form::open(['route' => ['driverdocument.store'], 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="new-user-info">
                            <div class="row">
                                @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                                    <div class="form-group col-md-4">
                                        {{ Form::label('driver_id', __('message.select_name',[ 'select' => __('message.driver') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                        {{ Form::select('driver_id', isset($id) ? [ optional($data->driver)->id => optional($data->driver)->display_name] : [], old('driver_id'), [
                                            'class' => 'select2js form-group driver',
                                            'required',
                                            'data-placeholder' => __('message.select_name',[ 'select' => __('message.driver') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'driver', 'status' => 'pending' ]),
                                        ]) }}
                                    </div>
                                @endif

                                @if(auth()->user()->hasRole('fleet'))
                                    <div class="form-group col-md-4">
                                        {{ Form::label('driver_id', __('message.select_name',[ 'select' => __('message.driver') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                        {{ Form::select('driver_id', isset($id) ? [ optional($data->driver)->id => optional($data->driver)->display_name] : [], old('driver_id'), [
                                            'class' => 'select2js form-group driver',
                                            'required',
                                            'data-placeholder' => __('message.select_name',[ 'select' => __('message.driver') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'driver', 'fleet_id' => auth()->user()->id, 'status' => 'pending'  ]),
                                        ]) }}
                                    </div>
                                @endif

                                @php
                                    $is_required = isset($id) && optional($data->document)->is_required == 1 ? '*' : '';
                                    $has_expiry_date = isset($id) && optional($data->document)->has_expiry_date == 1 ? 1 : '';
                                @endphp
                                <div class="form-group col-md-4">
                                    {{ Form::label('document_id', __('message.select_name',[ 'select' => __('message.document') ]).' <span class="text-danger">* </span>', ['class' => 'form-control-label' ], false) }}

                                    {{ Form::select('document_id',
                                        isset($id) ? [optional($data->document)->id => optional($data->document)->name . ($is_required ? " *" : "")] : [],
                                        $data->document_id ?? old('document_id'),
                                        [
                                            'class' => 'select2js form-group document_id',
                                            'id' => 'document_id',
                                            'required',
                                            'data-placeholder' => __('message.select_name',[ 'select' => __('message.document') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'document']),
                                        ]
                                    ) }}
                                </div>

                                <div class="form-group col-md-4" id="dynamic-fields-container">
                                    {{-- Pehle se selected document ka field yahan render hoga --}}
                                    @if(isset($data->document_fields_value))
                                        <script>
                                            var existingDocumentData = @json($data->document);
                                            var existingFieldValue = @json($data->document_fields_value);
                                        </script>
                                    @endif

                                </div>


                                <div class="form-group col-md-4">
                                    <label class="form-control-label" for="expire_date">{{ __('message.expire_date') }} <span class="text-danger" id="has_expiry_date">{{ $has_expiry_date == 1 ? '*' : ''  }}</span> </label>
                                    {{ Form::text('expire_date', old('expire_date'),[ 'class' =>'form-control min-datepicker', 'placeholder' => __('message.expire_date'), 'required' => $has_expiry_date == 1 ? 'required' : null ]) }}
                                </div>

                                @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                                    <div class="form-group col-md-4">
                                        {{ Form::label('is_verified', __('message.is_verify').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                        {{ Form::select('is_verified',[ '0' => __('message.pending'), '1' => __('message.approved'), '2' => __('message.rejected') ], old('is_verified'), [ 'id' => 'is_verified', 'class' => 'form-control select2js', 'required']) }}
                                    </div>
                                @endif

                                
                            </div>
                            <hr>
                            {{ Form::submit( __('message.save'), ['class'=>'btn btn-md btn-primary float-right']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    @section('bottom_script')
    <script>
     (function($) {
    "use strict";
    $(document).ready(function() {

        function loadDocumentFields(documentData, existingValue = null) {

            if (!documentData) return;

            // **Ensure the container is visible**
            $('#dynamic-fields-container').show().html('');

            // **Handle Required & Expiry Date Logic**
            if (documentData.is_required == 1) {
                $('#document_required').text('*');
                $('#driver_document').attr('required', true);
            } else {
                $('#document_required').text('');
                $('#driver_document').removeAttr('required');
            }

            if (documentData.has_expiry_date == 1) {
                $('#has_expiry_date').text('*');
                $('#expire_date').attr('required', true);
            } else {
                $('#has_expiry_date').text('');
                $('#expire_date').removeAttr('required');
            }
            // **Generate Dynamic Fields**

            if (documentData.field) {

                var fieldName = documentData.field.name;

                // Agar existingValue hai to use karo warna documentData ka value lo
                var fieldValue = existingValue !== null ? existingValue : (documentData.document_fields_value ?? "");

                var fieldHtml = '';

                switch (fieldName) {

                    case 'Single-Line Text':
                        fieldHtml = `<label class="form-control-label">${fieldName} <span class="text-danger">*</span></label>
                                     <input type="text" name="document_fields_value" class="form-control" value="${fieldValue}">
`;
                        break;

                    case 'Multiple-Line Text':
                        fieldHtml = `<label class="form-control-label">${fieldName} <span class="text-danger">*</span></label>
                                     <textarea name="document_fields_value" class="form-control" rows="3">${fieldValue}</textarea>`;
                        break;

                    case 'Checkbox':
                        var checked = fieldValue == "1" ? "checked" : "";
                        fieldHtml = `<div class="form-check">
                                        <input type="checkbox" name="document_fields_value" class="form-check-input" value="1" ${checked}>
                                        <label class="form-check-label">${fieldName}</label>
                                     </div>`;
                        break;

                    case 'WholeNumber':
                        fieldHtml = `<label class="form-control-label">${fieldName} <span class="text-danger">*</span></label>
                                     <input type="number" name="document_fields_value" class="form-control" value="${fieldValue}">`;
                        break;

                    case 'Date':
                        fieldHtml = `<label class="form-control-label">${fieldName} <span class="text-danger">*</span></label>
                                     <input type="date" name="document_fields_value" class="form-control" value="${fieldValue}">`;
                        break;

                    case 'Currency':
                        fieldHtml = `<label class="form-control-label">${fieldName} <span class="text-danger">*</span></label>
                                     <input type="text" name="document_fields_value" class="form-control currency-input" value="${fieldValue}">`;
                        break;

                        case 'File':
                            fieldHtml = `<label class="form-control-label">${fieldName} <span class="text-danger">*</span></label>
                                        <input type="file" name="document_fields_value" class="form-control-file" ${existingValue ? '' : ''}>`;

                            if (existingValue) {

                                var fileExtension = existingValue.split('.').pop().toLowerCase();

                                // Image Preview
                                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension)) {
                                    const assetBaseUrl = "{{ asset('storage/') }}";
                                    fieldHtml += `<img src="${assetBaseUrl +'/' + existingValue}" alt="File Preview" class="img-thumbnail mt-2" style="max-width: 200px;">`;
                                }
                                // PDF Preview
                                else if (fileExtension === 'pdf') {
                                    const assetBaseUrl = "{{ asset('storage/') }}";
                                    fieldHtml += `<iframe src="${assetBaseUrl +'/' + existingValue}" width="100%" height="300px"></iframe>`;
                                }
                            }
                            break;

                    default:
                        fieldHtml = `<label class="form-control-label">${fieldName}</label>
                                     <input type="text" name="document_fields_value" class="form-control" value="${fieldValue}">`;
                        break;
                }

                $('#dynamic-fields-container').append(fieldHtml);
            }
        }

        // **Dropdown Change Event**
        $(document).on('change', '#document_id', function() {

            var data = $('#document_id').select2('data')[0];
            loadDocumentFields(data);
        });

        // **Load Fields on Edit Page with Existing Data**
        if (typeof existingDocumentData !== "undefined") {
           loadDocumentFields(existingDocumentData, existingFieldValue);
        }
    });
})(jQuery);

    </script>
    @endsection
</x-master-layout>
