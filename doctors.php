<?php
require_once "connect.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";
$searchDoctor = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $docID = trim($_POST["docID"] ?? "");
        $docFName = trim($_POST["docFName"] ?? "");
        $docLName = trim($_POST["docLName"] ?? "");
        $docAddress = trim($_POST["docAddress"] ?? "");
        $docSpecial = trim($_POST["docSpecial"] ?? "");

        $stmt = $conn->prepare("INSERT INTO doctor (docID, docFName, docLName, docAddress, docSpecial) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $docID, $docFName, $docLName, $docAddress, $docSpecial);
        $message = $stmt->execute() ? "Doctor added successfully." : "Error: " . $stmt->error;
        $stmt->close();
    }

    if ($action === "update") {
        $docID = trim($_POST["docID"] ?? "");
        $docFName = trim($_POST["docFName"] ?? "");
        $docLName = trim($_POST["docLName"] ?? "");
        $docAddress = trim($_POST["docAddress"] ?? "");
        $docSpecial = trim($_POST["docSpecial"] ?? "");

        $stmt = $conn->prepare("UPDATE doctor SET docFName = ?, docLName = ?, docAddress = ?, docSpecial = ? WHERE docID = ?");
        $stmt->bind_param("sssss", $docFName, $docLName, $docAddress, $docSpecial, $docID);
        $message = $stmt->execute() ? "Doctor updated successfully." : "Error: " . $stmt->error;
        $stmt->close();
    }

    if ($action === "delete") {
        $docID = trim($_POST["docID"] ?? "");
        $stmt = $conn->prepare("DELETE FROM doctor WHERE docID = ?");
        $stmt->bind_param("s", $docID);
        $message = $stmt->execute() ? "Doctor deleted successfully." : "Error: " . $stmt->error;
        $stmt->close();
    }

    if ($action === "search") {
        $docID = trim($_POST["docID"] ?? "");
        $stmt = $conn->prepare("SELECT * FROM doctor WHERE docID = ?");
        $stmt->bind_param("s", $docID);
        $stmt->execute();
        $result = $stmt->get_result();
        $searchDoctor = $result->fetch_assoc();
        $message = $searchDoctor ? "Doctor record found." : "No doctor record found.";
        $stmt->close();
    }
}

$countResult = $conn->query("SELECT COUNT(*) AS totalDoctors FROM doctor");
$totalDoctors = $countResult ? ($countResult->fetch_assoc()["totalDoctors"] ?? 0) : 0;
$allDoctors = $conn->query("SELECT * FROM doctor ORDER BY docLName, docFName");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-4">
    <div class="page-box">
        <h1>Doctors Management</h1>
        <a href="index.php" class="btn btn-secondary btn-sm mb-3">Back to Menu</a>
        <?php if ($message !== ""): ?>
            <div class="alert alert-info"><?php echo e($message); ?></div>
        <?php endif; ?>
        <p><strong>Total Doctors (COUNT):</strong> <?php echo e($totalDoctors); ?></p>
    </div>

    <div class="page-box">
        <h2>Add Doctor</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="row g-2">
                <div class="col-md-2"><input type="text" name="docID" class="form-control" placeholder="Doctor ID" required></div>
                <div class="col-md-2"><input type="text" name="docFName" class="form-control" placeholder="First Name" required></div>
                <div class="col-md-2"><input type="text" name="docLName" class="form-control" placeholder="Last Name" required></div>
                <div class="col-md-3"><input type="text" name="docAddress" class="form-control" placeholder="Address" required></div>
                <div class="col-md-2"><input type="text" name="docSpecial" class="form-control" placeholder="Specialization" required></div>
                <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary">Add</button></div>
            </div>
        </form>
    </div>

    <div class="page-box">
        <h2>Search Doctor</h2>
        <form method="post" class="row g-2 mb-3">
            <input type="hidden" name="action" value="search">
            <div class="col-md-3"><input type="text" name="docID" class="form-control" placeholder="Doctor ID" required></div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-dark">Search</button></div>
        </form>

        <?php if ($searchDoctor): ?>
            <table class="table table-bordered">
                <tr><th>Doctor ID</th><td><?php echo e($searchDoctor["docID"]); ?></td></tr>
                <tr><th>First Name</th><td><?php echo e($searchDoctor["docFName"]); ?></td></tr>
                <tr><th>Last Name</th><td><?php echo e($searchDoctor["docLName"]); ?></td></tr>
                <tr><th>Address</th><td><?php echo e($searchDoctor["docAddress"]); ?></td></tr>
                <tr><th>Specialization</th><td><?php echo e($searchDoctor["docSpecial"]); ?></td></tr>
            </table>
        <?php endif; ?>
    </div>

    <div class="page-box">
        <h2>Update Doctor</h2>
        <form method="post">
            <input type="hidden" name="action" value="update">
            <div class="row g-2">
                <div class="col-md-2"><input type="text" name="docID" class="form-control" placeholder="Doctor ID" required></div>
                <div class="col-md-2"><input type="text" name="docFName" class="form-control" placeholder="First Name" required></div>
                <div class="col-md-2"><input type="text" name="docLName" class="form-control" placeholder="Last Name" required></div>
                <div class="col-md-3"><input type="text" name="docAddress" class="form-control" placeholder="Address" required></div>
                <div class="col-md-2"><input type="text" name="docSpecial" class="form-control" placeholder="Specialization" required></div>
                <div class="col-md-1 d-grid"><button type="submit" class="btn btn-warning">Update</button></div>
            </div>
        </form>
    </div>

    <div class="page-box">
        <h2>Delete Doctor</h2>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="delete">
            <div class="col-md-3"><input type="text" name="docID" class="form-control" placeholder="Doctor ID" required></div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-danger">Delete</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>View Doctors</h2>
        <div class="table-wrapper">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Address</th>
                    <th>Specialization</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($allDoctors && $allDoctors->num_rows > 0): ?>
                    <?php while ($row = $allDoctors->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo e($row["docID"]); ?></td>
                            <td><?php echo e($row["docFName"]); ?></td>
                            <td><?php echo e($row["docLName"]); ?></td>
                            <td><?php echo e($row["docAddress"]); ?></td>
                            <td><?php echo e($row["docSpecial"]); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No doctor records yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
