<?php

namespace App\Repository\Interface;


interface UserRepositoryInterface
{

    public function getAll();

    public function getById($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function getUserProfile();

    public function updateProfile($id, array $data);

    public function updatePassword($id, array $data);

    public function resetPassword($id);

    public function updateUser($id, array $data);

    public function getUserStudent();

    public function getUserTeacher();

    public function getClassUser();

    public function getUserLesson();

    public function getStudentAnswers($id);

    public function getAllStudentAnswers();

    public function getStudentAnswersAdmin($idstudent, $idanswer);

    public function getAllStudentAnswersAdmin($idstudent);

    public function getUserQuiz();
}