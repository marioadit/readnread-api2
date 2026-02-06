<?php

namespace App\Repository\Interface;

use Illuminate\Http\Request;

interface QuestionRepositoryInterface
{
    public function getAll();
    public function getById($id);
    public function create(array $data);
    public function update($id, Request $request);
    public function delete($id);
    public function createWithRelations(array $data);
    public function updateWithRelations($id, array $request);
    public function getWithRelations($id);
    public function getDetailQuestion($id);
    public function getAllStudentAnswers();
    public function getStudentAnswers($id);
    // public function getQuestionsByQuiz($questionId);
    // public function getQuestionsByMatchPair($questionId);
    // public function getQuestionsByOptions($questionId);
    // public function getQuestionsByStudentAnswer($questionId);
    // public function getWithQuizAndOptions($questionId);
}
