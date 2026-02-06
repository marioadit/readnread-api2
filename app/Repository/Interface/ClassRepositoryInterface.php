<?php

namespace App\Repository\Interface;

interface ClassRepositoryInterface 
{
    public function getAll();
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getClassesForTeacher($teacherId);
    public function getStudentsInClass($classId);
    public function addStudentToClass($classId, $studentId);
    public function removeStudentFromClass($classId, $studentId);
} 