<?php
$pageTitle = "Advanced Blocking System";
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3><i class="fas fa-ban"></i> Advanced Blocking System</h3>
                    <div class="d-flex gap-2">
                        <a href="?controller=admin&action=management" class="btn btn-secondary">
                            <i class="fas fa-building"></i> Resort Management
                        </a>
                        <a href="?controller=admin&action=dashboard" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </div>
                </div>

                <!-- Resort Selection -->
                <div class="card-body border-bottom">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="controller" value="admin">
                        <input type="hidden" name="action" value="advancedBlocking">
                        
                        <div class="col-md-6">
                            <label class="form-label">Select Resort for Blocking Management</label>
                            <select name="resort_id" class="form-select" onchange="this.form.submit()" required>
                                <option value="">Choose a resort...</option>
                                <?php foreach ($resorts as $resort): ?>
                                    <option value="<?= $resort->resortId ?>" 
                                        <?= (isset($_GET['resort_id']) && $_GET['resort_id'] == $resort->resortId) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($resort->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>

                <?php if (!empty($_GET['resort_id'])): ?>
                <div class="card-body">
                    <div class="row">
                        <!-- Preset Blocking Options -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-magic"></i> Preset Blocking Options</h5>
                                    <p class="text-muted mb-0">Quick block multiple dates using preset rules</p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="?controller=admin&action=applyPresetBlocking">
                                        <input type="hidden" name="resort_id" value="<?= $_GET['resort_id'] ?>">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Preset Type</label>
                                                    <select class="form-select" name="preset_type" id="presetTypeSelect" required>
                                                        <option value="">Choose preset...</option>
                                                        <option value="weekends">
                                                            <i class="fas fa-calendar-week"></i> Weekends Only (Sat & Sun)
                                                        </option>
                                                        <option value="philippine_holidays">
                                                            <i class="fas fa-flag"></i> Philippine Holidays
                                                        </option>
                                                        <option value="all_dates">
                                                            <i class="fas fa-ban"></i> Full Block (All Dates)
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Reason</label>
                                                    <input type="text" class="form-control" name="reason"
                                                           placeholder="e.g., Maintenance, Private Event" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row" id="dateRangeContainer">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Start Date</label>
                                                    <input type="date" class="form-control" name="start_date"
                                                           min="<?= date('Y-m-d') ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">End Date</label>
                                                    <input type="date" class="form-control" name="end_date"
                                                           min="<?= date('Y-m-d') ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="holidayCheckboxContainer" class="mb-3" style="display: none;">
                                            <label class="form-label">Select Holidays to Block (for the current year)</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="holidays[]" value="01-01" id="holiday_01-01">
                                                        <label class="form-check-label" for="holiday_01-01">New Year's Day (Jan 1)</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="holidays[]" value="04-09" id="holiday_04-09">
                                                        <label class="form-check-label" for="holiday_04-09">Araw ng Kagitingan (Apr 9)</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="holidays[]" value="05-01" id="holiday_05-01">
                                                        <label class="form-check-label" for="holiday_05-01">Labor Day (May 1)</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="holidays[]" value="06-12" id="holiday_06-12">
                                                        <label class="form-check-label" for="holiday_06-12">Independence Day (Jun 12)</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="holidays[]" value="08-29" id="holiday_08-29">
                                                        <label class="form-check-label" for="holiday_08-29">National Heroes Day (Aug 29)</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="holidays[]" value="11-30" id="holiday_11-30">
                                                        <label class="form-check-label" for="holiday_11-30">Bonifacio Day (Nov 30)</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="holidays[]" value="12-25" id="holiday_12-25">
                                                        <label class="form-check-label" for="holiday_12-25">Christmas Day (Dec 25)</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="holidays[]" value="12-30" id="holiday_12-30">
                                                        <label class="form-check-label" for="holiday_12-30">Rizal Day (Dec 30)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-warning btn-lg">
                                                <i class="fas fa-ban"></i> Apply Preset Blocking
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Manual Individual Date Blocking -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5><i class="fas fa-calendar-plus"></i> Manual Date Blocking</h5>
                                    <p class="text-muted mb-0">Block individual dates manually</p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="?controller=admin&action=blockResortAvailability">
                                        <input type="hidden" name="resortId" value="<?= $_GET['resort_id'] ?>">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Block Date</label>
                                                    <input type="date" class="form-control" name="blockDate" 
                                                           min="<?= date('Y-m-d') ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Reason</label>
                                                    <input type="text" class="form-control" name="reason"
                                                           placeholder="e.g., Maintenance, Private Event" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-ban"></i> Block Single Date
                                        </button>
                                    </form>
                                </div>
                            </div>

                           <!-- Deblocking Options -->
                           <div class="card mt-4">
                               <div class="card-header">
                                   <h5><i class="fas fa-key"></i> Deblocking Options</h5>
                                   <p class="text-muted mb-0">Remove existing blocks by date range or all at once</p>
                               </div>
                               <div class="card-body">
                                   <!-- Deblock by Date Range -->
                                   <form method="POST" action="?controller=admin&action=deblockByDateRange" class="mb-3">
                                       <input type="hidden" name="resort_id" value="<?= $_GET['resort_id'] ?>">
                                       <div class="row">
                                           <div class="col-md-6">
                                               <label class="form-label">Start Date</label>
                                               <input type="date" class="form-control" name="start_date" required>
                                           </div>
                                           <div class="col-md-6">
                                               <label class="form-label">End Date</label>
                                               <input type="date" class="form-control" name="end_date" required>
                                           </div>
                                       </div>
                                       <button type="submit" class="btn btn-success mt-2">
                                           <i class="fas fa-key"></i> Deblock Date Range
                                       </button>
                                   </form>
                                   <hr>
                                   <!-- Deblock All -->
                                   <form method="POST" action="?controller=admin&action=deblockAll" onsubmit="return confirm('Are you sure you want to remove ALL blocks for this resort? This action cannot be undone.');">
                                       <input type="hidden" name="resort_id" value="<?= $_GET['resort_id'] ?>">
                                       <div class="d-grid">
                                           <button type="submit" class="btn btn-info">
                                               <i class="fas fa-globe"></i> Deblock All Dates
                                           </button>
                                       </div>
                                   </form>
                               </div>
                           </div>
                        </div>

                        <!-- Preset Information & Calendar Preview -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-info-circle"></i> Preset Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6><i class="fas fa-calendar-week text-primary"></i> Weekends</h6>
                                        <p class="small text-muted">Automatically blocks all Saturdays and Sundays within the selected date range.</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6><i class="fas fa-flag text-success"></i> Philippine Holidays</h6>
                                        <p class="small text-muted">Blocks major Philippine holidays including:</p>
                                        <ul class="small">
                                            <li>New Year's Day (Jan 1)</li>
                                            <li>Araw ng Kagitingan (Apr 9)</li>
                                            <li>Labor Day (May 1)</li>
                                            <li>Independence Day (Jun 12)</li>
                                            <li>National Heroes Day (Aug 29)</li>
                                            <li>Bonifacio Day (Nov 30)</li>
                                            <li>Christmas Day (Dec 25)</li>
                                            <li>Rizal Day (Dec 30)</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6><i class="fas fa-ban text-danger"></i> Full Block</h6>
                                        <p class="small text-muted">Blocks every single day within the selected date range. Useful for maintenance periods or temporary closure.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Important Notes</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="small">
                                        <li><strong>Existing Bookings:</strong> Blocking dates will not cancel existing confirmed bookings</li>
                                        <li><strong>Future Bookings:</strong> Blocked dates will prevent new bookings from being made</li>
                                        <li><strong>Bulk Operations:</strong> Large date ranges may take a few moments to process</li>
                                        <li><strong>Remove Blocks:</strong> Individual blocks can be removed from the Resort Management page</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Blocks Display -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5><i class="fas fa-list"></i> Current Blocked Dates</h5>
                                    <button class="btn btn-outline-primary btn-sm" id="loadBlocksBtn" 
                                            data-resort-id="<?= $_GET['resort_id'] ?>">
                                        <i class="fas fa-sync"></i> Refresh
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="blockedDatesList">
                                        <div class="text-center p-3">
                                            <i class="fas fa-spinner fa-spin"></i> Loading blocked dates...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card-body">
                    <div class="text-center p-5">
                        <i class="fas fa-hand-point-up fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Select a Resort</h4>
                        <p class="text-muted">Choose a resort from the dropdown above to manage its blocking settings.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resortId = '<?= $_GET['resort_id'] ?? '' ?>';
    
    if (resortId) {
        loadBlockedDates(resortId);
    }
    
    // Load blocks button
    document.getElementById('loadBlocksBtn')?.addEventListener('click', function() {
        const resortId = this.getAttribute('data-resort-id');
        loadBlockedDates(resortId);
    });
});

   document.getElementById('presetTypeSelect').addEventListener('change', function() {
       const holidayContainer = document.getElementById('holidayCheckboxContainer');
       const dateRangeContainer = document.getElementById('dateRangeContainer');
       const startDateInput = dateRangeContainer.querySelector('input[name="start_date"]');
       const endDateInput = dateRangeContainer.querySelector('input[name="end_date"]');

       if (this.value === 'philippine_holidays') {
           holidayContainer.style.display = 'block';
           dateRangeContainer.style.display = 'none';
           startDateInput.required = false;
           endDateInput.required = false;
       } else {
           holidayContainer.style.display = 'none';
           dateRangeContainer.style.display = 'block';
           startDateInput.required = true;
           endDateInput.required = true;
       }
   });

