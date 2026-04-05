<?php

namespace App\Repository\Interfaces;

use Illuminate\Http\Request;

interface IUserAnswerRepository
{
    public function getAll();
    public function getById($id);
    public function store(Request $request);
    public function update(Request $request);
    public function delete(Request $request);
}
