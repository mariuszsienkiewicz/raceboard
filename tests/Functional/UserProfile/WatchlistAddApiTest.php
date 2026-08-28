<?php

declare(strict_types=1);

namespace App\Tests\Functional\UserProfile;

use App\RaceCatalog\Domain\Model\Race;
use App\RaceCatalog\Domain\Repository\RaceRepositoryInterface;
use App\Shared\Domain\Model\RaceId;
use App\Shared\Domain\Model\UserId;
use App\UserProfile\Domain\Model\User;
use App\UserProfile\Domain\Repository\UserRepositoryInterface;
use App\UserProfile\Domain\Repository\WatchlistEntryRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class WatchlistAddApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private RaceRepositoryInterface $raceRepository;
    private UserRepositoryInterface $userRepository;
    private WatchlistEntryRepositoryInterface $watchlistRepository;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->raceRepository = $this->service(RaceRepositoryInterface::class);
        $this->userRepository = $this->service(UserRepositoryInterface::class);
        $this->watchlistRepository = $this->service(WatchlistEntryRepositoryInterface::class);
        $this->hasher = $this->service(UserPasswordHasherInterface::class);
    }

    public function testAuthenticatedUserCanAddRaceToWatchlist(): void
    {
        $user = $this->createUser('test@example.com', 'password', 'Test User');
        $race = $this->createRace();
        $token = $this->login($this->client, 'test@example.com', 'password');

        $this->client->request(
            'POST',
            '/api/me/watchlist/'.$race->getId()->toString(),
            [],
            [],
            $this->authHeaders($token),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $response = json_decode($this->client->getResponse()->getContent() ?: '', true);
        self::assertIsArray($response);
        self::assertArrayHasKey('id', $response);
        self::assertIsString($response['id']);
        self::assertNotNull($this->watchlistRepository->findByUserAndRace($user->getId(), $race->getId()));
    }

    public function testNoAuthorizationTokenCannotAddToWatchlist(): void
    {
        $race = $this->createRace();

        $this->client->request('POST', '/api/me/watchlist/'.$race->getId()->toString());

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testInvalidAuthorizationTokenCannotAddToWatchlist(): void
    {
        $race = $this->createRace();

        $this->client->request(
            'POST',
            '/api/me/watchlist/'.$race->getId()->toString(),
            [],
            [],
            $this->authHeaders('invalid-token'),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAddNonExistentRaceReturns404(): void
    {
        $this->createUser('test@example.com', 'password', 'Test User');
        $token = $this->login($this->client, 'test@example.com', 'password');
        $unknownRaceId = RaceId::generate()->toString();

        $this->client->request(
            'POST',
            '/api/me/watchlist/'.$unknownRaceId,
            [],
            [],
            $this->authHeaders($token),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = json_decode($this->client->getResponse()->getContent() ?: '', true);
        self::assertIsArray($response);
        self::assertSame(['error' => 'race_not_found'], $response);
    }

    public function testAddDuplicateRaceReturns409(): void
    {
        $user = $this->createUser('test@example.com', 'password', 'Test User');
        $race = $this->createRace();
        $token = $this->login($this->client, 'test@example.com', 'password');
        $raceId = $race->getId()->toString();

        $this->client->request(
            'POST',
            '/api/me/watchlist/'.$raceId,
            [],
            [],
            $this->authHeaders($token),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->client->request(
            'POST',
            '/api/me/watchlist/'.$raceId,
            [],
            [],
            $this->authHeaders($token),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $response = json_decode($this->client->getResponse()->getContent() ?: '', true);
        self::assertIsArray($response);
        self::assertSame(['error' => 'watchlist_entry_already_exists'], $response);
        self::assertNotNull($this->watchlistRepository->findByUserAndRace($user->getId(), $race->getId()));
    }

    /**
     * @template T of object
     *
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
        $race = Race::create(RaceId::generate(), 'Test Race', 'Warsaw', 'Masovian');
        $this->raceRepository->save($race);

        return $race;
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
