@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="p-2 btn btn-primary btn-sm" style="float: left">Suppliers List : <b>{{ count($suppliers) }}</b></div>
                        </h3>
                        <button type="button" class="btn btn-primary btn-sm pull-right" style="float: right;"
                            data-toggle="modal" data-target="#modal-lg">
                            Add New Supplier
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Company Name</th>
                                    <th>Description</th>
                                    <th>Contacts</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suppliers as $index => $supplier)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $supplier->name }}</td>
                                        <td>{{ $supplier->description ?? '-' }}</td>
                                        <td>
                                            @if ($supplier->contacts->count() > 0)
                                                <ul class="mb-0">
                                                    @foreach ($supplier->contacts as $contact)
                                                        <li>
                                                            {{ $contact->name }}
                                                            @if ($contact->phone) ({{ $contact->phone }}) @endif
                                                            @if ($contact->qualification) - {{ $contact->qualification }} @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">No contacts</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm"
                                                data-toggle="modal" data-target="#edit-modal-{{ $supplier->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete this supplier?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="edit-modal-{{ $supplier->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Edit Supplier</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                                                    @csrf
                                                    @method('POST')
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Company Name</label>
                                                            <input type="text" name="name" class="form-control"
                                                                value="{{ $supplier->name }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Description</label>
                                                            <textarea name="description" class="form-control" rows="2">{{ $supplier->description }}</textarea>
                                                        </div>
                                                        <hr>
                                                        <h5>Contact Persons</h5>
                                                        <div id="edit-contacts-container-{{ $supplier->id }}">
                                                            @foreach ($supplier->contacts as $contact)
                                                                <div class="row contact-row mb-2">
                                                                    <input type="hidden" name="contacts[{{ $loop->index }}][id]" value="{{ $contact->id }}">
                                                                    <div class="col-md-4">
                                                                        <input type="text" name="contacts[{{ $loop->index }}][name]"
                                                                            class="form-control" placeholder="Name *"
                                                                            value="{{ $contact->name }}" required>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <input type="text" name="contacts[{{ $loop->index }}][phone]"
                                                                            class="form-control" placeholder="Phone"
                                                                            value="{{ $contact->phone }}">
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <input type="text" name="contacts[{{ $loop->index }}][qualification]"
                                                                            class="form-control" placeholder="Qualification"
                                                                            value="{{ $contact->qualification }}">
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <button type="button" class="btn btn-danger btn-sm remove-contact">X</button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 add-edit-contact"
                                                            data-target="edit-contacts-container-{{ $supplier->id }}">
                                                            <i class="fas fa-plus"></i> Add Contact
                                                        </button>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr><td colspan="5" class="text-center">No suppliers found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Create Supplier Modal -->
<div class="modal fade" id="modal-lg" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">New Supplier</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <hr>
                    <h5>Contact Persons</h5>
                    <div id="create-contacts-container">
                        <div class="row contact-row mb-2">
                            <div class="col-md-4">
                                <input type="text" name="contacts[0][name]" class="form-control" placeholder="Name *" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="contacts[0][phone]" class="form-control" placeholder="Phone">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="contacts[0][qualification]" class="form-control" placeholder="Qualification">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm remove-contact">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-create-contact">
                        <i class="fas fa-plus"></i> Add Contact
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ---- CREATE MODAL ----
        let contactIndex = 1;
        document.getElementById('add-create-contact').addEventListener('click', function() {
            const container = document.getElementById('create-contacts-container');
            const row = document.createElement('div');
            row.className = 'row contact-row mb-2';
            row.innerHTML = `
                <div class="col-md-4">
                    <input type="text" name="contacts[${contactIndex}][name]" class="form-control" placeholder="Name *" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="contacts[${contactIndex}][phone]" class="form-control" placeholder="Phone">
                </div>
                <div class="col-md-3">
                    <input type="text" name="contacts[${contactIndex}][qualification]" class="form-control" placeholder="Qualification">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-contact">X</button>
                </div>
            `;
            container.appendChild(row);
            contactIndex++;
        });

        // ---- EDIT MODALS ----
        document.querySelectorAll('.add-edit-contact').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const container = document.getElementById(targetId);
                const rowCount = container.querySelectorAll('.contact-row').length;
                const row = document.createElement('div');
                row.className = 'row contact-row mb-2';
                row.innerHTML = `
                    <div class="col-md-4">
                        <input type="text" name="contacts[${rowCount}][name]" class="form-control" placeholder="Name *" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="contacts[${rowCount}][phone]" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="contacts[${rowCount}][qualification]" class="form-control" placeholder="Qualification">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-contact">X</button>
                    </div>
                `;
                container.appendChild(row);
            });
        });

        // ---- REMOVE CONTACT ----
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-contact')) {
                const row = e.target.closest('.contact-row');
                const container = row.parentNode;
                if (container.querySelectorAll('.contact-row').length > 1) {
                    row.remove();
                } else {
                    alert('You must have at least one contact.');
                }
            }
        });
    });
</script>
@endsection