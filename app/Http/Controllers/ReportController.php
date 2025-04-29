<?php

namespace App\Http\Controllers;

use App\DataTables\DriverEarningDataTable;
use App\Models\RideRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\AdminReportExport;
use App\Exports\DriverReportExport;
use App\Exports\DriverEarningExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function adminEarning(Request $request)
    {
        $pageTitle = __('message.earning_report',['name' => __('message.admin')]);
        $auth_user = authSession();
        $params = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $driver_ids = User::where('user_type', 'driver')->pluck('id');
        $rider_ids = User::where('user_type', 'rider')->pluck('id');

        $ride_requests_rider = RideRequest::whereIn('rider_id', $rider_ids)->where('status', 'completed')->myRide()->orderBy('id', 'desc');
        $ride_requests_driver = RideRequest::whereIn('driver_id', $driver_ids)->where('status', 'completed')->myRide()->orderBy('id', 'desc');

        if (!empty($params['from_date'])) {
            $ride_requests_rider->whereDate('created_at', '>=', $params['from_date']);
            $ride_requests_driver->whereDate('created_at', '>=', $params['from_date']);
        }

        if (!empty($params['to_date'])) {
            $ride_requests_rider->whereDate('created_at', '<=', $params['to_date']);
            $ride_requests_driver->whereDate('created_at', '<=', $params['to_date']);
        }
        $params['datatable_botton_style'] = true;

        $data = $ride_requests_rider->get()->merge($ride_requests_driver->get());
        return view('report.adminreport', compact('pageTitle', 'auth_user','data','params'));
    }

    public function downloadAdminEarning(Request $request)
    {
        $startDate = $request->input('from_date');
        $endDate = $request->input('to_date');

        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');

        $export = new AdminReportExport($request, $startDate, $endDate);
        $filename = ($start && $endDate) ? "admin-earning-report_{$start}_to_{$end}.xlsx" : "admin-earning-report_{$start}.xlsx";

        return Excel::download($export, $filename);
    }

    public function downloadAdminEarningPdf(Request $request)
    {
        $export = new AdminReportExport($request);
        $collection = $export->collection();
        $mappedData = $collection->map([$export, 'map']);
        $headings = $export->headings('pdf');

        $totalAmountSum = getPriceFormat($export->getTotalAmountSum());
        $admin_commission = getPriceFormat($export->getAdminCommission());
        $driver_commission = getPriceFormat($export->getDriverCommission());

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $dateFilterText = '';
        $filenameDatePart = '';
        if ($fromDate && $toDate) {
            $fromDateFormatted = \Carbon\Carbon::parse($fromDate)->format('Y-m-d');
            $toDateFormatted = \Carbon\Carbon::parse($toDate)->format('Y-m-d');
            $dateFilterText = 'From Date: ' . $fromDateFormatted . ' To Date: ' . $toDateFormatted;
            $filenameDatePart = '_from_' . $fromDateFormatted . '_to_' . $toDateFormatted;
        } elseif ($fromDate) {
            $fromDateFormatted = \Carbon\Carbon::parse($fromDate)->format('Y-m-d');
            $dateFilterText = 'From Date: ' . $fromDateFormatted;
            $filenameDatePart = '_from_' . $fromDateFormatted;
        } elseif ($toDate) {
            $toDateFormatted = \Carbon\Carbon::parse($toDate)->format('Y-m-d');
            $dateFilterText = 'To Date: ' . $toDateFormatted;
            $filenameDatePart = '_to_' . $toDateFormatted;
        }

        $htmlContent = '<h1>Admin Earning Report</h1>';
        if ($dateFilterText) {
            $htmlContent .= '<p><strong>' . $dateFilterText . '</strong></p>';
        }

        $htmlContent .= '<style>
            body {
                font-family: "DejaVu Sans", sans-serif;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                border-bottom: 1px solid black;
            }
            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #bfbfbf;
            }
            .bold-text {
                 font-weight: bold;
            }
            .text-capitalize {
                text-transform: capitalize;
            }
            h1{
                text-align:center;
            }
            p{
                font-size:18px;
            }
            .note {
                margin-top: 20px;
                font-size: 15px;
                text-align: center;
                color: green;
            }  
        </style>';
        $htmlContent .= '<table>';
        $htmlContent .= '<thead><tr>';

        foreach ($headings as $heading) {
            $htmlContent .= '<th>' . $heading . '</th>';
        }
        $htmlContent .= '</tr></thead>';
        $htmlContent .= '<tbody>';

        foreach ($mappedData as $row) {
            $htmlContent .= '<tr>';
            foreach ($row as $cell) {
                if ($cell === 'Total' || $cell === $totalAmountSum || $cell === $admin_commission || $cell === $driver_commission) {
                    $htmlContent .= '<td class="bold-text">' . $cell . '</td>';
                } else {
                    $htmlContent .= '<td class="text-capitalize">' . $cell . '</td>';
                }
            }
            $htmlContent .= '</tr>';
        }

        $htmlContent .= '</tbody>';

        $htmlContent .= '</table>';
        $htmlContent .= '<div class="note">
                <p class="note">'.__('message.note_pdf_report').'</p>
                </div>';

        $pdf = Pdf::loadHTML($htmlContent)->setPaper('a4', 'landscape');

        // return $pdf->download('admin-earning-report.pdf');
        $filename = 'admin-earning-report' . $filenameDatePart . '.pdf';

        return $pdf->download($filename);
    }

    public function driverEarning(Request $request)
    {
        $pageTitle = __('message.earning_report',['name' => __('message.driver')] );
        $auth_user = authSession();
        $params = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];
        $driver_ids = User::where('user_type', 'driver')->pluck('id');
        $ride_requests_driver = RideRequest::whereIn('driver_id',$driver_ids)->where('status', 'completed')->myRide();

        if (!empty($params['from_date'])) {
            $ride_requests_driver->whereDate('created_at', '>=', $params['from_date']);
        }

        if (!empty($params['to_date'])) {
            $ride_requests_driver->whereDate('created_at', '<=', $params['to_date']);
        }

        $params['datatable_botton_style'] = true;
        $data = $ride_requests_driver->orderBy('created_at','desc')->get();
        return view('report.driver-earning-datatable', compact('pageTitle', 'auth_user','data','params'));
    }

    public function downloadDriverEarning(Request $request)
    {
        $startDate = request('from_date');
        $endDate = request('to_date');

        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');

        $export = new DriverEarningExport($request, $startDate, $endDate);
        $filename = ($start && $endDate) ? "driver-earning-report_{$start}_to_{$end}.xlsx" : "driver-earning-report_{$start}.xlsx";

        return Excel::download($export, $filename);
    }

    public function downloadDriverEarningPdf(Request $request)
    {
        $export = new DriverEarningExport($request);
        $collection = $export->collection();
        $mappedData = $collection->map([$export, 'map']);
        $headings = $export->headings('pdf');

        $total_amount   = $export->getDriverSumData('total_amount');
        $driver_commission  = $export->getDriverSumData('driver_commission');
        $admin_commission    = $export->getDriverSumData('admin_commission');

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $dateFilterText = '';
        $filenameDatePart = '';
        if ($fromDate && $toDate) {
            $fromDateFormatted = \Carbon\Carbon::parse($fromDate)->format('Y-m-d');
            $toDateFormatted = \Carbon\Carbon::parse($toDate)->format('Y-m-d');
            $dateFilterText = 'From Date: ' . $fromDateFormatted . ' To Date: ' . $toDateFormatted;
            $filenameDatePart = '_from_' . $fromDateFormatted . '_to_' . $toDateFormatted;
        } elseif ($fromDate) {
            $fromDateFormatted = \Carbon\Carbon::parse($fromDate)->format('Y-m-d');
            $dateFilterText = 'From Date: ' . $fromDateFormatted;
            $filenameDatePart = '_from_' . $fromDateFormatted;
        } elseif ($toDate) {
            $toDateFormatted = \Carbon\Carbon::parse($toDate)->format('Y-m-d');
            $dateFilterText = 'To Date: ' . $toDateFormatted;
            $filenameDatePart = '_to_' . $toDateFormatted;
        }

        $htmlContent = '<h1>Driver Earning Report</h1>';
        if ($dateFilterText) {
            $htmlContent .= '<p><strong>' . $dateFilterText . '</strong></p>';
        }

        $htmlContent .= '<style>
            body {
                font-family: "DejaVu Sans", sans-serif;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                border-bottom: 1px solid black;
            }
            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #bfbfbf;
            }
            .bold-text {
                 font-weight: bold;
            }
            .text-capitalize {
                text-transform: capitalize;
            }
            h1{
                text-align:center;
            }
            p{
                font-size:18px;
            }
            .note {
                margin-top: 20px;
                font-size: 15px;
                text-align: center;
                color: green;
            }  
        </style>';
        $htmlContent .= '<table>';
        $htmlContent .= '<thead><tr>';

        foreach ($headings as $heading) {
            $htmlContent .= '<th>' . $heading . '</th>';
        }
        $htmlContent .= '</tr></thead>';
        $htmlContent .= '<tbody>';

        foreach ($mappedData as $row) {
            $htmlContent .= '<tr>';
            foreach ($row as $cell) {
                
                if (in_array($cell,['Total',$total_amount,$driver_commission,$admin_commission])) {
                    $htmlContent .= '<td class="bold-text">' . $cell . '</td>';
                } else {
                    $htmlContent .= '<td class="text-capitalize">' . $cell . '</td>';
                }
            }
            $htmlContent .= '</tr>';
        }

        $htmlContent .= '</tbody>';

        $htmlContent .= '</table>';
        $htmlContent .= '<div class="note">
                <p class="note">'.__('message.note_pdf_report').'</p>
                </div>';

        $pdf = Pdf::loadHTML($htmlContent)->setPaper('a4', 'landscape');
        $filename = 'driver-earning-report' . $filenameDatePart . '.pdf';

        return $pdf->download($filename);
    }

    public function driverReport(Request $request)
    {
        $pageTitle = __('message.report',['name' => __('message.driver')] );
        $auth_user = authSession();
        $params = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        $driver_ids = isset($_GET['driver']) ? $_GET['driver'] : null;
        $ride_requests_driver = RideRequest::where('driver_id', $driver_ids)->where('status', 'completed')->myRide();
        $user_data = User::find($driver_ids);

        if (!empty($params['from_date'])) {
            $ride_requests_driver->whereDate('created_at', '>=', $params['from_date']);
        }

        if (!empty($params['to_date'])) {
            $ride_requests_driver->whereDate('created_at', '<=', $params['to_date']);
        }

        $params['datatable_botton_style'] = true;
        $data = $ride_requests_driver->get();
        return view('report.driver-report', compact('pageTitle', 'auth_user','data','params','user_data'));
    }

    public function downloadDriverReport(Request $request)
    {
        $startDate = $request->input('from_date');
        $endDate = $request->input('to_date');

        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');
        $driver_ids = isset($_GET['driver']) ? $_GET['driver'] : null;
        $export = new DriverReportExport($request, $startDate, $endDate,$driver_ids);
        $filename = ($start && $endDate) ? "driver-earning-report_{$start}_to_{$end}.xlsx" : "driver-earning-report_{$start}.xlsx";

        return Excel::download($export, $filename);
    }

    public function downloadDriverReportPdf(Request $request)
    {
        $export = new DriverReportExport($request);
        $collection = $export->collection();
        $mappedData = $collection->map([$export, 'map']);
        $headings = $export->headings('pdf');

        $total_amount = $export->getDriverSumData('total_amount');
        $admin_commission   = $export->getDriverSumData('admin_commission');
        $driver_commission  = $export->getDriverSumData('driver_commission');

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $dateFilterText = '';
        $filenameDatePart = '';
        if ($fromDate && $toDate) {
            $fromDateFormatted = \Carbon\Carbon::parse($fromDate)->format('Y-m-d');
            $toDateFormatted = \Carbon\Carbon::parse($toDate)->format('Y-m-d');
            $dateFilterText = 'From Date: ' . $fromDateFormatted . ' To Date: ' . $toDateFormatted;
            $filenameDatePart = '_from_' . $fromDateFormatted . '_to_' . $toDateFormatted;
        } elseif ($fromDate) {
            $fromDateFormatted = \Carbon\Carbon::parse($fromDate)->format('Y-m-d');
            $dateFilterText = 'From Date: ' . $fromDateFormatted;
            $filenameDatePart = '_from_' . $fromDateFormatted;
        } elseif ($toDate) {
            $toDateFormatted = \Carbon\Carbon::parse($toDate)->format('Y-m-d');
            $dateFilterText = 'To Date: ' . $toDateFormatted;
            $filenameDatePart = '_to_' . $toDateFormatted;
        }

        $htmlContent = '<h1>Driver Earning Report</h1>';
        if ($dateFilterText) {
            $htmlContent .= '<p><strong>' . $dateFilterText . '</strong></p>';
        }

        $htmlContent .= '<style>
            body {
                font-family: "DejaVu Sans", sans-serif;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                border-bottom: 1px solid black;
            }
            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #bfbfbf;
            }
            .bold-text {
                 font-weight: bold;
            }
            .text-capitalize {
                text-transform: capitalize;
            }
            h1{
                text-align:center;
            }
            p{
                font-size:18px;
            }
            .note {
                margin-top: 20px;
                font-size: 15px;
                text-align: center;
                color: green;
            }  
        </style>';
        $htmlContent .= '<table>';
        $htmlContent .= '<thead><tr>';

        foreach ($headings as $heading) {
            $htmlContent .= '<th>' . $heading . '</th>';
        }
        $htmlContent .= '</tr></thead>';
        $htmlContent .= '<tbody>';

        foreach ($mappedData as $row) {
            $htmlContent .= '<tr>';
            foreach ($row as $cell) {
                
                if (in_array($cell,['Total',$total_amount,$admin_commission,$driver_commission])) {
                    $htmlContent .= '<td class="bold-text">' . $cell . '</td>';
                } else {
                    $htmlContent .= '<td class="text-capitalize">' . $cell . '</td>';
                }
            }
            $htmlContent .= '</tr>';
        }

        $htmlContent .= '</tbody>';

        $htmlContent .= '</table>';
        $htmlContent .= '<div class="note">
                <p class="note">'.__('message.note_pdf_report').'</p>
                </div>';

        $pdf = Pdf::loadHTML($htmlContent)->setPaper('a4', 'landscape');
        $filename = 'driver-earning-report' . $filenameDatePart . '.pdf';

        return $pdf->download($filename);
    }
}
