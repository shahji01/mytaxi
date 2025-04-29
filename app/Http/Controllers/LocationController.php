<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\DataTables\LocationDataTable;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LocationDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title',['form' => __('message.location')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        $button = $auth_user->can('location add') ? '<a href="'.route('location.create').'" class="float-right btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> '.__('message.add_form_title',['form' => __('message.location')]).'</a>' : '';
        return $dataTable->render('global.datatable', compact('pageTitle','button','auth_user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = __('message.add_form_title',[ 'form' => __('message.location')]);
        
        return view('location.form', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['added_by'] = auth()->user()->id;

        // Get the existing latitude, longitude, and additional kilometers
        $latitude = $request['latitude'];
        $longitude = $request['longitude'];
        $additionalKilometer = $request['additional_kilometer'];
        $no_of_allow_drivers_in_save_zone = $request['no_of_allow_drivers_in_save_zone'];
        $no_of_minuts_remove_queue_out_save_zone = $request['no_of_minuts_remove_queue_out_save_zone'];

        // 1 degree of latitude is approximately 111 kilometers
        // 1 degree of longitude is approximately 85.39 km at the equator (it varies with latitude)

        // Calculate the change in latitude and longitude
        $latitudeChange = $additionalKilometer / 111;  // 1 degree of latitude = 111 km
        $longitudeChange = $additionalKilometer / (85.39 * cos(deg2rad($latitude)));  // 1 degree of longitude = 85.39 km at the equator, adjusted for latitude

        // Calculate the new coordinates for all four directions (north, south, east, west)
        $northLatitude = $latitude + $latitudeChange;
        $southLatitude = $latitude - $latitudeChange;
        $eastLongitude = $longitude + $longitudeChange;
        $westLongitude = $longitude - $longitudeChange;

        // Store the new coordinates for each direction
        $location = Location::create([
            'name' => $request->name,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'latitude_north' => $northLatitude,
            'latitude_south' => $southLatitude,
            'longitude_east' => $eastLongitude,
            'longitude_west' => $westLongitude,
            'additional_kilometer' => $additionalKilometer,
            'no_of_allow_drivers_in_save_zone' => $no_of_allow_drivers_in_save_zone,
            'no_of_minuts_remove_queue_out_save_zone' => $no_of_minuts_remove_queue_out_save_zone,
            'added_by' => $request['added_by'],
            'status' => $request->status
        ]);

        // Return success message
        $message = __('message.save_form', ['form' => __('message.location')]);

        if (request()->is('api/*')) {
            return json_message_response($message);
        }

        return redirect()->route('location.index')->withSuccess($message);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = __('message.update_form_title',[ 'form' => __('message.location')]);
        $data = Location::findOrFail($id);
        
        return view('location.form', compact('data', 'pageTitle', 'id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $request['added_by'] = auth()->user()->id;
        // Location data...
        $location->fill($request->all())->update();

        $message = __('message.update_form',['form' => __('message.location')]);

        if(request()->is('api/*')){
            return json_message_response( $message );
        }

        if(auth()->check()){
            return redirect()->route('location.index')->withSuccess($message);
        }
        return redirect()->back()->withSuccess($message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(env('APP_DEMO')){
            $message = __('message.demo_permission_denied');
            if(request()->ajax()) {
                return response()->json(['status' => true, 'message' => $message ]);
            }
            return redirect()->route('location.index')->withErrors($message);
        }
        $location = Location::find($id);
        $status = 'errors';
        $message = __('message.not_found_entry', ['name' => __('message.location')]);

        if($location != '') {
            $location->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.location')]);
        }
        
        if(request()->is('api/*')){
            return json_message_response( $message );
        }

        if(request()->ajax()) {
            return response()->json(['status' => true, 'message' => $message ]);
        }

        return redirect()->back()->with($status,$message);
    }
}
