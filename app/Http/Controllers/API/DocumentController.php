<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Http\Resources\DocumentResource;

class DocumentController extends Controller
{
    public function getList(Request $request){

        $document = Document::with('field');
    
        if( $request->has('status') && isset($request->status) ) {
            $document = $document->where('status',request('status'));
        }
        
        if( $request->has('is_deleted') && isset($request->is_deleted) && $request->is_deleted) {
            $document = $document->withTrashed();
        }

        $per_page = config('constant.PER_PAGE_LIMIT');
        
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $document->count();
            }
        }

        $document = $document->orderBy('id','desc')->paginate($per_page);
        
        $items = DocumentResource::collection($document);

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items,
        ];
        
        return json_custom_response($response);
    }
	public function getDoucumentList(Request $request)
    {
        $documentQuery = Document::with('field', 'group');
        
        if ($request->has('status') && isset($request->status)) {
            $documentQuery->where('status', $request->status);
        }
        
        if ($request->has('is_deleted') && isset($request->is_deleted) && $request->is_deleted) {
            $documentQuery->withTrashed();
        }

        // Agar per_page set hai to us hisaab se ya to count ya numeric value lein
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page == -1) {
                $per_page = $documentQuery->count();
            }
        }

        // Pahle sabhi documents lein (yahan pagination ke baad grouping karne me dikkat aa sakti hai,
        // isliye agar poore collection par grouping chahiye to pagination baad me apply karein ya custom logic use karein)
        $documents = $documentQuery->orderBy('id', 'desc')->get();
        
        // Group by group id (agar group null hai to "no_group")
        $grouped = $documents->groupBy(function ($doc) {
            return $doc->group ? $doc->group->id : 'no_group';
        });
        
        // Har group ka structure prepare karte hain
        $result = $grouped->map(function ($docs, $groupId) {
            // Agar group available hai to use hi nikalte hain, warna null
            $group = $docs->first()->group;
            return [
                'id'     => $group ? $group->id : null,
                'name'   => $group ? $group->name : null,
                'documents' => DocumentResource::collection($docs), // Yahan har document ka transformation ho raha hai
            ];
        })->values();
        
        $response = [
            'data' => $result,
        ];
        
        return json_custom_response($response);
    }
    public function updateFieldValue(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'document_field_id'    => 'required|exists:fields,id',
            'value'       => 'required|string',
        ]);

        $document = Document::where('id', $request->document_id)
                            ->where('document_field_id', $request->document_field_id)
                            ->first();

        if (!$document) {
            return response()->json([
                'message' => 'Document with given field not found'
            ], 404);
        }

        $document->update(['value' => $request->value]);

        return response()->json([
            'message' => 'Value updated successfully',
            'document' => $document
        ], 200);
    }
}