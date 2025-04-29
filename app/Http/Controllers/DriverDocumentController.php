<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DriverDocument;
use App\Models\Document;
use App\DataTables\DriverDocumentDataTable;
use App\Notifications\CommonNotification;
use App\Notifications\RideNotification;

class DriverDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DriverDocumentDataTable $dataTable)
    {
        $pageTitle = __('message.list_form_title',['form' => __('message.driver_document')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        $button = $auth_user->can('driverdocument add') ? '<a href="'.route('driverdocument.create').'" class="float-right btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> '.__('message.add_form_title',['form' => __('message.driver_document')]).'</a>' : '';
        return $dataTable->render('global.datatable', compact('pageTitle','button','auth_user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = __('message.add_form_title',[ 'form' => __('message.driver_document')]);
        $documents = Document::with('field:id,name')
            ->select('id', 'name', 'status', 'is_required', 'has_expiry_date', 'document_field_id')
            ->where('status', 1)
            ->get();

        return view('driver_document.form', compact('pageTitle', 'documents'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**public function store(Request $request)
    {
        $data = $request->all();
        $document = Document::with('field')->find($request->document_id);

        // Check if document has a field with name "File"
        if ($document && $document->field && $document->field->name == "File") {
            if ($request->hasFile('document_fields_value')) {
                $file = $request->file('document_fields_value');
                $filePath = $file->store('driver_document', 'public'); // Save file in storage/app/public/uploads/documents

                $data['document_fields_value'] = $filePath; // Store file path in DB
            } else {

                $data['document_fields_value'] = request('document_fields_value') != null ? request('document_fields_value') : null;
            }
        } else {

                $data['document_fields_value'] = request('document_fields_value') != null ? request('document_fields_value') : null;
        }

        $data['expire_date'] = request('expire_date')!= null ? date('Y-m-d',strtotime(request('expire_date'))) : null;
        $data['is_verified'] = request('is_verified') != null ? request('is_verified') : 0;
        $data['driver_id'] = request('driver_id') == null && auth()->user()->hasRole('driver') ? auth()->user()->id : request('driver_id');
        $driver_document = DriverDocument::create($data);

        uploadMediaFile($driver_document,$request->driver_document, 'driver_document');

        $message = __('message.save_form',['form' => __('message.driver_document')]);
        $is_verified = $driver_document->is_verified;
        if( in_array($is_verified, [ 0, 1, 2 ])  || $driver_document->driver->is_verified_driver == 0 ) {
            $is_verified_driver = (int) $driver_document->verifyDriverDocument($driver_document->driver->id);
            $driver_document->driver->update(['is_verified_driver' => $is_verified_driver ]);
        }

        if( in_array($is_verified, [ 1, 2 ]) )
        {
            $type = 'document_approved';
            $status = __('message.approved');
            if( $is_verified == 0 ) {
                $type = 'document_pending';
                $status = __('message.pending');
            }

            if( $is_verified == 2 ) {
                $type = 'document_rejected';
                $status = __('message.rejected');
            }
            $notification_data = [
                'id'   => $driver_document->driver->id,
                'is_verified_driver' => (int) $driver_document->driver->is_verified_driver,
                'type' => $type,
                'subject' => __('message.'.$type),
                'message' => __('message.approved_reject_form', [ 'form' => $driver_document->document->name, 'status' => $status ]),
            ];

            $driver_document->driver->notify(new CommonNotification($notification_data['type'], $notification_data));
        }

        if(request()->is('api/*')){
            return json_message_response( $message );
        }

        return redirect()->route('driverdocument.index')->withSuccess($message);
    }**/
	public function store(Request $request)
{
	
    $data = $request->all();
   
	$document = Document::with('field')->find($request->document_id);
	
    // Basic Validation
    $rules = [
        'driver_id' => 'required|exists:users,id',
        'document_id' => 'required|exists:documents,id',
        'expire_date' => 'nullable|date',
        'is_verified' => 'required|in:0,1,2',

    ];

    // Agar document ka field required hai toh 'document_fields_value' bhi required hoga
    if ($document && $document->field && $document->field->is_required == 0) {
        $rules['document_fields_value'] = 'required';
    }

    $request->validate($rules, [
    'driver_id.required' => 'Driver selection is required.',
    'document_id.required' => 'Document selection is required.',
    'is_verified.required' => 'Verification status is required.',
    'document_fields_value.required' => 'Please provide the required document.',
	]);

    // Check if document has a field with name "File"
    if ($document && $document->field && $document->field->name == "File") {
        if ($request->hasFile('document_fields_value')) {
            $file = $request->file('document_fields_value');
            $filePath = $file->store('driver_document', 'public'); // Save file in storage/app/public
            $data['document_fields_value'] = $filePath;
        } else {
            $data['document_fields_value'] = $request->document_fields_value ?? null;
        }
    } else {
        $data['document_fields_value'] = $request->document_fields_value ?? null;
    }

    $data['expire_date'] = $request->expire_date ? date('Y-m-d', strtotime($request->expire_date)) : null;
    $data['is_verified'] = $request->is_verified ?? 0;
    $data['driver_id'] = $request->driver_id == null && auth()->user()->hasRole('driver') ? auth()->user()->id : $request->driver_id;
    
    $driver_document = DriverDocument::create($data);

    // uploadMediaFile($driver_document, $request->driver_document, 'driver_document');

    $message = __('message.save_form', ['form' => __('message.driver_document')]);
    $is_verified = $driver_document->is_verified;

    if (in_array($is_verified, [0, 1, 2]) || $driver_document->driver->is_verified_driver == 0) {
        $is_verified_driver = (int) $driver_document->verifyDriverDocument($driver_document->driver->id);
        $driver_document->driver->update(['is_verified_driver' => $is_verified_driver]);
    }

    if (in_array($is_verified, [1, 2])) {
        $type = 'document_approved';
        $status = __('message.approved');

        if ($is_verified == 0) {
            $type = 'document_pending';
            $status = __('message.pending');
        }

        if ($is_verified == 2) {
            $type = 'document_rejected';
            $status = __('message.rejected');
        }

        $notification_data = [
            'id' => $driver_document->driver->id,
            'is_verified_driver' => (int) $driver_document->driver->is_verified_driver,
            'type' => $type,
            'subject' => __('message.' . $type),
            'message' => __('message.approved_reject_form', ['form' => $driver_document->document->name, 'status' => $status]),
        ];

        $driver_document->driver->notify(new CommonNotification($notification_data['type'], $notification_data));
    }

    if (request()->is('api/*')) {
        return json_message_response($message);
    }

    return redirect()->route('driverdocument.index')->withSuccess($message);
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pageTitle = __('message.add_form_title',[ 'form' => __('message.driver_document')]);
        $data = DriverDocument::findOrFail($id);

        return view('driver_document.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = __('message.update_form_title', [ 'form' => __('message.driver_document')]);
        $data = DriverDocument::with('document.field')->findOrFail($id);

        $documents = Document::with('field:id,name')
            ->select('id', 'name', 'status', 'is_required', 'has_expiry_date', 'document_field_id')
            ->where('status', 1)
            ->get();

        $selectedDocumentData = $data->document ? [
            'id' => $data->document->id,
            'document_field_id' => $data->document->document_field_id,
            'name' => $data->document->name,
            'is_required' => $data->document->is_required,
            'has_expiry_date' => $data->document->has_expiry_date,
            'document_fields_value' => $data->document_fields_value ?? '',
            'field' => [
                'name' => optional($data->document->field)->name
            ]
        ] : null;

        return view('driver_document.form', compact('data', 'pageTitle', 'id', 'selectedDocumentData', 'documents'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   /** public function update(Request $request, $id)
     {
        // dd($request->all());
         $driver_document = DriverDocument::find($id);

         if (!$driver_document) {
             $message = __('message.not_found_entry', ['name' => __('message.driver_document')]);
             if (request()->is('api/*')) {
                 return json_message_response($message);
             }
             return redirect()->route('driverdocument.index')->withErrors($message);
         }

         $old_is_verified = $driver_document->is_verified;

         // Retrieve the related document with its field relation.
            $document = Document::with('field')->find($request->document_id);

            // If the document field is "File", process file upload.
            if ($document && $document->field && $document->field->name == "File") {
                if ($request->hasFile('document_fields_value')) {
                    // Process file upload similar to the store method.
                    $file = $request->file('document_fields_value');
                    $filePath = $file->store('driver_document', 'public'); // Save in storage/app/public/driver_document
                    $driver_document->document_fields_value = $filePath; // Store file path in DB
                } else {
                    // If no file is provided, use the text value (if any).
                    $driver_document->document_fields_value = $request->input('document_fields_value', null);
                }
            } else {
                // For non-"File" types, simply update with the provided value.
                $driver_document->document_fields_value = $request->input('document_fields_value', null);
            }

            // Update the rest of the driver document fields.
            // Exclude document_fields_value since we've handled it already.
            $driver_document->fill($request->except('document_fields_value'));
            $driver_document->save();

         // Handle media file update if provided.
         if (isset($request->driver_document) && $request->driver_document != null) {
             $driver_document->clearMediaCollection('driver_document');
             $driver_document->addMediaFromRequest('driver_document')->toMediaCollection('driver_document');
         }

         $message = __('message.update_form', ['form' => __('message.driver_document')]);

         $is_verified = $driver_document->is_verified;
         if (in_array($is_verified, [0, 1, 2]) || $driver_document->driver->is_verified_driver == 0) {
             $is_verified_driver = (int)$driver_document->verifyDriverDocument($driver_document->driver->id);
             $driver_document->driver->update(['is_verified_driver' => $is_verified_driver]);
         }

         // Send notifications if the verification status has changed.
         if ($old_is_verified != $is_verified && in_array($is_verified, [0, 1, 2])) {
             $type = 'document_approved';
             $status = __('message.approved');
             if ($is_verified == 0) {
                 $type = 'document_pending';
                 $status = __('message.pending');
             }
             if ($is_verified == 2) {
                 $type = 'document_rejected';
                 $status = __('message.rejected');
             }
             $notification_data = [
                 'id' => $driver_document->driver->id,
                 'is_verified_driver' => (int)$driver_document->driver->is_verified_driver,
                 'type' => $type,
                 'subject' => __('message.' . $type),
                 'message' => __('message.approved_reject_form', ['form' => $driver_document->document->name, 'status' => $status]),
             ];

             $driver_document->driver->notify(new RideNotification($notification_data));
             $driver_document->driver->notify(new CommonNotification($notification_data['type'], $notification_data));
         }

         if (request()->is('api/*')) {
             return json_message_response($message);
         }

         return auth()->check()
             ? redirect()->route('driverdocument.index')->withSuccess($message)
             : redirect()->back()->withSuccess($message);
     }**/
public function update(Request $request, $id)
{
    $driver_document = DriverDocument::find($id);

    if (!$driver_document) {
        $message = __('message.not_found_entry', ['name' => __('message.driver_document')]);
        if (request()->is('api/*')) {
            return json_message_response($message);
        }
        return redirect()->route('driverdocument.index')->withErrors($message);
    }

    $old_is_verified = $driver_document->is_verified;

    // Retrieve the related document with its field relation.
    $document = Document::with('field')->find($request->document_id);

    // ✅ **Store Validation Added**
    $rules = [
        'document_id' => 'required|exists:documents,id',
        'document_fields_value' => 'nullable',
    ];

    if ($document && $document->field) {
        if ($document->field->name == "File") {
            $rules['document_fields_value'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048';
        } else {
            $rules['document_fields_value'] = 'nullable|string';
        }
    }

    //✅ **Validation for is_required**
//     if ($document) {
//         if ($document->is_required && !$request->has('document_fields_value')) {
//             return redirect()->back()->withErrors([
//                 'document_fields_value' => __('Please provide the required document', ['field' => __('message.document_fields_value')])
//             ]);
//         }

//         if (!$document->is_required && $request->has('document_fields_value')) {
//             return redirect()->back()->withErrors([
//                 'document_fields_value' => __('Please provide the required document', ['field' => __('message.document_fields_value')])
//             ]);
//         }
//     }

    $validatedData = $request->validate($rules);

    // ✅ **File Upload Logic**
    if ($document && $document->field && $document->field->name == "File") {
        if ($request->hasFile('document_fields_value')) {
            $file = $request->file('document_fields_value');
            $filePath = $file->store('driver_document', 'public'); // Save in storage/app/public/driver_document
            $driver_document->document_fields_value = $filePath; // Store file path in DB
        }
    } else {
        $driver_document->document_fields_value = $request->input('document_fields_value', null);
    }

    // ✅ **Updating the Driver Document**
    $driver_document->fill($request->except('document_fields_value'));
    $driver_document->save();

    // ✅ **Media File Update**
    if (isset($request->driver_document) && $request->driver_document != null) {
        $driver_document->clearMediaCollection('driver_document');
        $driver_document->addMediaFromRequest('driver_document')->toMediaCollection('driver_document');
    }

    $message = __('message.update_form', ['form' => __('message.driver_document')]);

    // ✅ **Updating Driver Verification Status**
    $is_verified = $driver_document->is_verified;
    if (in_array($is_verified, [0, 1, 2]) || $driver_document->driver->is_verified_driver == 0) {
        $is_verified_driver = (int)$driver_document->verifyDriverDocument($driver_document->driver->id);
        $driver_document->driver->update(['is_verified_driver' => $is_verified_driver]);
    }

    // ✅ **Sending Notification on Status Change**
    if ($old_is_verified != $is_verified && in_array($is_verified, [0, 1, 2])) {
        $type = 'document_approved';
        $status = __('message.approved');
        if ($is_verified == 0) {
            $type = 'document_pending';
            $status = __('message.pending');
        }
        if ($is_verified == 2) {
            $type = 'document_rejected';
            $status = __('message.rejected');
        }
        $notification_data = [
            'id' => $driver_document->driver->id,
            'is_verified_driver' => (int)$driver_document->driver->is_verified_driver,
            'type' => $type,
            'subject' => __('message.' . $type),
            'message' => __('message.approved_reject_form', ['form' => $driver_document->document->name, 'status' => $status]),
        ];

        $driver_document->driver->notify(new RideNotification($notification_data));
        $driver_document->driver->notify(new CommonNotification($notification_data['type'], $notification_data));
    }

    if (request()->is('api/*')) {
        return json_message_response($message);
    }

    return auth()->check()
        ? redirect()->route('driverdocument.index')->withSuccess($message)
        : redirect()->back()->withSuccess($message);
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
            return redirect()->route('driverdocument.index')->withErrors($message);
        }
        $driver_document = DriverDocument::find($id);
        $status = 'errors';
        $message = __('message.not_found_entry', ['name' => __('message.driver_document')]);

        if($driver_document != '') {
            $driver_document->delete();
            $status = 'success';
            $message = __('message.delete_form', ['form' => __('message.driver_document')]);
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
