<?php

namespace App\Repository\Interfaces;

use Illuminate\Http\Request;

interface OptionRepositoryInterface
{
    public function getAll();
    public function getById($id);
    public function create(array $data);
    public function update($id, Request $request);
    public function delete($id);
    public function getWithStudentAnswerandQuestion($optionId);
}
