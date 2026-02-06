<?php

namespace App\Repository\Interface;

use Illuminate\Http\Request;

interface LessonRepositoryInterface 
{
    public function getAll();
    public function getById($id);
    public function create(array $data);
    public function update($id, Request $request);
    public function delete($id);
    public function getLessonsByClass($classId);
    public function getLessonsByQuiz($classId);
} 