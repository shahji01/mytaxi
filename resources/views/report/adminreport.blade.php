<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch card-height">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title mb-0">{{ $pageTitle ?? ''}}</h4>
                        </div>
                        @include('report.adminfilter')
                    </div>
                    <div class="card-body">
                        <div class="card-header-toolbar">

                        </div>
                        <table id="basic-table" class="table mb-1 border-none  text-center" role="grid">
                            <thead>
                                <tr>
                                    <th scope='col'>{{ __('message.ride_request_id') }}</th>
                                    <th scope='col'>{{ __('message.title_name',['title' => __('message.rider')]) }}</th>
                                    <th scope='col'>{{ __('message.title_name',['title' => __('message.driver')]) }}</th>
                                    <th scope='col'>{{ __('message.pickup_date_time') }}</th>
                                    <th scope='col'>{{ __('message.drop_date_time') }}</th>
                                    <th scope='col'>{{ __('message.total_amount') }}</th>
                                    <th scope='col' class="text-center">{{ __('message.admin_commission') }}</th>
                                    <th scope='col' class="text-center">{{ __('message.driver_commission') }}</th>
                                    <th scope='col'>{{ __('message.created_at') }}</th>
                                </tr>
                            </thead>
                            @if(count($data) > 0)
                                <tbody>
                                    @foreach ($data as $values)
                                        @php
                                            $completed_ride_history = $values->rideRequestHistory()->where('history_type','completed')->first();
                                            $in_progress_ride_history = $values->rideRequestHistory()->where('history_type','in_progress')->first();
                                        @endphp
                                        <tr>
                                            <td><a href="{{ route('riderequest.show', $values->id) }}">{{ $values->id }}</a></td>
                                            <td><a href="{{ route('rider.show', $values->rider_id) }}">{{ optional($values->rider)->display_name ?? '-' }}</a></td>
                                            <td><a href="{{ route('driver.show', $values->driver_id) }}">{{ optional($values->driver)->display_name ?? '-' }}</a></td>
                                            <td>{{ dateAgoFormate($in_progress_ride_history->datetime,true) ?? '-' }}</td>
                                            <td>{{ dateAgoFormate($completed_ride_history->datetime,true) ?? '-' }}</td>
                                            <td>{{ getPriceFormat(optional($values->payment)->total_amount) ?? '-' }}</td>
                                            <td>{{ getPriceFormat(optional($values->payment)->admin_commission) ?? '-' }}</td>
                                            <td>{{ getPriceFormat(optional($values->payment)->driver_commission) ?? '-' }}</td>
                                            <td>{{ dateAgoFormate($values->created_at, true) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="font-weight-bold text-left">{{ __('message.total_amount') }}</td>
                                        <td class="text-center font-weight-bold">{{ getPriceFormat($data->sum('payment.total_amount')) }}</td>
                                        <td class="text-center font-weight-bold">{{ getPriceFormat($data->sum('payment.admin_commission')) }}</td>
                                        <td class="text-center font-weight-bold">{{ getPriceFormat($data->sum('payment.driver_commission')) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            @else
                                <tbody>
                                    <tr>
                                        <td colspan="9">{{ __('message.no_record_found') }}</td>
                                    </tr>
                                </tbody>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('bottom_script')
        <script>
            $("#basic-table").DataTable({
                "dom":  '<"row align-items-center"<"col-md-2"><"col-md-6" B><"col-md-4"f>><"table-responsive my-3" rt><"d-flex" <"flex-grow-1" l><"p-2" i><"mt-4" p>><"clear">',
                language: {
                    search: '',
                    searchPlaceholder: "{{ __('pagination.search') }}",
                    lengthMenu : "{{  __('pagination.show'). ' _MENU_ ' .__('pagination.entries')}}",
                    zeroRecords: "{{__('pagination.no_records_found')}}",
                    info: "{{__('pagination.showing') .' _START_ '.__('pagination.to') .' _END_ ' . __('pagination.of').' _TOTAL_ ' . __('pagination.entries')}}", 
                    infoFiltered: "{{__('pagination.filtered_from_total') . ' _MAX_ ' . __('pagination.entries')}}",
                    infoEmpty: "{{__('pagination.showing_entries')}}",
                    paginate: {
                        previous: "{{__('pagination.__previous')}}",
                        next: "{{__('pagination.__next')}}"
                    }
                },
                "order": [[0, "desc"]]
            });
            $(document).on('click', '.paginate_button', function() {
                pagination_btn_style_check = '{{ $params["datatable_botton_style"] }}';
                if ( pagination_btn_style_check ) {
                    $("<style>")
                        .prop("type", "text/css").html("\
                            .dataTables_paginate {\
                                display: block !important;\
                                opacity: 1 !important;\
                            }\
                        ")
                        .appendTo("head");
                }
            });
        </script>
    @endsection
    </x-master-layout>

