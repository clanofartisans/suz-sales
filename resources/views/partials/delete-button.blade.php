{!! Form::open(['method' => 'DELETE', 'route'  => $route, 'role' => 'form', 'id' => 'delete-form', 'style' => 'display: inline-block;']) !!}

{!! Form::button('<i class="fa fa-remove"></i> Delete', ['id' => 'delete-button', 'class' => 'btn btn-danger btn-sm', 'data-toggle' => 'modal', 'data-target' => '#delete-confirm-modal']) !!}

{!! Form::close() !!}

<div class="modal fade" id="delete-confirm-modal" tabindex="-1" role="dialog" aria-labelledby="Confirm Deletion" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Confirm Deletion
            </div>
            <div class="modal-body">
                {{ $confirmation }}
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a href="#" id="delete-confirm-submit" class="btn btn-danger danger">Confirm Deletion</a>
            </div>
        </div>
    </div>
</div>
