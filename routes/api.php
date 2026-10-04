<?php

use App\Http\Controllers\ApiAnswer\AnswerHistoryApiController;
use App\Http\Controllers\ApiAnswer\StudentAnswerApiController;
use App\Http\Controllers\ApiAuth\AuthApiController;
use App\Http\Controllers\ApiClass\ClassApiController;
use App\Http\Controllers\ApiLesson\LessonApiController;
use App\Http\Controllers\ApiQuestion\QuestionApiController;
use App\Http\Controllers\ApiQuiz\QuizApiController;
use App\Http\Controllers\ApiUser\UserApiController;
use App\Http\Controllers\ApiOption\OptionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Mime\DependencyInjection\AddMimeTypeGuesserPass;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/register', [AuthApiController::class, 'register']);

// logout
Route::middleware('auth:sanctum')->delete('/auth/logout', [AuthApiController::class, 'logout']);
Route::middleware(['auth:sanctum', 'check.token.expiration'])->group(function () {
    // Admin dan guru routes
    Route::middleware('role:admin|guru')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::prefix('users')->group(function () {
                Route::get('/all-student-answers/{idstudent}', [UserApiController::class, 'getAllStudentAnswersAdmin']);
                Route::get('/', [UserApiController::class, 'index']);

                Route::get('/profile', [UserApiController::class, 'getUserProfile']);
                Route::get('/{id}', [UserApiController::class, 'show']);

                Route::post('/', [UserApiController::class, 'store']);
                Route::put('/{id}', [UserApiController::class, 'update']);
                Route::delete('/{id}', [UserApiController::class, 'destroy']);

                Route::put('/profile/{id}', [UserApiController::class, 'updateProfile']);
                Route::put('/password/{id}', [UserApiController::class, 'updatePassword']);
                Route::post('/reset-password/{id}', [UserApiController::class, 'resetPassword']);

                Route::get('/student-answers/{idstudent}/{idanswer}', [UserApiController::class, 'getStudentAnswersAdmin']);
            });

            // Route::get('/dashboard/admin', [UserApiController::class, 'getAdminDashboard']);

            Route::get('/user-quiz', [UserApiController::class, 'getUserQuiz']);

            Route::get('/students', [UserApiController::class, 'getUserStudent']);

            Route::get('/teachers', [UserApiController::class, 'getUserTeacher']);

            // Admin Class Routes
            Route::prefix('classes')->group(function () {
                Route::get('/', [ClassApiController::class, 'index']);
                Route::get('/{id}', [ClassApiController::class, 'show']);
                Route::post('/', [ClassApiController::class, 'store']);
                Route::put('/{id}', [ClassApiController::class, 'update']);
                Route::delete('/{id}', [ClassApiController::class, 'destroy']);

                // Manage students in class
                Route::get('/{id}/students', [ClassApiController::class, 'students']);
                Route::post('/{id}/students', [ClassApiController::class, 'addStudent']);
                Route::delete('/{id}/students', [ClassApiController::class, 'removeStudent']);

                Route::get('/{id}/lessons', [LessonApiController::class, 'lessonsByClass']);
            });

            // Admin Lesson Routes
            Route::prefix('lessons')->group(function () {
                Route::get('/', [LessonApiController::class, 'index']);
                Route::get('/{id}', [LessonApiController::class, 'show']);
                Route::post('/', [LessonApiController::class, 'store']);
                Route::put('/{id}', [LessonApiController::class, 'update']);
                Route::delete('/{id}', [LessonApiController::class, 'destroy']);

                Route::get('/{id}/quiz', [LessonApiController::class, 'getLessonsByQuiz']);
            });

            Route::prefix('quiz')->group(function () {
                Route::get('/', [QuizApiController::class, 'index']);
                Route::get('/{id}', [QuizApiController::class, 'show']);

                // Quiz To Lesson
                Route::get('/{id}/lessons', [QuizApiController::class, 'getQuizByLessons']);

                // Quiz To Questions
                Route::get('/{id}/questions', [QuizApiController::class, 'getQuizByQuestions']);

                Route::get('/{id}/LessonQuestions', [QuizApiController::class, 'getWithLessonAndQuestions']);

                Route::post('/', [QuizApiController::class, 'store']);
                Route::put('/{id}', [QuizApiController::class, 'update']);
                Route::delete('/{id}', [QuizApiController::class, 'destroy']);
            });

            Route::prefix('questions')->group(function () {
                Route::get('/all-student-answers', [QuestionApiController::class, 'getAllStudentAnswers']);
                Route::get('/', [QuestionApiController::class, 'index']);
                Route::get('/{id}', [QuestionApiController::class, 'show']);
                Route::post('/', [QuestionApiController::class, 'store']);
                Route::put('/{id}', [QuestionApiController::class, 'update']);
                Route::delete('/{id}', [QuestionApiController::class, 'destroy']);
                Route::get('/{id}/student-answers', [QuestionApiController::class, 'getStudentAnswers']);
                Route::get('/{id}/detail', [QuestionApiController::class, 'detail']);
            });

            // Route::prefix('essay-answers')->group(function () {
            //     Route::get('/{id}', [OptionApiController::class, 'showEssayAnswer']);
            //     Route::post('/', [OptionApiController::class, 'storeEssayAnswer']);
            //     Route::put('/{id}', [OptionApiController::class, 'updateEssayAnswer']);
            //     Route::delete('/{id}', [OptionApiController::class, 'destroyEssayAnswer']);
            // });

            Route::prefix('options')->group(function () {
                Route::prefix('essay-answers')->group(function () {
                    Route::get('/{id}', [OptionApiController::class, 'showEssayAnswer']);
                    Route::post('/', [OptionApiController::class, 'storeEssayAnswer']);
                    Route::put('/{id}', [OptionApiController::class, 'updateEssayAnswer']);
                    Route::delete('/{id}', [OptionApiController::class, 'destroyEssayAnswer']);
                });

                Route::get('/', [OptionApiController::class, 'index']);
                Route::get('/{id}', [OptionApiController::class, 'show']);
                Route::post('/', [OptionApiController::class, 'store']);
                Route::put('/{id}', [OptionApiController::class, 'update']);
                Route::delete('/{id}', [OptionApiController::class, 'destroy']);
            });

            Route::prefix('student-answers')->group(function () {
                Route::get('/', [StudentAnswerApiController::class, 'index']);
                Route::get('/{id}', [StudentAnswerApiController::class, 'show']);
                Route::put('/', [StudentAnswerApiController::class, 'update']);
                Route::post('/', [StudentAnswerApiController::class, 'store']);
                Route::delete('/', [StudentAnswerApiController::class, 'destroy']);
            });

            Route::prefix('answer-history')->group(function () {
                Route::get('/', [AnswerHistoryApiController::class, 'index']);
                Route::get('/student/{studentId}', [AnswerHistoryApiController::class, 'getByStudent']);
                Route::get('/quiz/{quizId}', [AnswerHistoryApiController::class, 'getByQuiz']);
                Route::get('/detail/{studentId}/{quizId}', [AnswerHistoryApiController::class, 'getByDetail']);
            });

            Route::prefix('quizzes')->group(function () {
                Route::get('/{quizId}/results', [QuizApiController::class, 'getQuizResults']);
                Route::get('/results/{userId}', [QuizApiController::class, 'getUserQuizResults']);
                Route::get('/{userId}/results', [QuizApiController::class, 'getUserQuizResults']);
            });
        });
    });

    // Guru routes
    Route::middleware(['auth:sanctum', 'role:guru'])->group(function () {
        Route::prefix('guru')->group(function () {
            Route::get('/dashboard/teacher', [UserApiController::class, 'getTeacherDashboard']);
        });
    });

    // Admin routes
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard/admin', [UserApiController::class, 'getAdminDashboard']);
            Route::get('/alumni', [UserApiController::class, 'getUserStudentGraduated']);
        });
    });

    // Siswa routes
    Route::middleware(['auth:sanctum', 'role:siswa'])->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/all-student-answers', [UserApiController::class, 'getAllStudentAnswers']);
            Route::get('/profile', [UserApiController::class, 'getUserProfile']);
            Route::put('/profile/{id}', [UserApiController::class, 'updateProfile']);
            Route::put('/password/{id}', [UserApiController::class, 'updatePassword']);

            Route::get('/student-answers/{id}', [UserApiController::class, 'getStudentAnswers']);

            Route::get('/classes', [UserApiController::class, 'getClassUser']);

            // Detail
            Route::get('/classes/{id}', [ClassApiController::class, 'show']);

            // Detail students in class
            Route::get('classes/{id}/students', [ClassApiController::class, 'students']);

            // Student class and lesson routes
            Route::get('classes/{id}/lessons', [LessonApiController::class, 'lessonsByClass']);

            Route::get('lessons', [UserApiController::class, 'getUserLesson']);
            Route::get('lessons/{id}', [LessonApiController::class, 'show']);

            Route::get('quiz/{id}/LessonQuestions', [QuizApiController::class, 'getWithLessonAndQuestions']);

            Route::get('questions/{id}', [QuestionApiController::class, 'show']);

            Route::prefix('student-answers')->group(function () {
                Route::get('/', [StudentAnswerApiController::class, 'index']);
                Route::get('/{id}', [StudentAnswerApiController::class, 'show']);
                Route::put('/', [StudentAnswerApiController::class, 'update']);
                Route::post('/', [StudentAnswerApiController::class, 'store']);
                Route::delete('/', [StudentAnswerApiController::class, 'destroy']);
            });

            Route::prefix('answer-history')->group(function () {
                Route::get('/', [AnswerHistoryApiController::class, 'getMyHistory']);
                Route::get('/quiz/{quizId}', [AnswerHistoryApiController::class, 'getMyDetailHistory']);
                // Route::get('/detail/{quizId}', [AnswerHistoryApiController::class, 'getMyDetailHistory']);
            });

            Route::prefix('quizzes')->group(function () {
                Route::get('/results', [QuizApiController::class, 'getUserQuizResults']);
                Route::get('/{quizId}/results', [QuizApiController::class, 'getQuizResults']);
            });
        });
    });
});

