<?php

namespace App\DataTables;

use App\Models\Location;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

use App\Traits\DataTableTrait;

class LocationDataTable extends DataTable
{
    use DataTableTrait;
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('status', function($query) {
                $status = 'warning';
                switch ($query->status) {
                    case 1:
                        $status = 'primary';
                        $status_label =  __('message.active');
                        break;
                    case 0:
                        $status = 'danger';
                        $status_label =  __('message.inactive');
                        break;
                    default:
                        $status_label = null;
                        break;
                }
                return '<span class="text-capitalize badge bg-'.$status.'">'.$status_label.'</span>';
            })
            ->editColumn('region_id' , function ( $service ) {
                return $service->region_id != null ? optional($service->region)->name : '';
            })

            ->filterColumn('region_id', function( $query, $keyword ){
                $query->whereHas('region', function ($q) use($keyword){
                    $q->where('name', 'like' , '%'.$keyword.'%');
                });
            })
            ->editColumn('created_at', function ($query) {
                return dateAgoFormate($query->created_at, true);
            })
            ->addIndexColumn()
            ->addColumn('action', 'location.action')
            ->order(function ($query) {
                if (request()->has('order')) {
                    $order = request()->order[0];
                    $column_index = $order['column'];

                    $column_name = 'created_at';
                    $direction = 'desc';
                    if( $column_index != 0) {
                        $column_name = request()->columns[$column_index]['data'];
                        $direction = $order['dir'];
                    }
    
                    $query->orderBy($column_name, $direction);
                }
            })
            ->rawColumns([ 'action', 'status' ]);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Location $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $model = Location::myLocation();
        return $this->applyScopes($model);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')
                ->searchable(false)
                ->title(__('message.srno'))
                ->orderable(false)
                ->width(60),
            Column::make('name')->title( __('message.name') ),
            Column::make('longitude')->title( __('message.longitude') ),
            Column::make('latitude')->title( __('message.latitude') ),

            Column::make('latitude_north')->title( __('message.latitude_north') ),
            Column::make('latitude_south')->title( __('message.latitude_south') ),
            Column::make('longitude_east')->title( __('message.longitude_east') ),
            Column::make('longitude_west')->title( __('message.longitude_west') ),
            Column::make('no_of_allow_drivers_in_save_zone')->title( __('message.no_of_allow_drivers_in_save_zone') ),
            Column::make('no_of_minuts_remove_queue_out_save_zone')->title( __('message.no_of_minuts_remove_queue_out_save_zone') ),

            Column::make('created_at')->title( __('message.created_at') ),
            Column::make('status')->title( __('message.status') ),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'location_' . date('YmdHis');
    }
}
