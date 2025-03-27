<?php
/**
 * @OA\Info(title="University API", version="1.0")
 */
include_once 'ManageDB.php';

class GetData {
    public function getAll() {
        try {
            $db = new CourseEnrollments();
            
            // Get only instructors data
            $instructors = $db->getInstructorExcept();

            // Return instructors data
            return [
                'status' => 'success',
                'total_records' => count($instructors['instructors'] ?? []),
                'instructors' => $instructors['instructors'] ?? []
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}

// Handle the request
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
$api = new GetData();
echo json_encode($api->getAll(), JSON_PRETTY_PRINT);
exit;
?>