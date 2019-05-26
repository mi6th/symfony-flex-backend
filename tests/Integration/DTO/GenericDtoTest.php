<?php
declare(strict_types = 1);
/**
 * /tests/Integration/DTO/DtoTestCase.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Integration\DTO;

use App\DTO\RestDtoInterface;
use App\DTO\User;
use App\Entity\User as UserEntity;
use App\Tests\Integration\Dto\src\DummyDto;
use App\Utils\Tests\PhpUnitUtil;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

/**
 * Class GenericDtoTest
 *
 * @package App\Tests\Integration\DTO
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class GenericDtoTest extends KernelTestCase
{
    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatPatchThrowsAnExceptionIfGetterMethodDoesNotExist(): void
    {
        /** @var MockObject|RestDtoInterface $dtoMock */
        $dtoMock = $this->createMock(RestDtoInterface::class);

        $dtoMock
            ->expects(static::once())
            ->method('getVisited')
            ->willReturn(['foo']);

        $dto = new User();
        $dto->patch($dtoMock);

        unset($dto, $dtoMock);
    }

    /**
     * @dataProvider dataProviderTestThatDetermineGetterMethodReturnsExpected
     *
     * @param string           $expected
     * @param string           $property
     * @param RestDtoInterface $dto
     *
     * @throws Throwable
     */
    public function testThatDetermineGetterMethodReturnsExpected(
        string $expected,
        string $property,
        RestDtoInterface $dto
    ): void {
        static::assertSame($expected, PhpUnitUtil::callMethod($dto, 'determineGetterMethod', [$dto, $property]));
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Property 'foo' has multiple getter methods - this is insane!
     */
    public function testThatPatchThrowsAnErrorIfMultipleGettersAreDefined(): void
    {
        require_once __DIR__ . '/src/DummyDto.php';

        $dtoMock = new DummyDto();
        $dtoMock->setFoo('foo');

        $dto = new User();
        $dto->patch($dtoMock);

        unset($dto, $dtoMock);
    }

    public function testThatPatchCallsExpectedMethods(): void
    {
        /** @var MockObject|User $dtoUser */
        $dtoUser = $this->createMock(User::class);

        $dtoUser
            ->expects(static::once())
            ->method('getVisited')
            ->willReturn(['username', 'email']);

        $dtoUser
            ->expects(static::once())
            ->method('getUsername')
            ->willReturn('username');

        $dtoUser
            ->expects(static::once())
            ->method('getEmail')
            ->willReturn('email@com');

        $dto = (new User())->patch($dtoUser);

        static::assertSame('username', $dto->getUsername());
        static::assertSame('email@com', $dto->getEmail());
    }

    public function testThatUpdateMethodCallsExpectedMethods(): void
    {
        /** @var MockObject|UserEntity $userEntity */
        $userEntity = $this->createMock(UserEntity::class);

        $userEntity
            ->expects(static::once())
            ->method('setUsername')
            ->with('username')
            ->willReturn($userEntity);

        $userEntity
            ->expects(static::once())
            ->method('setEmail')
            ->with('email@com')
            ->willReturn($userEntity);

        $userEntity
            ->expects(static::once())
            ->method('setPlainPassword')
            ->with('password')
            ->willReturn($userEntity);

        $dto = (new User())
            ->setUsername('username')
            ->setEmail('email@com')
            ->setPassword('password');

        $dto->update($userEntity);
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatDetermineGetterMethodReturnsExpected(): Generator
    {
        yield [
            'getUsername',
            'username',
            new User(),
        ];

        yield [
            'getEmail',
            'email',
            new User(),
        ];

        // TODO: implement test cases for `has` and `is` methods
    }
}
