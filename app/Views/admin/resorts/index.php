<?php
// Prevent direct access to this file
if (!defined('APP_LOADED')) {
    http_response_code(403);
    require_once __DIR__ . '/../../errors/403.php';
    exit;
}

$pageTitle = "Manage Resorts";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Resorts</h3>
                    <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addResortModal">
                        Add New Resort
                    </button>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'resort_added'): ?>
                        <div class="alert alert-success">Resort added successfully!</div>
                    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'resort_updated'): ?>
                        <div class="alert alert-success">Resort updated successfully!</div>
                    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'resort_deleted'): ?>
                        <div class="alert alert-success">Resort deleted successfully!</div>
                    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'delete_has_facilities'): ?>
                        <div class="alert alert-danger">Cannot delete resort. It has associated facilities.</div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Contact Person</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($resorts)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No resorts found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($resorts as $resort): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($resort->resortId) ?></td>
                                            <td><?= htmlspecialchars($resort->name) ?></td>
                                            <td><?= htmlspecialchars($resort->address) ?></td>
                                            <td><?= htmlspecialchars($resort->contactPerson) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning edit-resort-btn" data-bs-toggle="modal" data-bs-target="#editResortModal" data-resort-id="<?= $resort->resortId ?>">
                                                    Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-resort-btn" data-bs-toggle="modal" data-bs-target="#deleteResortModal" data-delete-url="?controller=resort&action=destroy&id=<?= $resort->resortId ?>">
                                                    Delete
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info manage-payments-btn" data-bs-toggle="modal" data-bs-target="#managePaymentsModal" data-resort-id="<?= $resort->resortId ?>" data-resort-name="<?= htmlspecialchars($resort->name) ?>">
                                                    Manage Payments
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/resort_modals.php'; ?>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editResortModal = document.getElementById('editResortModal');
    if(editResortModal) {
        editResortModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const resortId = button.getAttribute('data-resort-id');
            const form = editResortModal.querySelector('#editResortForm');
            
            fetch(`?controller=resort&action=getResortJson&id=${resortId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.error) {
                        alert(data.error);
                    } else {
                        form.querySelector('#editResortId').value = data.resortId;
                        form.querySelector('#editName').value = data.name;
                        form.querySelector('#editAddress').value = data.address;
                        form.querySelector('#editContactPerson').value = data.contactPerson;
                    }
                })
                .catch(err => {
                    console.error('Error loading resort data:', err);
                    alert('Failed to load resort data. Please try again.');
                });
        });
    }

    const deleteResortModal = document.getElementById('deleteResortModal');
    if(deleteResortModal){
        deleteResortModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const deleteUrl = button.getAttribute('data-delete-url');
            const confirmBtn = document.getElementById('confirmDeleteResortBtn');
            confirmBtn.setAttribute('href', deleteUrl);
        });
    }
});

    const managePaymentsModal = document.getElementById('managePaymentsModal');
    if (managePaymentsModal) {
        managePaymentsModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const resortId = button.getAttribute('data-resort-id');
            const resortName = button.getAttribute('data-resort-name');
            
            document.getElementById('resortNameLabel').textContent = resortName;
            document.getElementById('paymentResortId').value = resortId;

            const paymentMethodsList = document.getElementById('paymentMethodsList');
            paymentMethodsList.innerHTML = '<p>Loading payment methods...</p>';

            fetch(`?controller=resort&action=getPaymentMethodsJson&resort_id=${resortId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        paymentMethodsList.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                        return;
                    }
                    if (data.length === 0) {
                        paymentMethodsList.innerHTML = '<p>No payment methods found for this resort.</p>';
                        return;
                    }

                    let html = '<ul class="list-group">';
                    data.forEach(method => {
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${method.MethodName}</strong>: ${method.MethodDetails}
                                </div>
                                <a href="?controller=resort&action=deletePaymentMethod&id=${method.PaymentMethodID}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this payment method?');">
                                    Delete
                                </a>
                            </li>
                        `;
                    });
                    html += '</ul>';
                    paymentMethodsList.innerHTML = html;
                })
                .catch(err => {
                    console.error('Error loading payment methods:', err);
                    paymentMethodsList.innerHTML = '<div class="alert alert-danger">Failed to load payment methods.</div>';
                });
        });
    }
</script>