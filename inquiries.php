<?php
require_once "connect.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$resultRows = [];
$countValue = 0;
$title = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "doctor_special") {
        $special = trim($_POST["docSpecial"] ?? "");
        $title = "Doctors by Specialization";

        $stmt = $conn->prepare("SELECT * FROM doctor WHERE docSpecial = ? ORDER BY docLName, docFName");
        $stmt->bind_param("s", $special);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultRows[] = $row;
        }
        $stmt->close();

        $stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM doctor WHERE docSpecial = ?");
        $stmtCount->bind_param("s", $special);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result()->fetch_assoc();
        $countValue = $countResult["total"] ?? 0;
        $stmtCount->close();
    }

    if ($action === "patients_age_range") {
        $ageFrom = (int)($_POST["ageFrom"] ?? 0);
        $ageTo = (int)($_POST["ageTo"] ?? 0);
        $title = "Patients by Age Range";

        $stmt = $conn->prepare("
            SELECT patID, patFName, patLName, patAge, patTelNo
            FROM patient
            WHERE patAge BETWEEN ? AND ?
            ORDER BY patLName, patFName
        ");
        $stmt->bind_param("ii", $ageFrom, $ageTo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultRows[] = $row;
        }
        $stmt->close();

        $stmtCount = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM patient
            WHERE patAge BETWEEN ? AND ?
        ");
        $stmtCount->bind_param("ii", $ageFrom, $ageTo);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result()->fetch_assoc();
        $countValue = $countResult["total"] ?? 0;
        $stmtCount->close();
    }

    if ($action === "consult_by_patient") {
        $patID = trim($_POST["patID"] ?? "");
        $title = "Consultations by Patient ID";

        $stmt = $conn->prepare("SELECT * FROM consultation WHERE patID = ? ORDER BY consultDate DESC");
        $stmt->bind_param("s", $patID);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultRows[] = $row;
        }
        $stmt->close();

        $stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM consultation WHERE patID = ?");
        $stmtCount->bind_param("s", $patID);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result()->fetch_assoc();
        $countValue = $countResult["total"] ?? 0;
        $stmtCount->close();
    }

    if ($action === "consult_by_doctor") {
        $docID = trim($_POST["docID"] ?? "");
        $title = "Consultations by Doctor ID";

        $stmt = $conn->prepare("SELECT * FROM consultation WHERE docID = ? ORDER BY consultDate DESC");
        $stmt->bind_param("s", $docID);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultRows[] = $row;
        }
        $stmt->close();

        $stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM consultation WHERE docID = ?");
        $stmtCount->bind_param("s", $docID);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result()->fetch_assoc();
        $countValue = $countResult["total"] ?? 0;
        $stmtCount->close();
    }

    if ($action === "consult_date_range") {
        $dateFrom = trim($_POST["dateFrom"] ?? "");
        $dateTo = trim($_POST["dateTo"] ?? "");
        $title = "Consultations by Date Range";

        $stmt = $conn->prepare("
            SELECT * FROM consultation
            WHERE DATE(consultDate) BETWEEN ? AND ?
            ORDER BY consultDate DESC
        ");
        $stmt->bind_param("ss", $dateFrom, $dateTo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultRows[] = $row;
        }
        $stmt->close();

        $stmtCount = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM consultation
            WHERE DATE(consultDate) BETWEEN ? AND ?
        ");
        $stmtCount->bind_param("ss", $dateFrom, $dateTo);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result()->fetch_assoc();
        $countValue = $countResult["total"] ?? 0;
        $stmtCount->close();
    }

    if ($title !== "" && empty($resultRows)) {
        $message = "No records found for this query.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations Inquiry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container py-4">
    <div class="page-box">
        <h1>Consultations Inquiry</h1>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to Menu</a>
    </div>

    <div class="page-box">
        <h2>Display Doctors by Specialization</h2>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="doctor_special">
            <div class="col-md-4"><input type="text" name="docSpecial" class="form-control" placeholder="Specialization" required></div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit">Show</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>Display Patients by Age Range</h2>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="patients_age_range">
            <div class="col-md-2"><input type="number" name="ageFrom" class="form-control" placeholder="From Age" required></div>
            <div class="col-md-2"><input type="number" name="ageTo" class="form-control" placeholder="To Age" required></div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit">Show</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>Display Consultations by Patient ID</h2>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="consult_by_patient">
            <div class="col-md-3"><input type="text" name="patID" class="form-control" placeholder="Patient ID" required></div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit">Show</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>Display Consultations by Doctor ID</h2>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="consult_by_doctor">
            <div class="col-md-3"><input type="text" name="docID" class="form-control" placeholder="Doctor ID" required></div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit">Show</button></div>
        </form>
    </div>

    <div class="page-box">
        <h2>Display Consultations by Date Range</h2>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="consult_date_range">
            <div class="col-md-3"><input type="date" name="dateFrom" class="form-control" required></div>
            <div class="col-md-3"><input type="date" name="dateTo" class="form-control" required></div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit">Show</button></div>
        </form>
    </div>

    <?php if ($title !== ""): ?>
        <div class="page-box">
            <h2><?php echo e($title); ?></h2>
            <p><strong>COUNT Result:</strong> <?php echo e($countValue); ?></p>
            <?php if ($message !== ""): ?>
                <div class="alert alert-info"><?php echo e($message); ?></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <?php foreach (array_keys($resultRows[0]) as $column): ?>
                                <th><?php echo e($column); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($resultRows as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo e($value); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
