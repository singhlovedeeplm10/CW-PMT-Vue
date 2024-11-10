<!-- Add Break Modal -->
<div class="modal fade" id="add_break_modal" tabindex="-1" role="dialog" aria-labelledby="addBreakModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBreakModalLabel">Add Break</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="break_form" action="" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="break_reason">Break Reason</label>
                        <input type="text" class="form-control" id="break_reason" name="break_reason" placeholder="Enter break reason" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" form="break_form" class="btn btn-primary">Take Break</button>
            </div>
        </div>
    </div>
</div>
