<?php
require_once "connect.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function idExists($conn, $table, $idColumn, $idValue) {
    $query = "SELECT COUNT(*) AS total FROM $table WHERE $idColumn = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $idValue);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["total"] ?? 0) > 0;
}

$message = "";
$searchConsult = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $consultID = trim($_POST["consultID"] ?? "");
        $patID = trim($_POST["patID"] ?? "");
        $docID = trim($_POST["docID"] ?? "");
        $consultDate = trim($_POST["consultDate"] ?? "");
        $diagnosis = trim($_POST["diagnosis"] ?? "");
        $prescription = trim($_POST["prescription"] ?? "");

        if (!idExists($conn, "patient", "patID", $patID)) {
            $message = "Cannot add consultation. Patient ID does not exist.";
        } elseif (!idExists($conn, "doctor", "docID", $docID)) {
            $message = "Cannot add consultation. Doctor ID does not exist.";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO consultation (consultID, patID, docID, consultDate, diagnosis, prescription) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $consultID, $patID, $docID, $consultDate, $diagnosis, $prescription);
                $message = $stmt->execute() ? "Consultation added successfully." : "Error: " . $stmt->error;
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                $message = "Database error while adding consultation: " . $e->getMessage();
            }
        }
    }

    if ($action === "update") {
        $consultID = trim($_POST["consultID"] ?? "");
        $patID = trim($_POST["patID"] ?? "");
        $docID = trim($_POST["docID"] ?? "");
        $consultDate = trim($_POST["consultDate"] ?? "");
        $diagnosis = trim($_POST["diagnosis"] ?? "");
        $prescription = trim($_POST["prescription"] ?? "");

        if (!idExists($conn, "patient", "patID", $patID)) {
            $message = "Cannot update consultation. Patient ID does not exist.";
        } elseif (!idExists($conn, "doctor", "docID", $docID)) {
            $message = "Cannot update consultation. Doctor ID does not exist.";
        } else {
            try {
                $stmt = $conn->prepare("UPDATE consultation SET patID = ?, docID = ?, consultDate = ?, diagnosis = ?, prescription = ? WHERE consultID = ?");
                $stmt->bind_param("ssssss", $patID, $docID, $consultDate, $diagnosis, $prescription, $consultID);
                $message = $stmt->execute() ? "Consultation updated successfully." : "Error: " . $stmt->error;
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                $message = "Database error while updating consultation: " . $e->getMessage();
            }
        }
    }

    if ($action === "delete") {
        $consultID = trim($_POST["consultID"] ?? "");
        $stmt = $conn->prepare("DELETE FROM consultation WHERE consultID = ?");
        $stmt->bind_param("s", $consultID);
        $message = $stmt->execute() ? "Consultation deleted successfully." : "Error: " . $stmt->error;
        $stmt->close();
    }

    if ($action === "search") {
        $consultID = trim($_POST["consultID"] ?? "");
        $stmt = $conn->prepare("SELECT * FROM consultation WHERE consultID = ?");
        $stmt->bind_param("s", $consultID);
        $stmt->execute();
        $result = $stmt->get_result();
        $searchConsult = $result->fetch_assoc();
        $message = $searchConsult ? "Consultation record found." : "No consultation record found.";
        $stmt->close();
    }
}

