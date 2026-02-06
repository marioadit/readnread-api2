<?php

namespace App\Providers;

use App\Repository\AuthRepository;
use App\Repository\ClassRepository;
use App\Repository\Interface\AuthRepositoryInterface;
use App\Repository\Interface\ClassRepositoryInterface;
use App\Repository\Interface\LessonRepositoryInterface;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizRepositoryInterface;
use App\Repository\Interface\UserRepositoryInterface;
use App\Repository\Interface\OptionRepositoryInterface;
use App\Repository\Interface\IUserAnswerRepository;
use App\Repository\LessonRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Repository\UserRepository;
use App\Repository\OptionRepository;
use App\Repository\UserAnswerRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(ClassRepositoryInterface::class, ClassRepository::class);
        $this->app->bind(LessonRepositoryInterface::class, LessonRepository::class);
        $this->app->bind(QuizRepositoryInterface::class, QuizRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class, QuestionRepository::class);
        $this->app->bind(OptionRepositoryInterface::class, OptionRepository::class);
        $this->app->bind(IUserAnswerRepository::class, UserAnswerRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
