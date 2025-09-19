<?php

namespace App\Repositories;

interface BaseRepositoryInterface
{
    /**
     * Get all resources
     * 
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*']);
    
    /**
     * Get paginated resources
     * 
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);
    
    /**
     * Create new resource
     * 
     * @param array $data
     * @return mixed
     */
    public function create(array $data);
    
    /**
     * Update resource
     * 
     * @param mixed $data
     * @param mixed $id
     * @return mixed
     */
    public function update($data, $id);
    
    /**
     * Delete resource
     * 
     * @param mixed $id
     * @return bool
     */
    public function delete($id);
    
    /**
     * Find resource by id
     * 
     * @param int $id
     * @param array $columns
     * @return mixed
     */
    public function find(int $id, array $columns = ['*']);
    
    /**
     * Find resource by specific column
     * 
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return mixed
     */
    public function findBy(string $field, $value, array $columns = ['*']);
}