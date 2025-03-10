<?php
include_once 'DB.php';
class ManageBD extends DB {
    public function getQueries() {
        // Get all students with their detailed course information
        $students = $this->connect()->query("
            SELECT 
                s.*,
                GROUP_CONCAT(
                    DISTINCT
                    JSON_OBJECT(
                        'course_id', t.course_id,
                        'sec_id', t.sec_id,
                        'semester', t.semester,
                        'year', t.year,
                        'grade', COALESCE(t.grade, 'N/A')
                    )
                ) as courses,
                COUNT(DISTINCT t.course_id) as course_count,
                GROUP_CONCAT(DISTINCT t.semester) as enrolled_semesters,
                GROUP_CONCAT(DISTINCT t.year) as enrolled_years
            FROM student s
            LEFT JOIN takes t ON s.ID = t.ID
            GROUP BY s.ID, s.name, s.dept_name, s.tot_cred
        ");
        $studentData = $students->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all departments
        $departments = $this->connect()->query("SELECT * FROM department");
        $departmentData = $departments->fetchAll(PDO::FETCH_ASSOC);
        
        // Enhanced student data enrichment
        $enrichedStudents = array_map(function($student) {
            $courses = $student['courses'] ? json_decode('[' . $student['courses'] . ']', true) : [];
            
            // Calculate academic statistics
            $grades = array_column($courses, 'grade');
            $uniqueSemesters = array_unique(array_column($courses, 'semester'));
            $uniqueYears = array_unique(array_column($courses, 'year'));
            
            return [
                'student_data' => [
                    'ID' => $student['ID'],
                    'name' => $student['name'],
                    'dept_name' => $student['dept_name'],
                    'tot_cred' => $student['tot_cred']
                ],
                'academic_info' => [
                    'total_courses' => count($courses),
                    'unique_semesters' => $uniqueSemesters,
                    'years_enrolled' => $uniqueYears,
                ],
                'status_matricula' => !empty($courses) ? 'Matriculado' : 'No matriculado',
                'matricula' => ['Primera'],
                'courses' => $courses
            ];
        }, $studentData);

        $queries = array(
            "students" => $enrichedStudents,
            "departments" => $departmentData
        );
        
        return $queries;
    }
}
?>