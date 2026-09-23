<?php

namespace App\Service;

use App\Repository\Interfaces\UserRepositoryInterface;

class UserService
{

    protected $userRepository;

    public function __construct(UserRepositoryInterface $_userRepository)
    {
        $this->userRepository = $_userRepository;
    }

    public function getAllUser()
    {
        return $this->userRepository->getAll();
    }

    public function getUserById($id)
    {
        return $this->userRepository->getById($id);
    }

    public function createUser(array $data)
    {
        return $this->userRepository->create($data);
    }

    public function updateUser($id, array $data)
    {
        return $this->userRepository->update($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->userRepository->delete($id);
    }

    public function getUserProfile()
    {
        return $this->userRepository->getUserProfile();
    }

    public function updateProfile($id, array $data)
    {
        return $this->userRepository->updateProfile($id, $data);
    }

    public function resetPassword($id)
    {
        return $this->userRepository->resetPassword($id);
    }

    public function updatePassword($id, array $data)
    {
        return $this->userRepository->updatePassword($id, $data);
    }

    public function getUserStudent()
    {
        return $this->userRepository->getUserStudent();
    }

    public function getUserStudentGraduated()
    {
        return $this->userRepository->getUserStudentGraduated();
    }

    public function getUserTeacher()
    {
        return $this->userRepository->getUserTeacher();
    }

    public function getClassUser()
    {
        return $this->userRepository->getClassUser();
    }

    public function getUserLesson()
    {
        return $this->userRepository->getUserLesson();
    }

    public function getStudentAnswers($id)
    {
        return $this->userRepository->getStudentAnswers($id);
    }

    public function getAllStudentAnswers()
    {
        return $this->userRepository->getAllStudentAnswers();
    }

    public function getStudentAnswersAdmin($idstudent, $idanswer)
    {
        return $this->userRepository->getStudentAnswersAdmin($idstudent, $idanswer);
    }

    public function getAllStudentAnswersAdmin($idstudent)
    {
        return $this->userRepository->getAllStudentAnswersAdmin($idstudent);
    }

    public function getUserQuiz()
    {
        return $this->userRepository->getUserQuiz();
    }

    public function getTeacherDashboard()
    {
        return $this->userRepository->getTeacherDashboard();
    }

    public function getAdminDashboard()
    {
        return $this->userRepository->getAdminDashboard();
    }
}
