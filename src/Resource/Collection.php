<?php
declare(strict_types = 1);
/**
 * /src/Resource/Collection.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Resource;

use App\Collection\CollectionTrait;
use App\Rest\RestResourceInterface;
use CallbackFilterIterator;
use Closure;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use IteratorIterator;
use function sprintf;

/**
 * Class Collection
 *
 * @package App\Resource
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 *
 * @property IteratorAggregate|IteratorAggregate<int, RestResourceInterface> $items
 *
 * @method RestResourceInterface                         get(string $className)
 * @method IteratorAggregate<int, RestResourceInterface> getAll(): IteratorAggregate
 */
class Collection implements Countable
{
    // Traits
    use CollectionTrait;

    /**
     * Collection constructor.
     *
     * @param IteratorAggregate|IteratorAggregate<int, RestResourceInterface> $resources
     */
    public function __construct(IteratorAggregate $resources)
    {
        $this->items = $resources;
    }

    /**
     * @param string $className
     *
     * @return RestResourceInterface|null
     */
    public function getEntityResource(string $className): ?RestResourceInterface
    {
        $current = $this->getFilteredItemByEntity($className);

        if ($current === null) {
            $message = sprintf(
                'Resource class does not exists for entity \'%s\'',
                $className
            );

            throw new InvalidArgumentException($message);
        }

        return $current;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    public function hasEntityResource(?string $className): bool
    {
        return $this->getFilteredItemByEntity((string)$className) !== null;
    }

    /**
     * @param string|null $className
     *
     * @return Closure
     */
    public function filter(?string $className): Closure
    {
        return static function (RestResourceInterface $restResource) use ($className): bool {
            return $className !== null && $restResource instanceof $className;
        };
    }

    /**
     * @param string $className
     *
     * @throws InvalidArgumentException
     */
    public function error(string $className): void
    {
        $message = sprintf(
            'Resource \'%s\' does not exists',
            $className
        );

        throw new InvalidArgumentException($message);
    }

    /**
     * @param string $entityName
     *
     * @return RestResourceInterface|null
     */
    private function getFilteredItemByEntity(string $entityName): ?RestResourceInterface
    {
        $filteredIterator = new CallbackFilterIterator(
            new IteratorIterator($this->items->getIterator()),
            static function (RestResourceInterface $restResource) use ($entityName): bool {
                return $restResource->getEntityName() === $entityName;
            }
        );
        $filteredIterator->rewind();

        return $filteredIterator->current();
    }
}
