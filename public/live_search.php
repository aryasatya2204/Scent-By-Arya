<?php
// /public/live_search.php
require_once '../includes/db.php';
header('Content-Type: application/json');

$results = [];
$term = $_GET['term'] ?? '';

if (strlen($term) > 0) {
    $sql = "SELECT id, name, brand FROM products WHERE name LIKE ? OR brand LIKE ? LIMIT 5";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        $search_term = "%" . $term . "%";
        mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

echo json_encode($results);
?>
