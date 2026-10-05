<?php

/**
 * DataTablesHelper - Helper class for server-side DataTables processing
 * 
 * This class handles server-side processing for DataTables including:
 * - Pagination
 * - Sorting
 * - Searching/Filtering
 * 
 * Usage:
 * $result = DataTablesHelper::process($request, $db, $query, $columns);
 */
class DataTablesHelper
{
    /**
     * Process server-side DataTables request
     * 
     * @param array $request Request parameters (GET/POST)
     * @param PDO $db Database connection
     * @param string $baseQuery Base SQL query (without ORDER BY, LIMIT, OFFSET)
     * @param array $columns Column definitions
     * @param array $bindings Query bindings for WHERE clause
     * @return array DataTables response format
     */
    public static function process(array $request, PDO $db, string $baseQuery, array $columns, array $bindings = []): array
    {
        // Get request parameters
        $draw = isset($request['draw']) ? (int) $request['draw'] : 1;
        $start = isset($request['start']) ? (int) $request['start'] : 0;
        $length = isset($request['length']) ? (int) $request['length'] : 10;
        
        // Limit max length to prevent excessive data retrieval
        if ($length > 100) {
            $length = 100;
        }
        
        // Get search value
        $searchValue = isset($request['search']['value']) ? trim($request['search']['value']) : '';
        
        // Get order column and direction
        $orderColumnIndex = isset($request['order'][0]['column']) ? (int) $request['order'][0]['column'] : 0;
        $orderDir = isset($request['order'][0]['dir']) ? strtoupper($request['order'][0]['dir']) : 'ASC';
        
        if (!in_array($orderDir, ['ASC', 'DESC'])) {
            $orderDir = 'ASC';
        }
        
        // Build WHERE clause for search
        $whereClause = '';
        $searchBindings = $bindings;
        
        if ($searchValue !== '') {
            $searchConditions = [];
            foreach ($columns as $i => $column) {
                if (isset($column['searchable']) && $column['searchable']) {
                    $searchParam = ':search_' . $i;
                    $searchConditions[] = $column['db'] . ' LIKE ' . $searchParam;
                    $searchBindings[$searchParam] = '%' . $searchValue . '%';
                }
            }
            
            if (!empty($searchConditions)) {
                if (!empty($bindings)) {
                    $whereClause = ' AND (' . implode(' OR ', $searchConditions) . ')';
                } else {
                    $whereClause = ' WHERE ' . implode(' OR ', $searchConditions);
                }
            }
        }
        
        // Build ORDER BY clause
        $orderBy = '';
        if (isset($columns[$orderColumnIndex]) && isset($columns[$orderColumnIndex]['db'])) {
            $orderBy = ' ORDER BY ' . $columns[$orderColumnIndex]['db'] . ' ' . $orderDir;
        }
        
        // Count total records (before filtering)
        $countQuery = 'SELECT COUNT(*) FROM (' . $baseQuery . ') AS count_table';
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($bindings);
        $totalRecords = (int) $countStmt->fetchColumn();
        
        // Count filtered records
        $filteredQuery = 'SELECT COUNT(*) FROM (' . $baseQuery . ') AS count_table' . $whereClause;
        $filteredStmt = $db->prepare($filteredQuery);
        $filteredStmt->execute($searchBindings);
        $filteredRecords = (int) $filteredStmt->fetchColumn();
        
        // Build data query with pagination and sorting
        $dataQuery = $baseQuery . $whereClause . $orderBy . ' LIMIT ' . $length . ' OFFSET ' . $start;
        $dataStmt = $db->prepare($dataQuery);
        $dataStmt->execute($searchBindings);
        $data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process data for output
        $outputData = [];
        foreach ($data as $row) {
            $outputRow = [];
            foreach ($columns as $column) {
                $dbColumn = $column['db'];
                $dtColumn = $column['dt'];
                
                $value = $row[$dbColumn] ?? null;
                
                // Apply formatter if provided
                if (isset($column['formatter']) && is_callable($column['formatter'])) {
                    $value = call_user_func($column['formatter'], $value, $row);
                }
                
                $outputRow[$dtColumn] = $value;
            }
            $outputData[] = $outputRow;
        }
        
        return [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $outputData
        ];
    }
    
    /**
     * Create column definition helper
     * 
     * @param string $db Database column name
     * @param int|string $dt DataTables column index or name
     * @param bool $searchable Whether column is searchable
     * @param bool $orderable Whether column is orderable
     * @param callable|null $formatter Optional formatter function
     * @return array Column definition
     */
    public static function column(string $db, $dt, bool $searchable = true, bool $orderable = true, ?callable $formatter = null): array
    {
        return [
            'db' => $db,
            'dt' => $dt,
            'searchable' => $searchable,
            'orderable' => $orderable,
            'formatter' => $formatter
        ];
    }
}