// Admin routes
// Route::middleware('role:admin')->group(function () {
//     Route::prefix('admin')->group(function () {
//         Route::prefix('users')->group(function () {
//             Route::get('/', [UserApiController::class, 'index']);
//             Route::get('/{id}', [UserApiController::class, 'show']);
//             Route::post('/', [UserApiController::class, 'store']);
//             Route::put('/{id}', [UserApiController::class, 'update']);
//             Route::delete('/{id}', [UserApiController::class, 'destroy']);

//     });
// });
// });


// Admin dan Guru routes
// Route::middleware('role:admin,guru')->group(function () {
//     route::prefix('admins')->group(function () {

//         // User profile routes
//         route::prefix('users')->group(function () {
//             Route::get('/profile', [UserApiController::class, 'getUserProfile']);
//             Route::put('/profile/{id}', [UserApiController::class, 'updateProfile']);
//             // Route::put('/password', [UserApiController::class, 'updatePassword']);
//             Route::put('/password/{id}', [UserApiController::class, 'updatePassword']);
//             Route::put('/{id}', [UserApiController::class, 'updateUser']);
//         });
//         // Guru class routes
//         Route::prefix('classes')->group(function () {
//             Route::get('/my-classes', [ClassApiController::class, 'myClasses']);
//             Route::get('/{id}/students', [ClassApiController::class, 'students']);
//             Route::get('/{id}/lessons', [LessonApiController::class, 'lessonsByClass']);
//         });

//         // Guru lesson routes
//         Route::prefix('lessons')->group(function () {
//             Route::get('/{id}', [LessonApiController::class, 'show']);
//             Route::post('/', [LessonApiController::class, 'store']);
//             Route::put('/{id}', [LessonApiController::class, 'update']);
//         });
//     });
// });
// });
