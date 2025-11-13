<?php

declare(strict_types=1);

namespace StudentLearning\Presentation\Http\Controller;

use StudentLearning\Application\Command\EnrollStudentCommand;
use StudentLearning\Application\Command\EnrollStudentHandler;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Enrollment HTTP Controller (StudentLearning context)
 *
 * Handles HTTP requests for student enrollment in courses
 * From the LEARNING perspective: granting students access to course materials
 */
#[Controller(prefix: '/api/enrollments')]
class EnrollmentController
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected HttpResponse $response;

    #[Inject]
    protected ValidatorFactoryInterface $validatorFactory;

    #[Inject]
    protected EnrollStudentHandler $enrollStudentHandler;

    /**
     * Enroll a student in a course
     *
     * POST /api/enrollments
     *
     * This grants the student learning access to the course materials.
     * It will trigger the enrollment process in both StudentLearning and CourseManagement contexts.
     *
     * Request Body:
     * {
     *   "course_id": "uuid",
     *   "student_id": "uuid"
     * }
     */
    #[PostMapping(path: '')]
    public function enroll(): ResponseInterface
    {
        try {
            // Validate request
            $validator = $this->validatorFactory->make(
                $this->request->all(),
                [
                    'course_id' => 'required|string|uuid',
                    'student_id' => 'required|string|uuid',
                ]
            );

            if ($validator->fails()) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ])->withStatus(422);
            }

            // Execute command
            $command = new EnrollStudentCommand(
                courseId: $this->request->input('course_id'),
                studentId: $this->request->input('student_id'),
            );

            $this->enrollStudentHandler->handle($command);

            return $this->response->json([
                'success' => true,
                'message' => 'Student successfully enrolled in course',
                'data' => [
                    'course_id' => $command->courseId,
                    'student_id' => $command->studentId,
                    'status' => 'enrolled',
                ],
            ])->withStatus(201);

        } catch (InvalidArgumentException $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ])->withStatus(400);

        } catch (Throwable $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'An error occurred while enrolling the student',
                'error' => $e->getMessage(),
            ])->withStatus(500);
        }
    }
}
