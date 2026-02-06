<?php

namespace App\Repository\Interface;

interface QuizRepositoryInterface
{
    public function getAll();
    public function getQuizByLessons($quizId);
    public function getQuizByQuestions($quizId);
    public function getWithLessonAndQuestions($quizId);
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getQuizResults($quizId, $studentId);
    public function getUserQuizResults($studentId);

}
