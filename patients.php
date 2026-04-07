<?php
require_once "connect.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function patientAgeValue($row) {
    if (isset($row["patAge"])) {
        return $row["patAge"];
    }
    return "";
}

$message = "";
$searchPatient = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $patID = trim($_POST["patID"] ?? "");
        $patFName = trim($_POST["patFName"] ?? "");
        $patLName = trim($_POST["patLName"] ?? "");
        $patAge = (int)($_POST["patAge"] ?? 0);
        $patTelNo = trim($_POST["patTelNo"] ?? "");

        $stmt = $conn->prepare("INSERT INTO patient (patID, patFName, patLName, patAge, patTelNo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $patID, $patFName, $patLName, $patAge, $patTelNo);
        $message = $stmt->execute() ? "Patient added successfully." : "Error: " . $stmt->error;
        $stmt->close();
    }

    if ($action === "update") {
        $patID = trim($_POST["patID"] ?? "");
        $patFName = trim($_POST["patFName"] ?? "");
        $patLName = trim($_POST["patLName"] ?? "");
        $patAge = (int)($_POST["patAge"] ?? 0);
        $patTelNo = trim($_POST["patTelNo"] ?? "");

        $stmt = $conn->prepare("UPDATE patient SET patFName = ?, patLName = ?, patAge = ?, patTelNo = ? WHERE patID = ?");
        $stmt->bind_param("ssiss", $patFName, $patLName, $patAge, $patTelNo, $patID);
        $message = $stmt->execute() ? "Patient updated successfully." : "Error: " . $stmt->error;
        $stmt->close();
    }

    if ($action === "delete") {
        $patID = trim($_POST["patID"] ?? "");
        $stmt = $conn->prepare("DELETE FROM patient WHERE patID = ?");
        $stmt->bind_param("s", $patID);
        $message = $stmt->execute() ? "Patient deleted successfully." : "Error: " . $stmt->error;
        $stmt->close();
    }

    if ($action === "search") {
        $patID = trim($_POST["patID"] ?? "");
        $stmt = $conn->prepare("SELECT * FROM patient WHERE patID = ?");
        $stmt->bind_param("s", $patID);
        $stmt->execute();
        $result = $stmt->get_result();
        $searchPatient = $result->fetch_assoc();
        $message = $searchPatient ? "Patient record found." : "No patient record found.";
        $stmt->close();
    }
}

$countResult = $conn->query("SELECT COUNT(*) AS totalPatients FROM patient");
$totalPatients = $countResult ? ($countResult->fetch_assoc()["totalPatients"] ?? 0) : 0;
$allPatients = $conn->query("SELECT * FROM patient ORDER BY patLName, patFName");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-4">
    <div class="page-box">
        <h1>Patients Management</h1>
        <a href="index.php" class="btn btn-secondary btn-sm mb-3">Back to Menu</a>
        <?php if ($message !== ""): ?>
            <div class="alert alert-info"><?php echo e($message); ?></div>
        <?php endif; ?>
        <p><strong>Total Patients (COUNT):</strong> <?php echo e($totalPatients); ?></p>
    </div>

    <div class="page-box">
        <h2>Add Patient</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="row g-2">
                <div class="col-md-2"><input type="text" name="patID" class="form-control" placeholder="Patient ID" required></div>
                <div class="col-md-2"><input type="text" name="patFName" class="form-control" placeholder="First Name" required></div>
                <div class="col-md-2"><input type="text" name="patLName" class="form-control" placeholder="Last Name" required></div>
                <div class="col-md-2"><input type="number" name="patAge" class="form-control" placeholder="Age" min="0" required></div>
                <div class="col-md-2"><input type="text" name="patTelNo" class="form-control" placeholder="Telephone" required></div>
                <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary">Add</button></div>
            </div>
        </form>
    </div>

    <div class="page-box">
        <h2>Search Patient</h2>
        <form method="post" class="row g-2 mb-3">
            <input type="hidden" name="action" value="search">
            <div class="col-md-3"><input type="text" name="patID" class="form-control" placeholder="Patient ID" required></div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-dark">Search</button></div>
        </form>

        <?php if ($searchPatient): ?>
            <table class="table table-bordered">
                <tr><th>Patient ID</th><td><?php echo e($searchPatient["patID"]); ?></td></tr>
                <tr><th>First Name</th><td><?php echo e($searchPatient["patFName"]); ?></td></tr>
                <tr><th>Last Name</th><td><?php echo e($searchPatient["patLName"]); ?></td></tr>
                <tr><th>Age</th><td><?php echo e(patientAgeValue($searchPatient)); ?></td></tr>
                <tr><th>Telephone</th><td><?php echo e($searchPatient["patTelNo"]); ?></td></tr>
            </table>
        <?php endif; ?>
    </div>

    <div class="page-box">
        <h2>Update Patient</h2>
        <form method="post">
            <input type="hidden" name="action" value="update">
            <div class="row g-2">
                <div class="col-md-2"><input type="text" name="patID" class="form-control" placeholder="Patient ID" required></div>
                <div class="col-md-2"><input type="text" name="patFName" class="form-control" placeholder="First Name" required></div>
                <div class="col-md-2"><input type="text" name="patLName" class="form-control" placeholder="Last Name" required></div>
                <div class="col-md-2"><input type="number" name="patAge" class="form-control" placeholder="Age" min="0" required></div>
                <div class="col-md-2"><input type="text" name="patTelNo" class="form-control" placeholder="Telephone" required></div>
                <div class="col-md-1 d-grid"><button type="submit" class="btn btn-warning">Update</button></div>
            </div>
        </form>
    </div>

    <div class="page-box">
        <h2>Delete Patient</h2>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="delete">
            <div class="col-md-3"><input type="text" name="patID" class="form-control" placeholder="Patient ID" required></div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-danger">Delete</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>View Patients</h2>
        <div class="table-wrapper">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Age</th>
                    <th>Telephone</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($allPatients && $allPatients->num_rows > 0): ?>
                    <?php while ($row = $allPatients->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo e($row["patID"]); ?></td>
                            <td><?php echo e($row["patFName"]); ?></td>
                            <td><?php echo e($row["patLName"]); ?></td>
                            <td><?php echo e(patientAgeValue($row)); ?></td>
                            <td><?php echo e($row["patTelNo"]); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No patient records yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
