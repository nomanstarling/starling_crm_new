<div class="form-group row bg-light-primary">

    <div class="col-lg-12">
        <div class="dropzone dropzone-queue mb-2" id="documentsZone">
            <div class="dropzone-panel mb-lg-0 mb-2">
                <a class="dropzone-select btn btn-sm btn-primary me-2">Attach files</a>
                <a class="dropzone-upload btn btn-sm btn-light-primary me-2">Upload All</a>
                <a class="dropzone-remove-all btn btn-sm btn-light-primary">Remove All</a>
            </div>
            <div class="dropzone-items wm-200px">
                <div class="dropzone-item" style="display:none">
                    <div class="dropzone-file">
                        <div class="dropzone-filename" title="some_image_file_name.jpg">
                            <span data-dz-name>some_image_file_name.jpg</span>
                            <strong>(<span data-dz-size>340kb</span>)</strong>
                        </div>

                        <div class="dropzone-error" data-dz-errormessage></div>
                    </div>
                    <div class="dropzone-toolbar">
                        <span class="dropzone-start" style="display: none;"><i class="bi bi-play-fill fs-3"></i></span>
                        <span class="dropzone-cancel" data-dz-remove style="display: none;"><i class="bi bi-x fs-3"></i></span>
                        <span class="dropzone-delete" data-dz-remove><i class="bi bi-x fs-1"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <span class="form-text text-muted">Max file size is 1MB and max number of files is 5.</span>
    </div>
</div>