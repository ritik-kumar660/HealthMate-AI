<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = $_POST['name'];
            $age = $_POST['age'];
            $gender = $_POST['gender'];
            $relationship = $_POST['relationship'];
            $medical_history = $_POST['medical_history'];

            $stmt = $pdo->prepare("INSERT INTO family_members (user_id, name, age, gender, relationship, medical_history) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $age, $gender, $relationship, $medical_history]);
        } elseif ($_POST['action'] === 'delete' && isset($_POST['member_id'])) {
            // Begin transaction
            $pdo->beginTransaction();
            try {
                // Delete related appointments
                $stmt = $pdo->prepare("DELETE FROM appointments WHERE family_member_id = ?");
                $stmt->execute([$_POST['member_id']]);
                
                // Delete related medications
                $stmt = $pdo->prepare("DELETE FROM medications WHERE family_member_id = ?");
                $stmt->execute([$_POST['member_id']]);
                
                // Delete related health records
                $stmt = $pdo->prepare("DELETE FROM health_records WHERE family_member_id = ?");
                $stmt->execute([$_POST['member_id']]);
                
                // Finally, delete the family member
                $stmt = $pdo->prepare("DELETE FROM family_members WHERE id = ? AND user_id = ?");
                $stmt->execute([$_POST['member_id'], $_SESSION['user_id']]);
                
                // Commit the transaction
                $pdo->commit();
            } catch (Exception $e) {
                // If there's an error, rollback the changes
                $pdo->rollBack();
                $error = "Error deleting family member: " . $e->getMessage();
            }
        }
    }
}

// Fetch family members
$stmt = $pdo->prepare("SELECT * FROM family_members WHERE user_id = ? ORDER BY name");
$stmt->execute([$_SESSION['user_id']]);
$family_members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Members - Family Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .hero-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%),
                        url('https://img.freepik.com/free-photo/happy-family-medium-shot_23-2148947831.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        .hero-text {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Family Health Tracker</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="family_members.php">Family Members</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="health_records.php">Health Records</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medications.php">Medications</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 hero-text">Family Members</h1>
                    <p class="lead">Manage your family's health profiles in one place.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Family Member</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="age" name="age" required>
                            </div>
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="relationship" class="form-label">Relationship</label>
                                <input type="text" class="form-control" id="relationship" name="relationship" required>
                            </div>
                            <div class="mb-3">
                                <label for="medical_history" class="form-label">Medical History</label>
                                <textarea class="form-control" id="medical_history" name="medical_history" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Member</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Family Members List</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Relationship</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($family_members as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['age']); ?></td>
                                        <td><?php echo htmlspecialchars($member['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($member['relationship']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $member['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this member?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Container -->
    <?php foreach ($family_members as $member): ?>
    <div class="modal fade" id="viewModal<?php echo $member['id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $member['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel<?php echo $member['id']; ?>">Member Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($member['name']); ?></p>
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($member['age']); ?></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($member['gender']); ?></p>
                    <p><strong>Relationship:</strong> <?php echo htmlspecialchars($member['relationship']); ?></p>
                    <p><strong>Medical History:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($member['medical_history'])); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all modals
            var modals = document.querySelectorAll('.modal');
            modals.forEach(function(modal) {
                new bootstrap.Modal(modal);
            });
        });
    </script>
</body>
</html> 