function loadBlockedDates(resortId) {
    const blocksList = document.getElementById('blockedDatesList');
    
    if (!blocksList || !resortId) return;
    
    blocksList.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading blocked dates...</div>';
    
    fetch(`?controller=admin&action=getResortScheduleJson&id=${resortId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                blocksList.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            if (data.length === 0) {
                blocksList.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No blocked dates found for this resort.
                    </div>`;
                return;
            }
            
            // Sort dates by BlockDate
            data.sort((a, b) => new Date(a.BlockDate) - new Date(b.BlockDate));
            
            let listHtml = '<div class="row">';
            data.forEach(block => {
                const blockDate = new Date(block.BlockDate);
                const formattedDate = blockDate.toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                listHtml += `
                    <div class="col-md-6 col-lg-4 mb-2">
                        <div class="card border-warning">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title mb-1">${formattedDate}</h6>
                                        <p class="card-text small text-muted mb-0">
                                            ${block.Reason}
                                        </p>
                                    </div>
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="removeBlock(${block.BlockedAvailabilityID}, ${resortId})"
                                            title="Remove block">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
            listHtml += '</div>';
            
            blocksList.innerHTML = listHtml;
        })
        .catch(error => {
            console.error('Error loading blocked dates:', error);
            blocksList.innerHTML = '<div class="alert alert-danger">Failed to load blocked dates. Please try again.</div>';
        });
}

function removeBlock(blockId, resortId) {
    if (!confirm('Are you sure you want to remove this block?')) {
        return;
    }
    
    fetch(`?controller=admin&action=deleteResortAvailabilityBlock&block_id=${blockId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadBlockedDates(resortId); // Refresh the list
            } else {
                alert('Failed to remove block.');
            }
        })
        .catch(error => {
            console.error('Error removing block:', error);
            alert('An error occurred while removing the block.');
        });
}
</script>