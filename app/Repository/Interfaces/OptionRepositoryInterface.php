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

    // Essay Answer methods
    public function getEssayAnswerById($id);
    public function createEssayAnswer(array $data);
    public function updateEssayAnswer($id, Request $request);
    public function deleteEssayAnswer($id);
}