$countResult = $conn->query("SELECT COUNT(*) AS totalConsults FROM consultation");
$totalConsults = $countResult ? ($countResult->fetch_assoc()["totalConsults"] ?? 0) : 0;
$allConsults = $conn->query("
    SELECT c.*, p.patFName, p.patLName, d.docFName, d.docLName
    FROM consultation c
    LEFT JOIN patient p ON c.patID = p.patID
    LEFT JOIN doctor d ON c.docID = d.docID
    ORDER BY c.consultDate DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-4">
    <div class="page-box">
        <h1>Consultations Management</h1>
        <a href="index.php" class="btn btn-secondary btn-sm mb-3">Back to Menu</a>
        <?php if ($message !== ""): ?>
            <div class="alert alert-info"><?php echo e($message); ?></div>
        <?php endif; ?>
        <p><strong>Total Consultations (COUNT):</strong> <?php echo e($totalConsults); ?></p>
    </div>

    <div class="page-box">
        <h2>Add Consultation</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="row g-2">
                <div class="col-md-2"><input type="text" name="consultID" class="form-control" placeholder="Consult ID" required></div>
                <div class="col-md-2"><input type="text" name="patID" class="form-control" placeholder="Patient ID" required></div>
                <div class="col-md-2"><input type="text" name="docID" class="form-control" placeholder="Doctor ID" required></div>
                <div class="col-md-2"><input type="datetime-local" name="consultDate" class="form-control" required></div>
                <div class="col-md-2"><input type="text" name="diagnosis" class="form-control" placeholder="Diagnosis" required></div>
                <div class="col-md-2"><input type="text" name="prescription" class="form-control" placeholder="Prescription" required></div>
            </div>
            <div class="mt-2"><button type="submit" class="btn btn-primary">Add</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>Search Consultation</h2>
        <form method="post" class="row g-2 mb-3">
            <input type="hidden" name="action" value="search">
            <div class="col-md-3"><input type="text" name="consultID" class="form-control" placeholder="Consult ID" required></div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-dark">Search</button></div>
        </form>

        <?php if ($searchConsult): ?>
            <table class="table table-bordered">
                <tr><th>Consult ID</th><td><?php echo e($searchConsult["consultID"]); ?></td></tr>
                <tr><th>Patient ID</th><td><?php echo e($searchConsult["patID"]); ?></td></tr>
                <tr><th>Doctor ID</th><td><?php echo e($searchConsult["docID"]); ?></td></tr>
                <tr><th>Date/Time</th><td><?php echo e($searchConsult["consultDate"]); ?></td></tr>
                <tr><th>Diagnosis</th><td><?php echo e($searchConsult["diagnosis"]); ?></td></tr>
                <tr><th>Prescription</th><td><?php echo e($searchConsult["prescription"]); ?></td></tr>
            </table>
        <?php endif; ?>
    </div>

    <div class="page-box">
        <h2>Update Consultation</h2>
        <form method="post">
            <input type="hidden" name="action" value="update">
            <div class="row g-2">
                <div class="col-md-2"><input type="text" name="consultID" class="form-control" placeholder="Consult ID" required></div>
                <div class="col-md-2"><input type="text" name="patID" class="form-control" placeholder="Patient ID" required></div>
                <div class="col-md-2"><input type="text" name="docID" class="form-control" placeholder="Doctor ID" required></div>
                <div class="col-md-2"><input type="datetime-local" name="consultDate" class="form-control" required></div>
                <div class="col-md-2"><input type="text" name="diagnosis" class="form-control" placeholder="Diagnosis" required></div>
                <div class="col-md-2"><input type="text" name="prescription" class="form-control" placeholder="Prescription" required></div>
            </div>
            <div class="mt-2"><button type="submit" class="btn btn-warning">Update</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>Delete Consultation</h2>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="delete">
            <div class="col-md-3"><input type="text" name="consultID" class="form-control" placeholder="Consult ID" required></div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-danger">Delete</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>View Consultations</h2>
        <div class="table-wrapper">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Consult ID</th>
                    <th>Patient ID</th>
                    <th>Patient Name</th>
                    <th>Doctor ID</th>
                    <th>Doctor Name</th>
                    <th>Date/Time</th>
                    <th>Diagnosis</th>
                    <th>Prescription</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($allConsults && $allConsults->num_rows > 0): ?>
                    <?php while ($row = $allConsults->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo e($row["consultID"]); ?></td>
                            <td><?php echo e($row["patID"]); ?></td>
                            <td><?php echo e(trim(($row["patFName"] ?? "") . " " . ($row["patLName"] ?? ""))); ?></td>
                            <td><?php echo e($row["docID"]); ?></td>
                            <td><?php echo e(trim(($row["docFName"] ?? "") . " " . ($row["docLName"] ?? ""))); ?></td>
                            <td><?php echo e($row["consultDate"]); ?></td>
                            <td><?php echo e($row["diagnosis"]); ?></td>
                            <td><?php echo e($row["prescription"]); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No consultation records yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
