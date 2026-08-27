<?php

declare(strict_types=1);

namespace App\Tests\Functional\Review;

use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Review\Domain\Model\Review;
use App\Review\Domain\Model\ReviewId;
use App\Review\Domain\Repository\ReviewRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ReviewDeleteApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private RaceRepositoryInterface $raceRepository;
    private UserRepositoryInterface $userRepository;
    private UserPasswordHasherInterface $hasher;
    private ReviewRepositoryInterface $reviewRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->raceRepository = $this->service(RaceRepositoryInterface::class);
        $this->userRepository = $this->service(UserRepositoryInterface::class);
        $this->reviewRepository = $this->service(ReviewRepositoryInterface::class);
        $this->hasher = $this->service(UserPasswordHasherInterface::class);
    }

    public function testOwnerCanDeleteOwnReview(): void
    {
        $user = $this->createUser('test@example.com', 'password', 'Test User');
        $race = $this->createRace();
        $review = $this->createReview($user, $race);
        $token = $this->login($this->client, 'test@example.com', 'password');
        $this->client->request('DELETE', '/api/reviews/'.$review->getId()->toString(), [], [], $this->authHeaders($token));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull($this->reviewRepository->findById($review->getId()));
    }

    public function testNoAuthorizationTokenCannotDeleteReview(): void
    {
        $this->client->request('DELETE', '/api/reviews/'.ReviewId::generate()->toString());

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testInvalidAuthorizationTokenCannotDeleteReview(): void
    {
        $this->client->request(
            'DELETE',
            '/api/reviews/'.ReviewId::generate()->toString(),
            [],
            [],
            $this->authHeaders('invalid-token'),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testOtherUserCannotDeleteReview(): void
    {
        $user = $this->createUser('test@example.com', 'password', 'Test User');
        $this->createUser('other@example.com', 'password', 'Other User');
        $race = $this->createRace();
        $review = $this->createReview($user, $race);
        $token = $this->login($this->client, 'other@example.com', 'password');
        $this->client->request('DELETE', '/api/reviews/'.$review->getId()->toString(), [], [], $this->authHeaders($token));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertNotNull($this->reviewRepository->findById($review->getId()));
    }

    public function testDeleteUnknownReviewReturns404(): void
    {
        $this->createUser('test@example.com', 'password', 'Test User');
        $token = $this->login($this->client, 'test@example.com', 'password');
        $this->client->request('DELETE', '/api/reviews/'.ReviewId::generate()->toString(), [], [], $this->authHeaders($token));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     *
     * @return T
     */
    private function service(string $id): object
    {
        $service = self::getContainer()->get($id);
        self::assertInstanceOf($id, $service);

        return $service;
    }

    private function createUser(string $email, string $password, string $displayName): User
    {
        $user = User::create(UserId::generate(), $email, 'placeholder', $displayName);
        $user->updatePassword($this->hasher->hashPassword($user, $password));
        $this->userRepository->save($user);

        return $user;
    }

    private function createRace(): Race
    {
        $race = Race::create(RaceId::generate(), 'name', 'city', 'voivodeship');
        $this->raceRepository->save($race);

        return $race;
    }

    private function createReview(User $user, Race $race): Review
    {
        $review = Review::create(ReviewId::generate(), $user->getId(), $race->getId(), 5, 'Test review');
        $this->reviewRepository->save($review);

        return $review;
    }

    private function login(KernelBrowser $client, string $email, string $password): string
    {
        $payload = json_encode([
            'email' => $email,
            'password' => $password,
        ]);
        self::assertNotFalse($payload);
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        self::assertResponseIsSuccessful();
        $response = $client->getResponse()->getContent();
        self::assertNotFalse($response);
        $data = json_decode($response, true);
        self::assertIsArray($data);
        self::assertArrayHasKey('token', $data);
        self::assertIsString($data['token']);

        return $data['token'];
    }

    /**
     * @return array<string, string>
     */
    private function authHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer '.$token];
    }
}
