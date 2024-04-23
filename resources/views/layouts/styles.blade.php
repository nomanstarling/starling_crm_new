<style>

    .modalRight .modal-content {
        min-height: 100%;
        border: 0;
        border-radius: 0;
    }

    .modalRight .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 120px); /* Adjust as needed */
    }

    /* Custom class for sliding from the right */
    .modal.modalRight .modal-dialog {
        position: fixed;
        margin: 0;
        width: 60% !important;
        height: 100%;
        right: 0;
        top: 0;
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
    }

    .modal.w-90, .modal.w-98, .modal.w-99{
        --bs-modal-width: inherit !important;
    }

    .modal.w-90 .modal-dialog{
        width: 90% !important;
    }

    .modal.w-99 .modal-dialog{
        width: 100% !important;
    }

    .modal.w-97 .modal-dialog{
        width: 97% !important;
    }

    .modal.modalRight.show .modal-dialog {
        transform: translateX(0);
    }

    .modal.modalRight .modal-content {
        height: 100%;
        border-radius: 0;
        position: relative;
    }

    .modal.modalRight .modal-footer {
        border-radius: 0;
        bottom: 0px;
        position: absolute;
        width: 100%;
    }

    .notify{
        z-index:99;
        top:60px;
    }
    @media (min-width: 992px){
        .wrapper{
            padding-left:inherit !important;
        }
    }
    .w-30{
        width:32%;
    }
    .border-right{
        border-right:1px solid #F1F1F4;
    }
    .bg-grey{
        background:#E9ECF0 !important;
    }
    .iti{
        width: 100%;
    }
    .card .card-header{
        min-height: inherit !important;
    }
    .image-input-placeholder {
        background-image: url({{ asset('assets/media/svg/avatars/blank.svg') }});
    }

    [data-bs-theme="dark"] .image-input-placeholder {
        background-image: url({{ asset('assets/media/svg/avatars/blank-dark.svg') }});
    }
    #toastr-container{
        top: 80px !important;
    }
    .btn-xs{
        padding: 3px 5px !important;
        font-size: 12px !important;
        line-height: 1.5 !important;
        border-radius: 3px !important;
    }
    .btn-xs i{
        padding: 0px !important;
    }

    .scrollable-cell {
        max-width: 200px; /* Adjust the max-width as per your design */
        overflow-x: auto;
        white-space: nowrap;
    }
    .mr-1{
        margin-right:5px;
    }
    .ml-4{
        margin-left:10px;
    }

</style>

<style>
    /* .noteRow {
        position: relative;
    }

    .noteContainer {
        position: relative;
    }

    .noteText {
        display: inline-block;
    }

    .removeButton {
        position: absolute;
        top: 4px;
        right: 4px;
        display:inline-block;
    } */


    

    :root {
	--c-grey-100: #f4f6f8;
	--c-grey-200: #e3e3e3;
	--c-grey-300: #b2b2b2;
	--c-grey-400: #7b7b7b;
	--c-grey-500: #3d3d3d;

	--c-blue-500: #688afd;
}

.timeline {
	width: 95%;
	/* max-width: 700px; */
	margin-left: auto;
	margin-right: auto;
	display: flex;
	flex-direction: column;
	padding: 3px 0 20px 32px;
	border-left: 2px solid var(--c-grey-200);
	font-size: 1.125rem;
}
.timeline .timeline-item-wrapper{
    width:100% !important;
}

.timeline .timeline-item {
	display: flex;
	gap: 24px;
	& + * {
		margin-top: 24px;
	}
	& + .extra-space {
		margin-top: 48px;
	}
}
.notesTable .timeline-item{
    margin-top: 15px !important;
}
.timeline .new-comment {
	width: 100%;
	input {
		border: 1px solid var(--c-grey-200);
		border-radius: 6px;
		height: 48px;
		padding: 0 16px;
		width: 100%;
		&::placeholder {
			color: var(--c-grey-300);
		}

		&:focus {
			border-color: var(--c-grey-300);
			outline: 0; // Don't actually do this
			box-shadow: 0 0 0 4px var(--c-grey-100);
		}
	}
}

.timeline .timeline-item-icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 30px;
	height: 30px;
	border-radius: 50%;
	margin-left: -48px;
	flex-shrink: 0;
	overflow: hidden;
    border: 1px solid #dbdfe9;
    color: #99a1b7;
    background:white;
	box-shadow: 0 0 0 6px #fff;
	/* svg {
		width: 20px;
		height: 20px;
	} */

	&.faded-icon {
		/* background-color: var(--c-grey-100);
		color: var(--c-grey-400); */
	}

	&.filled-icon {
		/* background-color: var(--c-blue-500);
		color: #fff; */
	}
}

.timeline .timeline-item-description {
	display: flex;
	padding-top: 6px;
	gap: 8px;
	color: var(--c-grey-400);

	img {
		flex-shrink: 0;
	}
	a {
		color: var(--c-grey-500);
		font-weight: 500;
		text-decoration: none;
		&:hover,
		&:focus {
			outline: 0; // Don't actually do this
			color: var(--c-blue-500);
		}
	}
}

.timeline .avatar {
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 5px;
	overflow: hidden;
	aspect-ratio: 1 / 1;
	flex-shrink: 0;
	width: 35px;
	height: 35px;

	img {
		object-fit: cover;
	}
}

.timeline .comment {
	margin-top: 8px;
	color: var(--c-grey-500);
	/* border: 1px solid #fdf1f1; */
	box-shadow: 0 4px 4px 0 var(--c-grey-100);
	border-radius: 6px;
	/* padding: 4px; */
	font-size: 1rem;
    position:relative;
}

.timeline .comment button {
	position: absolute;
    top:7px;
    right:11px;
}

.timeline  .avatar-list {
	display: flex;
	align-items: center;
	& > * {
		position: relative;
		box-shadow: 0 0 0 2px #fff;
		margin-right: -8px;
	}
}
.dropzone.dropzone-queue .dropzone-item{
    background-color: #d9e4f2 !important;
}



.input-group-text .form-check.form-check-solid .form-check-input:not(:checked){
    background-color:black !important;
}
.input-group-text .form-check-input:checked{
    background-color:#73C88E !important;
}

.form-switch.form-check-solid .form-check-input:not(:checked){
    background-color:#cdcdcd !important;
}

.form-check-input:checked{
    background-color:#73C88E !important;
}
input[readonly] {
    background-color: #F1F1F4 !important;
}
.sendEmailSwal .swal2-html-container{
    max-height: inherit !important;
}
.animated-row {
    transition: transform 0.5s ease-in-out;
}
.headerSearch{
    background-color: #2c2c41;
    border-color: #2c2c41;
    color: #878aa0;
}
.headerSearchForm .search-icon{
    color: #dadada !important;
}
.headerSearch:focus{
    background-color: #2c2c41;
    border-color: #2c2c41;
    color: #878aa0;
}
.btn-dark{
    background: #0B0C10 !important;
}
.table:not(.table-bordered) td, .table:not(.table-bordered) th, .table:not(.table-bordered) tr{
    text-transform: capitalize !important;
}
.iti__a11y-text{
    display: none;
}
.row-disabled{
    background: #f2f2f2 !important;
}
</style>