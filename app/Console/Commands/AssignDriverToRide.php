<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RideRequest;
use App\Models\User;
use App\Traits\RideRequestTrait;
use App\Models\Setting;
use App\Notifications\CommonNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AssignDriverToRide extends Command
{
    use RideRequestTrait;

    protected $signature = 'ride:assign-drivers-for-regular-rides';
    protected $description = 'Assign drivers to regular ride requests';

    public function handle()
    {
        $this->info('Starting the process to assign drivers to regular rides.');

        $radius = Setting::where('type', 'DISTANCE')->where('key', 'DISTANCE_RADIUS')->value('value') ?? 50;
        $min_amount = SettingData('wallet', 'min_amount_to_get_ride') ?? null;

        $requests = RideRequest::where('is_schedule', 0)
            ->where('status', 'new_ride_requested')
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->get();

        foreach ($requests as $request) {
            $this->processRideRequest($request, $radius, $min_amount);
        }

        $this->info('Command executed successfully');
    }

    protected function processRideRequest($ride_request, $radius, $min_amount)
    {
        $unit = $ride_request->distance_unit ?? 'km';
        $unit_value = convertUnitvalue($unit);
        $latitude = $ride_request->start_latitude;
        $longitude = $ride_request->start_longitude;
        $cancelled_driver_ids = $ride_request->cancelled_driver_ids ?? [];

        $nearby_driver = User::selectRaw("id, user_type, player_id, latitude, longitude, ( $unit_value * acos( cos( radians($latitude) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) AS distance")
            ->where('user_type', 'driver')
            ->where('status', 'active')
            ->where('is_online', 1)
            ->where('is_available', 1)
            ->where('service_id', $ride_request->service_id)
            ->whereNotIn('id', $cancelled_driver_ids)
            ->having('distance', '<=', $radius)
            ->when($min_amount, function ($query) use ($min_amount) {
                $query->whereHas('userWallet', function ($q) use ($min_amount) {
                    $q->where('total_amount', '>=', $min_amount);
                });
            })
            ->orderBy('distance', 'asc')
            ->first();

        if ($nearby_driver) {
            Log::info($ride_request->id .' Found a nearby driver with ID: ' . $nearby_driver->id);
            $ride_request->update([
                'riderequest_in_driver_id' => $nearby_driver->id,
                'riderequest_in_datetime' => Carbon::now()->format('Y-m-d H:i:s'),
                'cancelled_driver_ids' => $cancelled_driver_ids,
            ]);

            Log::info('Updated ride request with driver ID: ' . $nearby_driver->id);

            try {
                $firebaseData = app('firebase.firestore')->database()->collection('rides')->document('ride_' . $ride_request->id);
                if ($firebaseData) {
                    $rideData = [
                        'driver_id' => $nearby_driver->id,
                        'on_rider_stream_api_call' => 1,
                        'on_stream_api_call' => 1,
                        'ride_id' => $ride_request->id,
                        'rider_id' => $ride_request->rider_id,
                        'status' => $ride_request->status,
                        'payment_status' => '',
                        'payment_type' => '',
                        'tips' => 0,
                    ];
                    $firebaseData->set($rideData);

                    $notification_data = [
                        'id' => $ride_request->id,
                        'type' => 'new_ride_requested',
                        'data' => [
                            'rider_id' => $ride_request->rider_id,
                            'rider_name' => optional($ride_request->rider)->display_name ?? '',
                        ],
                        'message' => __('message.new_ride_requested'),
                        'subject' => __('message.ride.new_ride_requested'),
                    ];

                    $nearby_driver->notify(new CommonNotification($notification_data['type'], $notification_data));
                    Log::info('Sent notification to driver ID: ' . $nearby_driver->id);

                } else {
                    Log::info('Document does not exist: ride_' . $ride_request->id);
                }
            } catch (\Exception $e) {
                Log::error('Error updating Firestore or sending notification: ' . $e->getMessage());
            }
        } else {
            Log::info('No nearby driver found for ride request ID: ' . $ride_request->id);
        }
    }
}
