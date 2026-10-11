<?php

namespace App\Providers;

use App\Repository\AuthRepository;
use App\Repository\ClassRepository;
use App\Repository\Interfaces\AuthRepositoryInterface;
use App\Repository\Interfaces\ClassRepositoryInterface;
use App\Repository\Interfaces\LessonRepositoryInterface;
use App\Repository\Interfaces\QuestionRepositoryInterface;
use App\Repository\Interfaces\QuizRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Repository\Interfaces\OptionRepositoryInterface;
use App\Repository\Interfaces\IUserAnswerRepository;
use App\Repository\Interfaces\IAnswerHistoryRepository;
use App\Repository\Interfaces\IStudentScoreRecapRepository;
use App\Repository\LessonRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Repository\UserRepository;
use App\Repository\OptionRepository;
use App\Repository\UserAnswerRepository;
use App\Repository\AnswerHistoryRepository;
use App\Repository\StudentScoreRecapRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class , UserRepository::class);
        $this->app->bind(AuthRepositoryInterface::class , AuthRepository::class);
        $this->app->bind(ClassRepositoryInterface::class , ClassRepository::class);
        $this->app->bind(LessonRepositoryInterface::class , LessonRepository::class);
        $this->app->bind(QuizRepositoryInterface::class , QuizRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class , QuestionRepository::class);
        $this->app->bind(OptionRepositoryInterface::class , OptionRepository::class);
        $this->app->bind(IUserAnswerRepository::class , UserAnswerRepository::class);
        $this->app->bind(IAnswerHistoryRepository::class , AnswerHistoryRepository::class);
        $this->app->bind(IStudentScoreRecapRepository::class , StudentScoreRecapRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    //
    }
}
