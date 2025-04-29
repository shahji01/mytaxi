<x-master-layout>
    <div>
        @if(isset($group))
            {!! Form::model($group, ['route' => ['group.update', $group->id], 'method' => 'patch' ]) !!}
        @else
            {!! Form::open(['route' => 'groups.store', 'method' => 'post']) !!}
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">{{ isset($group) ? 'Edit Group' : 'Create Group' }}</h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {{ Form::label('name', 'Group Name <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                {{ Form::text('name', null, ['class' => 'form-control', 'required']) }}
                            </div>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary float-right">Save</button>
                    </div>
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</x-master-layout>
