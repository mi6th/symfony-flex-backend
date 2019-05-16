<?php
declare(strict_types=1);
/**
 * /tests/Functional/Repository/HealthzRepositoryTest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Functional\Repository;

use App\Entity\Healthz;
use App\Repository\HealthzRepository;
use App\Utils\Tests\PhpUnitUtil;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UserRepositoryTest
 *
 * @package App\Tests\Functional\Repository
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class HealthzRepositoryTest extends KernelTestCase
{
    /**
     * @var HealthzRepository;
     */
    private $repository;

    private static $initialized = false;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        static::bootKernel();

        if (!static::$initialized) {
            PhpUnitUtil::loadFixtures(static::$kernel);

            static::$initialized = true;
        }

        $this->repository = static::$container->get(HealthzRepository::class);
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testThatReadValueMethodReturnsExpectedWithEmptyDatabase(): void
    {
        static::assertNull($this->repository->read());
    }

    /**
     * @depends testThatReadValueMethodReturnsExpectedWithEmptyDatabase
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testThatCreateValueReturnsExpected(): void
    {
        static::assertInstanceOf(Healthz::class, $this->repository->create());
    }

    /**
     * @depends testThatCreateValueReturnsExpected
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testThatReadValueReturnExpectedAfterCreate(): void
    {
        static::assertNotNull($this->repository->read());
    }

    /**
     * @depends testThatReadValueReturnExpectedAfterCreate
     *
     * @throws \Exception
     */
    public function testThatCleanupMethodClearsDatabaseReturnsExpected(): void
    {
        static::assertSame(0, $this->repository->cleanup());
    }
}
