<?php
declare(strict_types = 1);
/**
 * /src/Request/ParamConverter/RestResourceConverter.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Request\ParamConverter;

use App\Resource\ResourceCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class RestResourceConverter
 *
 * Purpose of this param converter is to use exactly same methods and workflow as in basic REST API requests.
 *
 * @package App\Request\ParamConverter
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class RestResourceConverter implements ParamConverterInterface
{
    private ResourceCollection $collection;

    public function __construct(ResourceCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Throwable
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();
        $identifier = (string)$request->attributes->get($name, '');
        $resource = $this->collection->get($configuration->getClass());

        if ($identifier !== '') {
            $request->attributes->set($name, $resource->findOne($identifier, true));
        }

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $this->collection->has($configuration->getClass());
    }
}
