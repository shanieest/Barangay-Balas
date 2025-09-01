<?php
require_once __DIR__ . '/../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$household_data = [];
$residents = [];

// Decide where your household id comes from (session or GET param)
$householdId = $_SESSION['household_id'] ?? ($_GET['household_id'] ?? null);

if ($householdId) {
    // Fetch household details
    $stmt = $conn->prepare("
        SELECT id, house_number, purok, type_of_water_source, type_of_toilet_facility
        FROM households
        WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $householdId);
    $stmt->execute();
    $result = $stmt->get_result();
    $household_data = $result ? ($result->fetch_assoc() ?? []) : [];
    $stmt->close();

    // Fetch household members
    $stmt = $conn->prepare("
        SELECT 
            r.id, r.first_name, r.last_name, r.relationship_to_head, r.sex, r.civil_status,
            r.occupation, r.educational_attainment, r.philhealth_number,
            r.is_4ps_member, r.is_indigent, r.medical_history,
            CASE 
              WHEN r.date_of_birth IS NOT NULL THEN TIMESTAMPDIFF(YEAR, r.date_of_birth, CURDATE())
              WHEN r.age IS NOT NULL THEN r.age
              ELSE NULL
            END AS age
        FROM residents r
        WHERE r.household_id = ?
        ORDER BY r.relationship_to_head = 'Head' DESC, r.last_name ASC, r.first_name ASC
    ");
    $stmt->bind_param("i", $householdId);
    $stmt->execute();
    $res = $stmt->get_result();
    $residents = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

// Fetch all verified residents for selection
$all_residents = [];
$res_stmt = $conn->prepare("SELECT id, first_name, last_name FROM residents WHERE verification_status = 'Verified' ORDER BY last_name, first_name");
if ($res_stmt) {
    $res_stmt->execute();
    $result = $res_stmt->get_result();
    $all_residents = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $res_stmt->close();
}
?>
<!-- Census Data Section (Hidden by default) -->
<section id="census" class="d-none">
    <h2 class="mb-4">Census Data</h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Household information updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Error updating household information: <?= htmlspecialchars($_GET['message'] ?? 'Unknown error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Household Information</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateHouseholdModal">
                Update Household
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <!-- Household Details -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Household Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Household No:</strong> <?= htmlspecialchars($household_data['house_number'] ?? 'N/A'); ?></p>
                                    <p><strong>Purok:</strong> <?= htmlspecialchars($household_data['purok'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Household Amenities -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Household Amenities</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Water Source:</strong> <?= htmlspecialchars($household_data['type_of_water_source'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Toilet Facility:</strong> <?= htmlspecialchars($household_data['type_of_toilet_facility'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Household Members -->
            <h5 class="mb-3">Household Members</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Relationship</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Civil Status</th>
                            <th>Occupation</th>
                            <th>Education</th>
                            <th>PhilHealth</th>
                            <th>4Ps</th>
                            <th>Indigent</th>
                            <th>Medical History</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($residents)): ?>
                            <?php foreach ($residents as $resident): ?>
                                <tr>
                                    <td><?= htmlspecialchars(trim(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? ''))); ?></td>
                                    <td><?= htmlspecialchars($resident['relationship_to_head'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($resident['age'] !== null ? $resident['age'] : ''); ?></td>
                                    <td><?= htmlspecialchars(ucfirst($resident['sex'] ?? '')); ?></td>
                                    <td><?= htmlspecialchars($resident['civil_status'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($resident['occupation'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($resident['educational_attainment'] ?? ''); ?></td>
                                    <td><?= (!empty($resident['philhealth_number'])) ? 'Yes' : 'No'; ?></td>
                                    <td><?= !empty($resident['is_4ps_member']) ? 'Yes' : 'No'; ?></td>
                                    <td><?= !empty($resident['is_indigent']) ? 'Yes' : 'No'; ?></td>
                                    <td><?= htmlspecialchars($resident['medical_history'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="text-center">No household members found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include "../../pages/modals/censusModal.php"; ?>