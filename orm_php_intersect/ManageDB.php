<?php
include_once 'DB.php';

class CourseEnrollments extends DB {
    public function getInstructorExcept() {
        $query = $this->connect()->query("
        SELECT * FROM (
            SELECT i.* 
            FROM instructor i 
            WHERE salary > 90000
        ) AS set_1
        EXCEPT
        SELECT * FROM (
            SELECT i.* 
            FROM instructor i 
            WHERE i.dept_name IN ('Music', 'Physics')
        ) AS set_2
        ORDER BY salary DESC
    ");
    
    $instructors = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform data into structured format
    $formattedData = array_map(function($instructor) {
        return [
            'instructor_info' => [
                'ID' => $instructor['ID'],
                'name' => $instructor['name'],
                'dept_name' => $instructor['dept_name'],
                'salary' => (float)$instructor['salary']
            ]
        ];
    }, $instructors);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'total_records' => count($formattedData),
        'instructors' => array_values($formattedData)
    ], JSON_PRETTY_PRINT);
    exit;
}

    public function getEnrollments() {
        $query = $this->connect()->query("
            SELECT DISTINCT 
                c.course_id,
                c.title,
                c.dept_name,
                c.credits,
                t.ID as student_id,
                t.sec_id,
                t.semester,
                t.year
            FROM course AS c 
            NATURAL JOIN takes AS t
            ORDER BY c.course_id, t.year, t.semester
        ");
        
        $enrollments = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Transform data into structured format
        $formattedData = array_map(function($enrollment) {
            return [
                'course_info' => [
                    'course_id' => $enrollment['course_id'],
                    'title' => $enrollment['title'],
                    'dept_name' => $enrollment['dept_name'],
                    'credits' => (int)$enrollment['credits'],
                    'ID' => $enrollment['student_id'],
                    'sec_id' => $enrollment['sec_id'],
                    'semester' => $enrollment['semester'],
                    'year' => (int)$enrollment['year']
                ]
            ];
        }, $enrollments);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'total_records' => count($formattedData),
            'enrollments' => array_values($formattedData)  // Ensure sequential array
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
?>