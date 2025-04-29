<?php
    $auth_user= authSession();
?>
{{ Form::open(['route' => ['location.destroy', $id], 'method' => 'delete','data--submit'=>'location'.$id]) }}
<div class="d-flex justify-content-end align-items-center">
    @if($auth_user->can('location edit'))
    <a class="mr-2" href="{{ route('location.edit', $id) }}" title="{{ __('message.update_form_title',['form' => __('message.location') ]) }}"><i class="fas fa-edit text-primary"></i></a>
    @endif

    @if($auth_user->can('location delete'))
    <a class="mr-2 text-danger" href="javascript:void(0)" data--submit="location{{$id}}" 
        data--confirmation='true' data-title="{{ __('message.delete_form_title',['form'=> __('message.location') ]) }}"
        title="{{ __('message.delete_form_title',['form'=>  __('message.location') ]) }}"
        data-message='{{ __("message.delete_msg") }}'>
        <i class="fas fa-trash-alt"></i>
    </a>
    @endif
</div>
{{ Form::close() }}