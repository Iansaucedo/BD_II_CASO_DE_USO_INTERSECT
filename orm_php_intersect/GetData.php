<?php
/**
 * @OA\Info(title="University API", version="1.0")
 */
include_once 'ManageDB.php';

/**
 * @OA\Get(
 *     path="/getdata",
 *     summary="Fetch student and department data with additional fields",
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="students", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="departments", type="array", @OA\Items(type="object"))
 *         )
 *     )
 * )
 */

class GetData {
    public function getAll() {
        try {
            $queries = new ManageBD();
            $queries_res = $queries->getQueries();
            
            if (!$queries_res || !isset($queries_res['students']) || !isset($queries_res['departments'])) {
                throw new Exception('Error fetching data from database');
            }

            return $queries_res;
            
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>